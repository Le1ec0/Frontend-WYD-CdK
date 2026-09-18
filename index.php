<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'site-security.php';
site_bootstrap('index');
/*
*  BASE registro WYD (PHP)
*  Criado por seitbnao - djunio_rmf@hotmail.com
*/
$httpHost = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
$httpHost = preg_replace('/:\d+$/', '', $httpHost);
if ($httpHost === '124.198.128.225')
{
    $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
    header('Location: https://www.wydcdk.com.br' . $requestUri, true, 301);
    exit;
}
error_reporting(0);
$Titulo_do_Site = "WYD CDK";
$Slogan = "Crie aqui sua conta! UP / PVP";
//user 7556 se for 7556 se for emulador 7663 use 7663
$LinkDownload = "#";
$LinkInfo = "#";
$LinkRegras = "#";
$Copyright = 'Copyright 2020. All Rights Reserved.  JOYIMPACT Co, Ltd. - All Rights Reserved, Published by Vibrant Communications Limited.';
/* Fun��o que retorna o primeiro caractere da conta  */
function OpenAccountDatabase()
{
    $secretPath = getenv('WYD_SITE_DB_SECRET');
    if ($secretPath === false || trim($secretPath) === '')
    {
        $secretPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Server' . DIRECTORY_SEPARATOR . 'secrets' . DIRECTORY_SEPARATOR . 'site-db.json';
    }
    if (!is_file($secretPath))
    {
        return null;
    }
    if (!extension_loaded('pdo_mysql'))
    {
        throw new RuntimeException('O driver PDO MySQL nao esta habilitado.');
    }
    $config = json_decode((string)file_get_contents($secretPath), true);
    if (!is_array($config) || empty($config['host']) || empty($config['database']) || empty($config['username']))
    {
        throw new RuntimeException('A configuracao do banco de contas esta invalida.');
    }
    $dsn = 'mysql:host=' . $config['host'] . ';port=' . (int)($config['port'] ?? 3306) . ';dbname=' . $config['database'] . ';charset=utf8mb4';
    return new PDO($dsn, $config['username'], (string)($config['password'] ?? ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
$Msg = '';
$Status = false;
if(isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirm']) && isset($_POST['captcha']))
{
if (!site_validate_csrf((string)($_POST['csrf'] ?? ''))) {
$Status = true;
$Msg .= 'Sessao expirada. Recarregue a pagina e tente novamente <br><br>';
goto Fim;
}
$Login = trim($_POST['username']);
$Senha = trim($_POST['password']);
$cSenha = trim($_POST['confirm']);
$Email = trim($_POST['email']);
$Captcha = trim($_POST['captcha']);
$IPAddress = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if(strlen($Login) < 6 || strlen($Login) > 12)
{
$Status = true;
$Msg .= 'O usuario deveria conter ao menos uma letra O usuario deve ter entre 6 e 12 caracteres <br><br>';
}
if(strlen($Senha) < 6 || strlen($Senha) > 10)
{
$Status = true;
$Msg .= 'A senha deve ter entre 6 e 10 caracteres A senha nao deve conter caracteres especiais <br><br>';
}
if(strcmp($Senha,$cSenha) != 0)
{
$Status = true;
$Msg .= 'A confirmacao da senha deve ser igual a senha<br><br>';
}
if(!filter_var($Email, FILTER_VALIDATE_EMAIL))
{
$Status = true;
$Msg .= 'O email ja existe Email invalido <br><br>';
}
if($_SESSION['captcha']  != $Captcha)
{
$Status = true;
$Msg .= 'O codigo de verificacao esta errado <br><br>';
}
if($Status)
goto Fim;
$pdo = null;
try
{
    $pdo = OpenAccountDatabase();
    if ($pdo !== null)
    {
        $checkAccount = $pdo->prepare('SELECT id FROM accounts WHERE account_name = ? LIMIT 1');
        $checkAccount->execute([$Login]);
        if ($checkAccount->fetchColumn() !== false)
        {
            $Status = true;
            $Msg = 'Conta existente <br><br>';
            goto Fim;
        }
    }
}
catch (Throwable $e)
{
    $Status = true;
    $Msg = 'O banco de contas esta temporariamente indisponivel <br><br>';
    goto Fim;
}
$AccCreate = false;
if(!$Status && $pdo !== null)
{
    try
    {
        $insertAccount = $pdo->prepare('INSERT INTO accounts (account_name, password_hash, password_scheme, numeric_token, email, registration_ip) VALUES (?, ?, ?, ?, ?, ?)');
        $insertAccount->execute([$Login, password_hash($Senha, PASSWORD_DEFAULT), 'password_hash', hex2bin('FFFFFFFFFFFF'), $Email, $IPAddress]);
        $AccCreate = true;
    }
    catch (Throwable $e)
    {
        $AccCreate = false;
        $Status = true;
        $Msg = 'Nao foi possivel salvar a conta no banco <br><br>';
    }
}
Fim:
if(!$Status && $AccCreate)
{
$Msg = "Conta criada com sucesso! $Login<br><br>";
$Status = true;
}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html id="" class=" domloaded" xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="https://www.facebook.com/2008/fbml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title><?php echo $Titulo_do_Site; ?></title>
<link rel="stylesheet" type="text/css" href="./RegisterResource/css/default.css">
<link rel="stylesheet" type="text/css" href="./RegisterResource/css/friend.css">
<script type="text/javascript" async="" src="./RegisterResource/js/ga.js"></script>
<script type="text/javascript" src="./RegisterResource/js/registration_login_sdasgard.js"></script>
<script type="text/javascript" src="./RegisterResource/js/login_asgard.js"></script>
<script type="text/javascript" src="./RegisterResource/js/gsap/TweenMax.min.js"></script>
<script type="text/javascript" src="./RegisterResource/js/gsap/plugins/Physics2DPlugin.min.js"></script>
<script type="text/javascript" src="./RegisterResource/js/cadastro_sdasgard.js"></script>
<script type="text/javascript" src="./RegisterResource/js/particles/particle-system.js"></script>
</head>
<body>
<div id="content">
<div id="fb-root" class=" fb_reset"><script type="text/javascript" src="./js/all.js" async=""></script><div style="position: absolute; top: -10000px; height: 0px; width: 0px;"><div></div></div><div style="position: absolute; top: -10000px; height: 0px; width: 0px;"><div><iframe name="fb_xdm_frame_http" frameborder="0" allowtransparency="true" allowfullscreen="true" scrolling="no" title="Facebook Cross Domain Communication Frame" aria-hidden="true" tabindex="-1" id="fb_xdm_frame_http"  style="border: none;"></iframe><iframe name="fb_xdm_frame_https" frameborder="0" allowtransparency="true" allowfullscreen="true" scrolling="no" title="Facebook Cross Domain Communication Frame" aria-hidden="true" tabindex="-1" id="fb_xdm_frame_https" style="border: none;"></iframe></div></div></div>
<div id="vibrantLogo" alt=""></div>
<div class="head"></div>
<div id="headerBox">
<div id="header"></div>
</div>
<div id="imageBox">
<div align="center" id="image"></div>
</div>
<div class="main">     
<?php if($Status == false) { ?>
<form id="registrationForm_sd" name="registrationForm_sd" method="post">
 <div class="formbox">
<table border="0" cellspacing="0" cellpadding="0" align="center">
<tbody>
<tr id="serverOficialRow">
<td><?php echo $Slogan; ?></td>      
</tr>
<tr>
<td><input type="text" placeholder="Usuario" class="input_s" name="username" id="username" onblur="checkAvailablity()" onkeypress="return submitenterSignup(this,event)"></td>
</tr>
<tr id="userErrors" class="p_info">
<td>
<span class="error" id="userPart">O usuario e obrigatorio</span>
<span class="error" id="userAlpha">O usuario deveria conter ao menos uma letra</span>
<span class="error" id="userLPart">O usuario deve ter entre 6 e 12 caracteres</span>
<span class="error" id="userChars">Nao e possi�vel entrar com caracteres especiais no usuario</span>
<span class="error" id="userNotAvail">Usuario nao disponi�vel</span>
</td>
</tr>
<tr>
<td><input type="text" class="input_s" placeholder="E-mail" id="email" name="email" onblur="return checkAvailabilityEmail()" onkeypress="return submitenterSignup(this,event)"></td>
</tr>
<tr id="emailErrors" class="p_info">
<td>
<span class="error" id="emailPart">O email e obrigatorio</span>
<span class="error" id="emailNotAvail">O email ja existe</span>
<span class="error" id="emailVPart">Email invalido</span>
</td>
</tr>
<tr>
<td><input type="password" class="input_s" placeholder="Senha" name="password" id="password" onblur="return checkAvailabilityPwd()" onkeypress="return submitenterSignup(this,event)"></td>
</tr>
<tr id="passwordErrors" class="p_info">
<td>
<span class="error" id="pwdPart">A senha e obrigatoria</span>
<span class="error" id="pwdLPart">A senha deve ter entre 6 e 10 caracteres</span>
<span class="error" id="pwdChars">A senha nao deve conter caracteres especiais</span>
<span class="error" id="pwdAlpha">A senha deve conter ao menos uma letra</span>
</td>
</tr>
<tr>
<td><input type="password" class="input_s" placeholder="Confirmar a senha" name="confirm" id="confirm" onblur="return checkConfrmPswd()" onkeypress="return submitenterSignup(this,event)"></td>
</tr>
 <tr id="confirmPasswordErrors" class="p_info">
<td>
<span class="error" id="confrmPart">Confirmar a senha e obrigatorio</span>
<span class="error" id="confrmNotEq">A confirmacao da senha deve ser igual a senha</span>
</td>
</tr>
<tr>
<td id="codigo_label">Codigo de Verificacao:</td>
</tr>
<tr>
<td>
<input type="text" name="captcha" id="captcha" maxlength="4" onblur="checkImg();">
<img src="./misc/CaptchaImg.php" id="img" style="float:left" alt="Captcha" height="25" width="55"></img>
<a href="#" onclick="return reloadImg('img');" id="newCaptchaImg">
<div id="refreshButton"></div>
</a>
</td>
</tr>
<tr id="captchaErrors" class="p_info">
<td>
<span class="error" id="captchaerror">O codigo de verificacao esta errado</span>
</td>
</tr>
<tr>
<td>
<input name="chkPolicy" type="checkbox" id="chkPolicy" value="checked">
<p id="termosDeUso">Eu concordo com os <a href="<?php echo $LinkRegras; ?>" target="_blank">Termos de Uso</a></p>
</td>
</tr>
<tr id="checkPolicyErrors" class="p_info">
<td>
<span class="error" id="chkPart">Por favor aceite os termos de uso</span>
</td>
</tr>
<input type="hidden" name="strCallingProgram" value="Vibrantgames_Registration">
<input type="hidden" name="csrf" value="<?php echo htmlspecialchars(site_csrf_token(), ENT_QUOTES, 'Windows-1252'); ?>">
<input type="hidden" name="fwdSite" value="register" />
<input type="hidden" name="IPAddress" id="IPAddress" value="<?php echo $_SERVER["REMOTE_ADDR"];  ?>" />
<tr>
<td><p class="btn_playnow">
<input id="register_button" type="submit"  value=""  onclick="javascript:f_login();" onfocus="this.blur()" /></p></td> 
</tr>
<tr>
 <td>Ja tem uma conta? <a href="https://drive.google.com/file/d/1fnpaIwSxDEMeRLwzLMynpscx4MzmMYHI/"target="__blank">Baixe o jogo (Google Drive)</a></td>
</tr>
<tr>
 <td>Alterar senha? <a href="account.php">Clique Aqui</a></td>
</tr>
<tr>
<td>Inserir Guildmark? <a href="guildmark.php" target="__blank">Clique Aqui</a></td>
</tr>
<tr>
<td>Entre no nosso grupo do Discord! <a href="https://discord.com/invite/YMRDaV4" target="__blank">Clique Aqui</a></td>
</tr>
<td>Pacote Visual C++ All in one (32bit e 64bit) <a href="http://wydcdkpvp.servegame.com/MS Visual C++ Redist. AIO v0.29.0 (x86_x64).exe" target="__blank">Clique Aqui</a></td>
<tr>
<td>Download WinRAR (64bit BR) <a href="https://www.rarlab.com/rar/winrar-x64-580br.exe" target="__blank">Clique Aqui</a></td>
                </tr>
      	      </tbody>
            </table>
          </div>
        </form>
        <?php } else
Menssage:
if($Msg != ''){
echo '<center><br><br><br><br><br><br><br><br><br><br><br><br>'.$Msg.'';
echo '<a href="JavaScript: window.history.back();">Voltar</a></center>';
}
?>
<div class="buttondiv">
<div class="buttonBox">
<a href="<?php echo $LinkDownload; ?>" id="downloadGame"></a>
<a href="<?php echo $LinkInfo; ?>" id="sobreJogo"></a>
</div>
</div>
</div>
<div id="posContent"></div>
</div>
<div class="foot"><?php echo $Copyright; ?></div>
<script>
function register() {
replaceTargetWith( 'register_button', '<img id="register_button" src="./RegisterResource/confirmar1.png" >' );
var ret = submitSignUpStepwise();
if (ret) {
return ret;
} else {
 replaceTargetWith( 'register_button', '<input id="register_button" type="button" name="" onclick="register();">' );
return ret;
}
}
function replaceTargetWith( targetID , html ) {
/// find our target
var i, tmp, elm, last, target = document.getElementById(targetID);
/// create a temporary div or tr (to support tds)
tmp = document.createElement(html.indexOf('<td')!=-1?'tr':'div');
/// fill that div with our html, this generates our children
tmp.innerHTML = html;
/// step through the temporary div's children and insertBefore our target
i = tmp.childNodes.length;
/// the insertBefore method was more complicated than I first thought so I
/// have improved it. Have to be careful when dealing with child lists as
/// they are counted as live lists and so will update as and when you make
/// changes. This is why it is best to work backwards when moving children
/// around, and why I'm assigning the elements I'm working with to `elm`
/// and `last`
last = target;
while(i--){
target.parentNode.insertBefore((elm = tmp.childNodes[i]), last);
last = elm;
}
/// remove the target.
 target.parentNode.removeChild(target);
}
</script>
</body>
</html>
