<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'site-security.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'db.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

site_bootstrap('account');

error_reporting(0);
ini_set('display_errors', '0');

function validAccountName(string $value): bool
{
    return preg_match('/^(?=.*[A-Za-z])[A-Za-z0-9]{6,12}$/', $value) === 1;
}

function validPassword(string $value): bool
{
    return preg_match('/^[A-Za-z0-9]{6,10}$/', $value) === 1;
}

function changeDatabasePassword(string $accountName, string $oldPassword, string $newPassword, ?PDO $pdo): void
{
    if ($pdo === null) {
        throw new RuntimeException('O banco de contas esta temporariamente indisponivel.');
    }

    $pdo->beginTransaction();
    try {
        $select = $pdo->prepare('SELECT password_hash, password_scheme FROM accounts WHERE account_name = ? LIMIT 1');
        $select->execute([$accountName]);
        $row = $select->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row) || ($row['password_scheme'] ?? '') !== 'password_hash' || !password_verify($oldPassword, (string)($row['password_hash'] ?? ''))) {
            throw new RuntimeException('Usuario ou senha atual invalidos.');
        }

        $update = $pdo->prepare('UPDATE accounts SET password_hash = ?, password_scheme = ? WHERE account_name = ?');
        $update->execute([password_hash($newPassword, PASSWORD_DEFAULT), 'password_hash', $accountName]);
        if ($update->rowCount() !== 1) {
            throw new RuntimeException('Conta nao encontrada no banco de contas.');
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

$message = '';
$success = false;
$prefillUser = trim((string)($_GET['user'] ?? ''));

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    site_rate_limit('account-post', 10, 60);
    $csrf = (string)($_POST['csrf'] ?? '');
    $accountName = trim((string)($_POST['username'] ?? ''));
    $oldPassword = (string)($_POST['old_password'] ?? '');
    $newPassword = (string)($_POST['new_password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    if (!site_validate_csrf($csrf, 'account_csrf')) {
        $message = 'Sessao expirada. Recarregue a pagina e tente novamente.';
    } elseif (!validAccountName($accountName)) {
        $message = 'Usuario invalido. Use 6 a 12 caracteres alfanumericos.';
    } elseif (!validPassword($oldPassword) || !validPassword($newPassword)) {
        $message = 'As senhas devem ter de 6 a 10 caracteres alfanumericos.';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'A confirmacao da nova senha nao confere.';
    } elseif ($oldPassword === $newPassword) {
        $message = 'A nova senha deve ser diferente da senha atual.';
    } else {
        try {
            changeDatabasePassword($accountName, $oldPassword, $newPassword, OpenAccountDatabase());
            $message = 'Senha alterada com sucesso!';
            $success = true;
        } catch (Throwable $e) {
            $message = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel da Conta - <?php echo htmlspecialchars($SERVER_NAME); ?></title>
    <link rel="icon" type="image/png" href="images/logo.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Top Bar -->
    <div id="top_util_bar">
        <div class="ongame_network_links">
            <span style="color:#e69d30; font-weight:bold; margin-right:10px;"><?php echo htmlspecialchars($SERVER_NAME); ?>:</span>
            <a href="index.php">INÍCIO</a>
            <a href="account.php">PAINEL DA CONTA</a>
            <a href="guildmark.php">GUILDMARK</a>
            <a href="ranking.php">RANKING</a>
            <a href="download.php">DOWNLOADS</a>
            <a href="<?php echo htmlspecialchars($LINK_DISCORD); ?>" target="_blank">DISCORD</a>
        </div>
        <div class="audio_player_ctrl">
            <audio id="bgm_audio" src="audio/Login.mp3" loop preload="auto"></audio>
            <button id="btn_toggle_bgm" class="audio_btn">🔈 Tocar BGM Clássica</button>
        </div>
    </div>

    <div id="wrapper">
        <header id="header">
            <div class="logo_area">
                <img src="images/logo.png" alt="<?php echo htmlspecialchars($SERVER_NAME); ?> Logo" class="logo_wyd">
                <div class="logo_text">
                    <h1><?php echo htmlspecialchars($SERVER_NAME); ?></h1>
                    <p>Painel de Gerenciamento da Conta • Versão <?php echo htmlspecialchars($SERVER_VERSION); ?></p>
                </div>
            </div>

            <nav id="main_nav">
                <a href="index.php" class="nav_item">Início</a>
                <a href="account.php" class="nav_item active">Painel da Conta</a>
                <a href="guildmark.php" class="nav_item">Guildmark</a>
                <a href="ranking.php" class="nav_item">Ranking Top</a>
                <a href="download.php" class="nav_item">Downloads</a>
                <a href="<?php echo htmlspecialchars($LINK_DISCORD); ?>" target="_blank" class="nav_item">Discord</a>
            </nav>
        </header>

        <div id="main_container" style="justify-content: center;">
            <div style="width: 580px; margin: 20px 0;">
                <div class="retro_box">
                    <div class="box_header">
                        <h3>🔑 Alterar Senha da Conta</h3>
                        <span class="box_badge">SEGURANÇA</span>
                    </div>
                    <div class="box_content" style="padding: 20px;">

                        <?php if ($message): ?>
                            <div class="modal_msg <?php echo $success ? 'success' : 'error'; ?>" style="display:block; margin-bottom: 15px;">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="account.php">
                            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(site_csrf_token('account_csrf')); ?>">

                            <div class="modal_row">
                                <label for="username">Usuário da Conta:</label>
                                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($prefillUser); ?>" maxlength="12" placeholder="Seu login" required>
                            </div>

                            <div class="modal_row">
                                <label for="old_password">Senha Atual:</label>
                                <input type="password" name="old_password" id="old_password" maxlength="10" placeholder="••••••••" required>
                            </div>

                            <div class="modal_row">
                                <label for="new_password">Nova Senha (6 a 10 caracteres alfanuméricos):</label>
                                <input type="password" name="new_password" id="new_password" maxlength="10" placeholder="••••••••" required>
                            </div>

                            <div class="modal_row">
                                <label for="confirm_password">Confirmar Nova Senha:</label>
                                <input type="password" name="confirm_password" id="confirm_password" maxlength="10" placeholder="••••••••" required>
                            </div>

                            <button type="submit" class="btn_action" style="margin-top: 15px; width: 100%;">ALTERAR SENHA AGORA</button>
                        </form>

                        <div style="margin-top: 20px; border-top: 1px dashed #3a2e20; padding-top: 12px; font-size: 10px; color: #8f806e; line-height: 1.5;">
                            <strong>Aviso de Segurança:</strong><br>
                            A equipe do <?php echo htmlspecialchars($SERVER_NAME); ?> nunca solicitará sua senha em jogo ou por canais privados. Mantenha sua senha segura e não utilize a mesma senha de outros servidores.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer id="footer">
            <div class="footer_inner">
                <div class="footer_left">
                    <div class="og_logo_badge">CDK</div>
                    <div>
                        <div class="footer_copy">
                            <?php echo htmlspecialchars($SERVER_NAME); ?> • Painel Seguro da Conta.<br>
                            With Your Destiny é marca registrada da JoyImpact Co., Ltd.
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script src="js/main.js"></script>
</body>
</html>
