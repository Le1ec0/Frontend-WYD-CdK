<?php
declare(strict_types=1);

function site_is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
        || ((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function site_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => site_is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function site_client_ip(): string
{
    return (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

function site_rate_limit(string $bucket, int $limit, int $windowSeconds): void
{
    $directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'wydcdk-site-rate';
    if (!is_dir($directory)) {
        @mkdir($directory, 0700, true);
    }

    $path = $directory . DIRECTORY_SEPARATOR . hash('sha256', $bucket . '|' . site_client_ip()) . '.json';
    $handle = @fopen($path, 'c+');
    if ($handle === false) {
        return;
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return;
        }

        $raw = stream_get_contents($handle);
        $state = is_string($raw) ? json_decode($raw, true) : null;
        $now = time();
        if (!is_array($state) || !isset($state['started'], $state['count']) || $now - (int)$state['started'] >= $windowSeconds) {
            $state = ['started' => $now, 'count' => 0];
        }

        $state['count'] = (int)$state['count'] + 1;
        if ($state['count'] > $limit) {
            $retryAfter = max(1, $windowSeconds - ($now - (int)$state['started']));
            header('Retry-After: ' . $retryAfter);
            http_response_code(429);
            echo 'Muitas requisicoes. Tente novamente em instantes.';
            exit;
        }

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($state, JSON_THROW_ON_ERROR));
        fflush($handle);
        flock($handle, LOCK_UN);
    } finally {
        fclose($handle);
    }
}

function site_csrf_token(string $key = 'site_csrf'): string
{
    site_start_session();
    if (empty($_SESSION[$key])) {
        $_SESSION[$key] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION[$key];
}

function site_validate_csrf(?string $value, string $key = 'site_csrf'): bool
{
    site_start_session();
    return is_string($value) && isset($_SESSION[$key]) && hash_equals((string)$_SESSION[$key], $value);
}

function site_bootstrap(string $bucket, int $limit = 120, int $windowSeconds = 60): void
{
    site_start_session();
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    if (site_is_https()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
    site_rate_limit($bucket, $limit, $windowSeconds);
}
