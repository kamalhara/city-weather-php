<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('places.php');
}

$token = (string) ($_POST['csrf'] ?? '');
if (!hash_equals($csrf, $token)) {
    redirect('places.php');
}

$place = [
    'name' => trim((string) ($_POST['name'] ?? '')),
    'country' => trim((string) ($_POST['country'] ?? '')),
    'state' => trim((string) ($_POST['state'] ?? '')),
    'lat' => (float) ($_POST['lat'] ?? 0),
    'lon' => (float) ($_POST['lon'] ?? 0),
];

if ($place['name'] === '' || ($place['lat'] === 0.0 && $place['lon'] === 0.0)) {
    redirect('places.php');
}

toggle_saved_place($place);
$next = trim((string) ($_POST['return'] ?? 'places.php'));
if (!preg_match('/^(index|forecast|places)\.php(\?.*)?$/', $next)) {
    $next = 'places.php';
}

redirect($next);
