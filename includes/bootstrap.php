<?php

declare(strict_types=1);

$config = require __DIR__ . '/config.php';

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/store.php';
require_once __DIR__ . '/weather.php';
require_once __DIR__ . '/icons.php';

$secureCookies = is_https_request();
$csrf = $_COOKIE['cwd_csrf'] ?? '';
if (!preg_match('/^[a-f0-9]{32}$/', (string) $csrf)) {
    $csrf = bin2hex(random_bytes(16));
    setcookie('cwd_csrf', $csrf, [
        'expires' => time() + 60 * 60 * 24,
        'path' => '/',
        'secure' => $secureCookies,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_COOKIE['cwd_csrf'] = $csrf;
}

$units = $_COOKIE['cwd_units'] ?? 'metric';
if (!in_array($units, ['metric', 'imperial'], true)) {
    $units = 'metric';
}

if (isset($_GET['units']) && in_array($_GET['units'], ['metric', 'imperial'], true)) {
    $units = $_GET['units'];
    setcookie('cwd_units', $units, [
        'expires' => time() + 60 * 60 * 24 * 365,
        'path' => '/',
        'secure' => $secureCookies,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_COOKIE['cwd_units'] = $units;
}

$store = store_load();
