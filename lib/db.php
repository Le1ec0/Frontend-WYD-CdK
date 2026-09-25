<?php
declare(strict_types=1);

/**
 * WYD CDK - Central Database Connection Helper
 */
function getSiteDbSecretPath(): ?string
{
    $env = getenv('WYD_SITE_DB_SECRET');
    if ($env !== false && trim($env) !== '' && is_file($env)) {
        return $env;
    }

    $projectRoot = dirname(__DIR__, 2);
    $candidates = [
        // Runtime/VPS configuration is preferred over any workstation fallback.
        $projectRoot . DIRECTORY_SEPARATOR . 'Server' . DIRECTORY_SEPARATOR . 'secrets' . DIRECTORY_SEPARATOR . 'site-db.json',
        // Local-only fallback; Backup is never part of a release or Git checkout.
        $projectRoot . DIRECTORY_SEPARATOR . 'Backup' . DIRECTORY_SEPARATOR . 'secrets' . DIRECTORY_SEPARATOR . 'site-db.json',
        __DIR__ . DIRECTORY_SEPARATOR . 'site-db.json'
    ];

    foreach ($candidates as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    return null;
}

function OpenAccountDatabase(): ?PDO
{
    $secretPath = getSiteDbSecretPath();
    if ($secretPath === null || !is_file($secretPath)) {
        return null;
    }

    if (!extension_loaded('pdo_mysql')) {
        return null;
    }

    $config = json_decode((string)file_get_contents($secretPath), true);
    if (!is_array($config) || empty($config['host']) || empty($config['database']) || empty($config['username'])) {
        return null;
    }

    $dsn = 'mysql:host=' . $config['host'] . ';port=' . (int)($config['port'] ?? 3306) .
        ';dbname=' . $config['database'] . ';charset=utf8mb4';
        
    return new PDO($dsn, $config['username'], (string)($config['password'] ?? ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function OpenGuildDatabase(): ?PDO
{
    return OpenAccountDatabase();
}
