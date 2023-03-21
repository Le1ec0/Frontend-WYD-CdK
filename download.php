<style type="text/css">
<!--
body,td,th {
   color: #ffffff;
font-family: Arial;
}
body {
   
   background-image: url();
}
-->
</style>
<?php
error_reporting(0);
/*
Logica Guild Marck:

b030(00000+$guildid).bmp
b030(00000+$guildid).bmp
*/
$Titulo_do_Site = "Cavaleiros de Kersef";
$guildid = $_POST['guildid'];
$img = "./img/guilds/b0".(3000000+$guildid).".bmp";
if (isset($_FILES['arquivo']['name']))
{
$uploaddir = '.\\img/guilds\\';
$arquivo = $uploaddir."b0".(3000000+$guildid).".bmp";
$dimensao = getimagesize($_FILES['arquivo']['tmp_name']);
if($_FILES['arquivo']["type"] == "image/bmp")
{
if(($dimensao[0] <= 16) && ($dimensao[1] <= 12))
{
if($_FILES['arquivo']["size"] <= 2000)
{
if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $arquivo))
{
copy($arquivo,$uploaddir."b0".(2000000+$guildid).".bmp");
copy($arquivo,$uploaddir."b0".(1000000+$guildid).".bmp");
echo "O arquivo foi enviado com sucesso.<br>";
$img = "guilds/b0".(3000000+$guildid).".bmp";
}
else
{
echo"[Error]: O arquivo não foi enviado.<br>";
}
}
else
{
echo"[Error]: Imagem muito pesada.<br>";
}
}
else
{
echo"[Error]: Imagem muito grande.<br>";
}
}
else
{
echo"[Error]: Formato de imagem invalida.<br>";
}
}
?>
<br>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html id="" class=" domloaded" xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="https://www.facebook.com/2008/fbml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title><?php echo $Titulo_do_Site; ?></title>
<script type="text/javascript" async="" src="./RegisterResource/js/ga.js"></script>
<script type="text/javascript" src="./RegisterResource/js/registration_login_sdasgard.js"></script>
<script type="text/javascript" src="./RegisterResource/js/login_asgard.js"></script>
<script type="text/javascript" src="./RegisterResource/js/gsap/TweenMax.min.js"></script>
<script type="text/javascript" src="./RegisterResource/js/gsap/plugins/Physics2DPlugin.min.js"></script>
<script type="text/javascript" src="./RegisterResource/js/cadastro_sdasgard.js"></script>
<script type="text/javascript" src="./RegisterResource/js/particles/particle-system.js"></script>
</head>
<body onload="myFunction()" style="opacity: 0;">
<div id="content">
<div class="main">    
<form method="post" enctype="multipart/form-data">
<table width="452" border="0">
<font color="#CEB798"><b>A Imagem deve ser 16x12 formato BMP 24bits </b><br /></font>
<br>
<br>
<tr>
<td width="147" align="center"><b>Guild ID:</b></td>
<td width="218" align="center"><input name="guildid" type="text" /></td>
<td width="73" align="center"></td>
</tr>
<tr>
<td align="center"><b>Imagem:<b></td>
<td align="center"><input name="arquivo" type="file" /></td>
<p>
</tr>
<tr>
<td align="center"><input type="submit" value="Confirmar" /></td>
</tr>
</table>
</form>
<font color="#CEB798"><b>Para descobrir seu ID da sua Guild digite /guild, e ira aparecer o ID da Guild.</b><br /></font>
</div>
<br />
</body>
</form>
