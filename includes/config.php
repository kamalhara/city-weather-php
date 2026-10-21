<?php

declare(strict_types=1);

$defaults = [
    'cache_ttl' => 600,
    'history_limit' => 12,
];

$localFile = __DIR__ . '/config.local.php';
$local = is_file($localFile) ? require $localFile : [];

if (!is_array($local)) {
    $local = [];
}

return array_merge($defaults, $local);
