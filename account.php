<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'site-security.php';
site_bootstrap('account');
/*
 * Painel minimo da conta: altera a senha diretamente na tabela accounts.
 *
 * A senha nunca e exibida, registrada ou armazenada em texto puro no banco.
 */
error_reporting(0);
ini_set('display_errors', '0');

function openAccountDatabase(): ?PDO
{
    $secretPath = getenv('WYD_SITE_DB_SECRET');
    if ($secretPath === false || trim($secretPath) === '') {
        $secretPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Server' . DIRECTORY_SEPARATOR . 'secrets' . DIRECTORY_SEPARATOR . 'site-db.json';
    }
    if (!is_file($secretPath)) {
        return null;
    }
    if (!extension_loaded('pdo_mysql')) {
        throw new RuntimeException('O driver PDO MySQL nao esta habilitado.');
    }

    $config = json_decode((string)file_get_contents($secretPath), true);
    if (!is_array($config) || empty($config['host']) || empty($config['database']) || empty($config['username'])) {
        throw new RuntimeException('A configuracao do banco de contas esta invalida.');
    }

    $dsn = 'mysql:host=' . $config['host'] . ';port=' . (int)($config['port'] ?? 3306) .
        ';dbname=' . $config['database'] . ';charset=utf8mb4';
    return new PDO($dsn, $config['username'], (string)($config['password'] ?? ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

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
            changeDatabasePassword($accountName, $oldPassword, $newPassword, openAccountDatabase());
            $message = 'Senha alterada com sucesso.';
            $success = true;
        } catch (Throwable $exception) {
            $message = $exception->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="windows-1252">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alterar senha - WYD CDK</title>
    <style>
        body { margin: 0; background: #17120d; color: #f2e6d0; font: 16px Arial, sans-serif; }
        main { width: min(420px, calc(100% - 32px)); margin: 56px auto; padding: 24px; background: #2a2017; border: 1px solid #80633d; border-radius: 8px; box-sizing: border-box; }
        h1 { margin-top: 0; color: #e6bc72; font-size: 24px; }
        label { display: block; margin: 14px 0 6px; }
        input { width: 100%; box-sizing: border-box; padding: 10px; border: 1px solid #80633d; border-radius: 4px; background: #fffaf0; color: #24180e; }
        button { margin-top: 20px; width: 100%; padding: 11px; border: 0; border-radius: 4px; background: #b98032; color: #fff; font-weight: bold; cursor: pointer; }
        .message { padding: 10px; margin-bottom: 16px; background: #3a2b1c; border-left: 4px solid #b98032; }
        .success { border-left-color: #73b46b; }
        small { color: #cbb99d; }
        a { color: #e6bc72; }
    </style>
</head>
<body>
<main>
    <h1>Alterar senha</h1>
    <?php if ($message !== ''): ?>
        <div class="message<?php echo $success ? ' success' : ''; ?>"><?php echo htmlspecialchars($message, ENT_QUOTES, 'Windows-1252'); ?></div>
    <?php endif; ?>
    <p><small>A senha deve ter de 6 a 10 caracteres alfanumericos.</small></p>
    <form method="post" autocomplete="off">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(site_csrf_token('account_csrf'), ENT_QUOTES, 'Windows-1252'); ?>">
        <label for="username">Usuario</label>
        <input id="username" name="username" type="text" minlength="6" maxlength="12" required>
        <label for="old_password">Senha atual</label>
        <input id="old_password" name="old_password" type="password" minlength="6" maxlength="10" required>
        <label for="new_password">Nova senha</label>
        <input id="new_password" name="new_password" type="password" minlength="6" maxlength="10" required>
        <label for="confirm_password">Confirmar nova senha</label>
        <input id="confirm_password" name="confirm_password" type="password" minlength="6" maxlength="10" required>
        <button type="submit">Alterar senha</button>
    </form>
    <p><a href="index.php">Voltar</a></p>
</main>
</body>
</html>
