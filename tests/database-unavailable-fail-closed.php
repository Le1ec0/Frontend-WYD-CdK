<?php
declare(strict_types=1);

$siteRoot = dirname(__DIR__);
$temporaryRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wydcdk-site-db-test-' . bin2hex(random_bytes(8));
if (!mkdir($temporaryRoot, 0700, true)) {
    fwrite(STDERR, "FAIL: could not create isolated test directory\n");
    exit(1);
}

$secretPath = $temporaryRoot . DIRECTORY_SEPARATOR . 'site-db.json';
file_put_contents($secretPath, '{}');
putenv('WYD_SITE_DB_SECRET=' . $secretPath);

function failTest(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function requireTest(bool $condition, string $message): void
{
    if (!$condition) {
        failTest($message);
    }
}

function runRoute(string $route, array $post, array $session): array
{
    global $siteRoot;

    if (!in_array($route, ['guildmark', 'registration'], true)) {
        failTest('unknown worker route');
    }

    session_id('wydcdk' . bin2hex(random_bytes(12)));
    session_start();
    foreach ($session as $key => $value) {
        $_SESSION[$key] = $value;
    }
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_POST = $post;

    ob_start();
    require $siteRoot . DIRECTORY_SEPARATOR . ($route === 'guildmark' ? 'guildmark.php' : 'index.php');
    $body = (string)ob_get_clean();
    return [$body, $_SESSION, get_defined_vars()];
}

try {
    if (($argv[1] ?? '') === '--worker') {
        $scenario = $argv[2] ?? '';
        if ($scenario === 'guildmark-login') {
            [, $session] = runRoute('guildmark', [
                'action' => 'login',
                'username' => 'ProbeUser1',
                'password' => 'WrongPass1',
                'world' => 'UP',
                'csrf' => 'test-csrf',
            ], ['guildmark_csrf' => 'test-csrf']);
            requireTest(!isset($session['guildmark_account']), 'guildmark login established a session without the database');
        } elseif ($scenario === 'guildmark-save') {
            [$body, $session] = runRoute('guildmark', [
                'action' => 'save',
                'guild_id' => '123',
                'world' => 'UP',
                'catalog_file' => '',
                'csrf' => 'test-csrf',
            ], [
                'guildmark_csrf' => 'test-csrf',
                'guildmark_account' => 'ProbeUser1',
                'guildmark_world' => 'UP',
            ]);
            requireTest(isset($session['guildmark_account']), 'save test did not exercise a pre-existing session');
            requireTest(str_contains($body, 'banco de dados') && str_contains($body, 'indisponível'), 'guildmark save did not fail closed when its database was unavailable');
            requireTest(!str_contains($body, 'atualizada com sucesso'), 'guildmark save succeeded without database authorization');
        } elseif ($scenario === 'registration') {
            [$body, , $scope] = runRoute('registration', [
                'action' => 'cadastro',
                'username' => 'ProbeUser1',
                'email' => 'probe@example.invalid',
                'password' => 'Pass1234',
                'confirm' => 'Pass1234',
                'captcha' => 'test-captcha',
                'csrf' => 'test-csrf',
            ], ['site_csrf' => 'test-csrf', 'captcha' => 'test-captcha']);
            requireTest(($scope['AccCreate'] ?? false) === false, 'registration reported success without persisting an account');
            requireTest(str_contains($body, 'indisponível'), 'registration did not report the unavailable database');
            requireTest(!str_contains($body, 'Modo de teste local'), 'registration exposed the local-test fallback');
        } elseif ($scenario === 'leader-policy') {
            require $siteRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'guild-mark.php';
            requireTest(function_exists('guild_mark_account_leads_guild'), 'guild leader authorization helper is missing');
            requireTest(!guild_mark_account_leads_guild([], 123), 'an account with no guild leadership was authorized');
            requireTest(!guild_mark_account_leads_guild([['id' => 124]], 123), 'an account was authorized for another guild');
            requireTest(guild_mark_account_leads_guild([['id' => 123]], 123), 'the recorded guild leader was denied');
        } else {
            failTest('unknown test scenario');
        }

        fwrite(STDOUT, "PASS {$scenario}\n");
        exit(0);
    }

    $scenarios = ['guildmark-login', 'guildmark-save', 'registration', 'leader-policy'];
    $failures = 0;
    foreach ($scenarios as $scenario) {
        $command = [PHP_BINARY, __FILE__, '--worker', $scenario];
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (!is_resource($process)) {
            failTest("could not start worker {$scenario}");
        }
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
        if ($exitCode !== 0) {
            fwrite(STDERR, "FAIL {$scenario}: " . trim((string)$stderr) . "\n");
            $failures++;
        } else {
            fwrite(STDOUT, trim((string)$stdout) . "\n");
        }
    }

    if ($failures > 0) {
        exit(1);
    }
    fwrite(STDOUT, "PASS all database-unavailable checks\n");
} finally {
    putenv('WYD_SITE_DB_SECRET');
    if (is_file($secretPath)) {
        unlink($secretPath);
    }
    if (is_dir($temporaryRoot)) {
        rmdir($temporaryRoot);
    }
}
