<?php
declare(strict_types=1);

function guild_mark_detect_mime(string $path, array $imageInfo = []): string
{
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mime = finfo_file($finfo, $path);
            finfo_close($finfo);
            if (is_string($mime) && $mime !== '') {
                return $mime;
            }
        }
    }

    return is_string($imageInfo['mime'] ?? null) ? $imageInfo['mime'] : '';
}

function guild_mark_validate_bmp(string $path): bool
{
    if (!is_file($path) || filesize($path) === false || filesize($path) > 2048) {
        return false;
    }

    $info = @getimagesize($path);
    if (!is_array($info) || ($info[0] ?? 0) !== 16 || ($info[1] ?? 0) !== 12 || ($info['bits'] ?? 0) !== 24) {
        return false;
    }

    $mime = guild_mark_detect_mime($path, $info);
    return in_array($mime, ['image/bmp', 'image/x-ms-bmp'], true);
}

function guild_mark_convert_upload(array $upload, string $destination): void
{
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('A imagem nao foi recebida corretamente.');
    }
    if (!isset($upload['tmp_name'], $upload['size']) || !is_string($upload['tmp_name']) || !is_uploaded_file($upload['tmp_name'])) {
        throw new RuntimeException('Upload de imagem invalido.');
    }
    if ((int)$upload['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('A imagem original nao pode ultrapassar 5 MB.');
    }

    $inputInfo = @getimagesize($upload['tmp_name']);
    if (!is_array($inputInfo) || ($inputInfo[0] ?? 0) < 1 || ($inputInfo[1] ?? 0) < 1 || ($inputInfo[0] ?? 0) > 4096 || ($inputInfo[1] ?? 0) > 4096) {
        throw new RuntimeException('A imagem precisa ser valida e ter no maximo 4096x4096 pixels.');
    }

    $inputMime = guild_mark_detect_mime($upload['tmp_name'], $inputInfo);
    if (!in_array($inputMime, ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/bmp'], true)) {
        throw new RuntimeException('Formato de imagem nao suportado. Use PNG, JPG, GIF, WEBP ou BMP.');
    }

    $source = @imagecreatefromstring((string)file_get_contents($upload['tmp_name']));
    if ($source === false) {
        throw new RuntimeException('Nao foi possivel ler o conteudo da imagem.');
    }

    $sourceWidth = imagesx($source);
    $sourceHeight = imagesy($source);
    $target = imagecreatetruecolor(16, 12);
    $background = imagecolorallocate($target, 0, 0, 0);
    imagefill($target, 0, 0, $background);

    $sourceRatio = $sourceWidth / $sourceHeight;
    $targetRatio = 16 / 12;
    if ($sourceRatio > $targetRatio) {
        $cropHeight = $sourceHeight;
        $cropWidth = (int)round($sourceHeight * $targetRatio);
        $sourceX = (int)floor(($sourceWidth - $cropWidth) / 2);
        $sourceY = 0;
    } else {
        $cropWidth = $sourceWidth;
        $cropHeight = (int)round($sourceWidth / $targetRatio);
        $sourceX = 0;
        $sourceY = (int)floor(($sourceHeight - $cropHeight) / 2);
    }

    imagecopyresampled($target, $source, 0, 0, $sourceX, $sourceY, 16, 12, $cropWidth, $cropHeight);
    $temporary = tempnam(dirname($destination), 'guildmark-');
    if ($temporary === false || !imagebmp($target, $temporary, false)) {
        if (PHP_VERSION_ID < 80500) {
            imagedestroy($source);
            imagedestroy($target);
        }
        throw new RuntimeException('Nao foi possivel converter a imagem para BMP.');
    }
    if (PHP_VERSION_ID < 80500) {
        imagedestroy($source);
        imagedestroy($target);
    }

    if (!guild_mark_validate_bmp($temporary)) {
        @unlink($temporary);
        throw new RuntimeException('A conversao nao produziu um BMP WYD valido.');
    }
    if (!@rename($temporary, $destination)) {
        @unlink($temporary);
        throw new RuntimeException('Nao foi possivel salvar a marca da guilda.');
    }
}
