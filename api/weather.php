<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require dirname(__DIR__) . '/includes/bootstrap.php';

function json_out(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    json_out(405, ['error' => 'Use a GET request.']);
}

try {
    $place = resolve_place_from_request($store);
    if ($place === null) {
        json_out(400, ['error' => 'Enter a city name.']);
    }
    $bundle = load_weather_bundle($place, $units, (int) $config['cache_ttl']);
    remember_place($bundle['place']);
    json_out(200, $bundle);
} catch (Throwable $error) {
    json_out(502, ['error' => $error->getMessage()]);
}
