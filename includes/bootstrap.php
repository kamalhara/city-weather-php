<?php

declare(strict_types=1);

$config = require __DIR__ . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/store.php';
require_once __DIR__ . '/weather.php';
require_once __DIR__ . '/icons.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

if (!isset($_SESSION['units']) || !in_array($_SESSION['units'], ['metric', 'imperial'], true)) {
    $_SESSION['units'] = 'metric';
}

if (isset($_GET['units']) && in_array($_GET['units'], ['metric', 'imperial'], true)) {
    $_SESSION['units'] = $_GET['units'];
}

$store = store_load();
$units = $_SESSION['units'];
$csrf = $_SESSION['csrf'];
