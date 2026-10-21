<?php

declare(strict_types=1);

$error = null;
$bundle = null;
$matches = [];
$searchValue = trim((string) ($_GET['city'] ?? ''));

try {
    if ($searchValue !== '' && empty($_GET['lat'])) {
        $matches = geocode_city($searchValue);
    }
    $place = $matches !== [] ? $matches[0] : resolve_place_from_request($store);
    if ($place !== null) {
        $bundle = load_weather_bundle($place, $units, (int) $config['cache_ttl']);
        remember_place($bundle['place']);
        $store = store_load();
        $searchValue = $bundle['place']['name'];
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}
