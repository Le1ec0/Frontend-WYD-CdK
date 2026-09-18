<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'site-security.php';
site_bootstrap('captcha', 30, 60);
$codigoCaptcha = substr(md5(time()), 0, 4);
$_SESSION['captcha'] = $codigoCaptcha;
$imagemCaptcha = imagecreatefrompng(__DIR__ . DIRECTORY_SEPARATOR . 'fundocaptch.png');
$fonteCaptcha = imageloadfont(__DIR__ . DIRECTORY_SEPARATOR . 'anonymous.gdf');
$corCaptcha = imagecolorallocate($imagemCaptcha, 24, 68, 33);
imagestring($imagemCaptcha, $fonteCaptcha, 10, 4, $codigoCaptcha, $corCaptcha);
header('Content-type: image/png');
imagepng($imagemCaptcha);
if (PHP_VERSION_ID < 80500) {
    imagedestroy($imagemCaptcha);
}
?>
