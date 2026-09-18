<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'site-security.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'guild-mark.php';
site_bootstrap('guildmark', 60, 60);

error_reporting(0);
ini_set('display_errors', '0');

function openGuildDatabase(): ?PDO
{
    $secretPath = getenv('WYD_SITE_DB_SECRET');
    if ($secretPath === false || trim($secretPath) === '') {
        $secretPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Server' . DIRECTORY_SEPARATOR . 'secrets' . DIRECTORY_SEPARATOR . 'site-db.json';
    }
    if (!is_file($secretPath) || !extension_loaded('pdo_mysql')) {
        return null;
    }

    $config = json_decode((string)file_get_contents($secretPath), true);
    if (!is_array($config) || empty($config['host']) || empty($config['database']) || empty($config['username'])) {
        return null;
    }

    $dsn = 'mysql:host=' . $config['host'] . ';port=' . (int)($config['port'] ?? 3306) . ';dbname=' . $config['database'] . ';charset=utf8mb4';
    return new PDO($dsn, $config['username'], (string)($config['password'] ?? ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function readGuildLeaders(PDO $pdo, string $accountName, string $world): array
{
    $query = $pdo->prepare('SELECT account_blob FROM wyd_world_account_state WHERE account_name = ? AND world_key = ? LIMIT 1');
    $query->execute([$accountName, $world]);
    $blob = $query->fetchColumn();
    if (!is_string($blob) || strlen($blob) < 7945) {
        return [];
    }

    $leaders = [];
    for ($slot = 0; $slot < 4; $slot++) {
        $offset = 216 + ($slot * 816);
        $name = trim((string)strtok(substr($blob, $offset, 16), "\0"));
        $guildBytes = substr($blob, $offset + 18, 2);
        $guildId = strlen($guildBytes) === 2 ? unpack('v', $guildBytes)[1] : 0;
        $guildLevel = ord($blob[$offset + 800] ?? "\0");
        if ($name !== '' && $guildId > 0 && $guildLevel >= 9) {
            $leaders[(string)$guildId] = ['id' => $guildId, 'name' => $name, 'slot' => $slot];
        }
    }
    return array_values($leaders);
}

function listGuildMarkCatalog(string $directory): array
{
    if (!is_dir($directory)) {
        return [];
    }

    $catalog = [];
    foreach (glob($directory . DIRECTORY_SEPARATOR . '*.bmp') ?: [] as $path) {
        if (guild_mark_validate_bmp($path)) {
            $catalog[] = basename($path);
        }
    }
    sort($catalog, SORT_NATURAL | SORT_FLAG_CASE);
    return $catalog;
}

function copyGuildMarkVariants(string $source, string $directory, int $guildId): void
{
    if (!guild_mark_validate_bmp($source)) {
        throw new RuntimeException('A marca selecionada nao e um BMP WYD valido.');
    }
    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
        throw new RuntimeException('Nao foi possivel preparar o diretorio da guilda.');
    }

    foreach ([1000000, 2000000, 3000000] as $prefix) {
        $destination = $directory . DIRECTORY_SEPARATOR . 'b0' . (string)($prefix + $guildId) . '.bmp';
        $temporary = tempnam($directory, 'guildmark-out-');
        if ($temporary === false || !copy($source, $temporary) || !rename($temporary, $destination)) {
            if ($temporary !== false) {
                @unlink($temporary);
            }
            throw new RuntimeException('Nao foi possivel salvar todas as variantes da marca.');
        }
    }
}

$message = '';
$success = false;
$database = null;
$accountName = (string)($_SESSION['guildmark_account'] ?? '');
$world = (string)($_SESSION['guildmark_world'] ?? 'UP');
if (!in_array($world, ['UP', 'PVP'], true)) {
    $world = 'UP';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    site_rate_limit('guildmark-post', 12, 60);
    $csrf = (string)($_POST['csrf'] ?? '');
    $action = (string)($_POST['action'] ?? '');
    if (!site_validate_csrf($csrf, 'guildmark_csrf')) {
        $message = 'Sessao expirada. Recarregue a pagina e tente novamente.';
    } elseif ($action === 'login') {
        $login = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $loginWorld = (string)($_POST['world'] ?? 'UP');
        if (!preg_match('/^(?=.*[A-Za-z])[A-Za-z0-9]{6,12}$/', $login) || strlen($password) > 128 || !in_array($loginWorld, ['UP', 'PVP'], true)) {
            $message = 'Dados de acesso invalidos.';
        } else {
            try {
                $database = openGuildDatabase();
                $select = $database?->prepare('SELECT password_hash, password_scheme FROM accounts WHERE account_name = ? LIMIT 1');
                $select?->execute([$login]);
                $row = $select?->fetch(PDO::FETCH_ASSOC);
                if (!is_array($row) || ($row['password_scheme'] ?? '') !== 'password_hash' || !password_verify($password, (string)($row['password_hash'] ?? ''))) {
                    $message = 'Dados de acesso invalidos.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['guildmark_account'] = $login;
                    $_SESSION['guildmark_world'] = $loginWorld;
                    $accountName = $login;
                    $world = $loginWorld;
                    $message = 'Login realizado. Selecione uma guilda liderada por voce.';
                }
            } catch (Throwable) {
                $message = 'O banco de contas esta temporariamente indisponivel.';
            }
        }
    } elseif ($action === 'logout') {
        unset($_SESSION['guildmark_account'], $_SESSION['guildmark_world']);
        $accountName = '';
        $message = 'Sessao encerrada.';
    } elseif ($action === 'save' && $accountName !== '') {
        $selectedWorld = (string)($_POST['world'] ?? 'UP');
        $selectedGuild = filter_var($_POST['guild_id'] ?? null, FILTER_VALIDATE_INT);
        if (!in_array($selectedWorld, ['UP', 'PVP'], true) || $selectedGuild === false || $selectedGuild < 1 || $selectedGuild > 65535) {
            $message = 'Guilda ou mundo invalido.';
        } else {
            try {
                $database ??= openGuildDatabase();
                $leaders = $database === null ? [] : readGuildLeaders($database, $accountName, $selectedWorld);
                $leaderIds = array_map(static fn(array $leader): int => (int)$leader['id'], $leaders);
                if (!in_array((int)$selectedGuild, $leaderIds, true)) {
                    $message = 'Somente o lider da guilda pode trocar a marca.';
                } else {
                    $guildDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'guilds' . DIRECTORY_SEPARATOR . $selectedWorld;
                    $source = null;
                    $selectedCatalog = basename((string)($_POST['catalog_file'] ?? ''));
                    $catalogDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'guilds' . DIRECTORY_SEPARATOR . 'catalog';
                    if ($selectedCatalog !== '' && preg_match('/^[A-Za-z0-9._-]+\.bmp$/i', $selectedCatalog)) {
                        $candidate = realpath($catalogDirectory . DIRECTORY_SEPARATOR . $selectedCatalog);
                        $catalogRoot = realpath($catalogDirectory);
                        if ($candidate !== false && $catalogRoot !== false && str_starts_with(strtolower($candidate), strtolower($catalogRoot . DIRECTORY_SEPARATOR))) {
                            $source = $candidate;
                        }
                    }

                    $temporaryUpload = null;
                    if (isset($_FILES['arquivo']) && (int)($_FILES['arquivo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                        if (!is_dir($guildDirectory) && !mkdir($guildDirectory, 0750, true) && !is_dir($guildDirectory)) {
                            throw new RuntimeException('Nao foi possivel preparar o diretorio da guilda.');
                        }
                        $temporaryUpload = tempnam($guildDirectory, 'guildmark-input-');
                        if ($temporaryUpload === false) {
                            throw new RuntimeException('Nao foi possivel preparar o upload.');
                        }
                        guild_mark_convert_upload($_FILES['arquivo'], $temporaryUpload);
                        $source = $temporaryUpload;
                    }

                    if ($source === null) {
                        throw new RuntimeException('Escolha uma marca pronta ou envie uma imagem.');
                    }
                    copyGuildMarkVariants($source, $guildDirectory, (int)$selectedGuild);
                    if ($temporaryUpload !== null) {
                        @unlink($temporaryUpload);
                    }
                    $world = $selectedWorld;
                    $success = true;
                    $message = 'Marca da guilda atualizada com sucesso.';
                }
            } catch (Throwable $exception) {
                if (isset($temporaryUpload) && is_string($temporaryUpload)) {
                    @unlink($temporaryUpload);
                }
                $message = $exception->getMessage();
            }
        }
    }
}

try {
    $database ??= $accountName !== '' ? openGuildDatabase() : null;
    $leaders = $database === null || $accountName === '' ? [] : readGuildLeaders($database, $accountName, $world);
} catch (Throwable) {
    $leaders = [];
}
$catalog = listGuildMarkCatalog(__DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'guilds' . DIRECTORY_SEPARATOR . 'catalog');
$csrf = site_csrf_token('guildmark_csrf');
function escGuildMark(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Marca da guilda - WYD CDK</title>
    <style>
        body { margin: 0; background: #17120d; color: #f2e6d0; font: 16px Arial, sans-serif; }
        main { width: min(760px, calc(100% - 32px)); margin: 36px auto; padding: 24px; background: #2a2017; border: 1px solid #80633d; border-radius: 8px; box-sizing: border-box; }
        h1 { margin-top: 0; color: #e6bc72; }
        label { display: block; margin: 14px 0 6px; }
        input, select { width: 100%; box-sizing: border-box; padding: 10px; border: 1px solid #80633d; border-radius: 4px; background: #fffaf0; color: #24180e; }
        button { margin-top: 16px; padding: 10px 16px; border: 0; border-radius: 4px; background: #b98032; color: #fff; font-weight: bold; cursor: pointer; }
        .message { padding: 10px; margin-bottom: 16px; background: #3a2b1c; border-left: 4px solid #b98032; }
        .success { border-left-color: #73b46b; }
        small { color: #cbb99d; }
        .topline { display: flex; justify-content: space-between; gap: 12px; align-items: center; }
        .logout { display: inline; }
        .logout button { margin-top: 0; background: #684332; }
    </style>
</head>
<body>
<main>
    <h1>Marca da guilda</h1>
    <?php if ($message !== ''): ?><div class="message<?php echo $success ? ' success' : ''; ?>"><?php echo escGuildMark($message); ?></div><?php endif; ?>
    <?php if ($accountName === ''): ?>
        <p>Entre com a conta do jogo para verificar automaticamente as guildas lideradas por voce.</p>
        <form method="post" autocomplete="off">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf" value="<?php echo escGuildMark($csrf); ?>">
            <label for="username">Login</label>
            <input id="username" name="username" type="text" minlength="6" maxlength="12" required>
            <label for="password">Senha</label>
            <input id="password" name="password" type="password" maxlength="128" required>
            <label for="world">Mundo</label>
            <select id="world" name="world"><option value="UP">UP</option><option value="PVP">PVP</option></select>
            <button type="submit">Entrar</button>
        </form>
    <?php else: ?>
        <div class="topline">
            <p>Conta: <strong><?php echo escGuildMark($accountName); ?></strong></p>
            <form class="logout" method="post"><input type="hidden" name="action" value="logout"><input type="hidden" name="csrf" value="<?php echo escGuildMark($csrf); ?>"><button type="submit">Sair</button></form>
        </div>
        <?php if ($leaders === []): ?>
            <p>Nenhum personagem lider de guilda foi encontrado neste mundo.</p>
        <?php else: ?>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="csrf" value="<?php echo escGuildMark($csrf); ?>">
                <label for="save-world">Mundo</label>
                <select id="save-world" name="world"><option value="UP"<?php echo $world === 'UP' ? ' selected' : ''; ?>>UP</option><option value="PVP"<?php echo $world === 'PVP' ? ' selected' : ''; ?>>PVP</option></select>
                <label for="guild-id">Guilda liderada</label>
                <select id="guild-id" name="guild_id" required><?php foreach ($leaders as $leader): ?><option value="<?php echo (int)$leader['id']; ?>">#<?php echo (int)$leader['id']; ?> — <?php echo escGuildMark((string)$leader['name']); ?></option><?php endforeach; ?></select>
                <label for="catalog-file">Escolher marca pronta</label>
                <select id="catalog-file" name="catalog_file"><option value="">— ou envie uma imagem abaixo —</option><?php foreach ($catalog as $file): ?><option value="<?php echo escGuildMark($file); ?>"><?php echo escGuildMark(pathinfo($file, PATHINFO_FILENAME)); ?></option><?php endforeach; ?></select>
                <label for="arquivo">Enviar imagem própria</label>
                <input id="arquivo" name="arquivo" type="file" accept="image/png,image/jpeg,image/gif,image/webp,image/bmp">
                <small>A imagem será convertida automaticamente para BMP 16×12, 24-bit e até 2 KB.</small><br>
                <button type="submit">Salvar marca</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</main>
</body>
</html>
