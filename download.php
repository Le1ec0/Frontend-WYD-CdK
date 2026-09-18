<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'site-security.php';
site_bootstrap('download', 60, 60);
header('Location: guildmark.php', true, 302);
exit;
