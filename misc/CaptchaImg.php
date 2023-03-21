<?php
session_start();
$codigoCaptcha = substr(md5( time()) ,0,4);
$_SESSION['captcha'] = $codigoCaptcha;
$imagemCaptcha = imagecreatefrompng("fundocaptch.png");
$fonteCaptcha = imageloadfont("anonymous.gdf");
$corCaptcha = imagecolorallocate($imagemCaptcha,24,68,33);
imagestring($imagemCaptcha,$fonteCaptcha,10,4,$codigoCaptcha,$corCaptcha);
header("Content-type: image/png");
imagepng($imagemCaptcha);
imagedestroy($imagemCaptcha);
?>