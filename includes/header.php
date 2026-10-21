<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $units */
$page = current_page();
$querySuffix = isset($_GET['city']) || isset($_GET['lat'])
    ? '&' . http_build_query(array_filter([
        'city' => $_GET['city'] ?? null,
        'lat' => $_GET['lat'] ?? null,
        'lon' => $_GET['lon'] ?? null,
        'country' => $_GET['country'] ?? null,
        'state' => $_GET['state'] ?? null,
        'geo' => $_GET['geo'] ?? null,
    ], static fn ($value) => $value !== null && $value !== ''))
    : '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f3eee4">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,500;8..60,600;8..60,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <a class="skip" href="#content">Skip to content</a>
    <header class="masthead">
        <div class="masthead-inner">
            <a class="brand" href="index.php">
                <span class="brand-kicker">College project</span>
                <span class="brand-name">City Weather</span>
            </a>
            <nav aria-label="Main">
                <a href="index.php"<?= $page === 'index' ? ' aria-current="page"' : '' ?>>Today</a>
                <a href="forecast.php"<?= $page === 'forecast' ? ' aria-current="page"' : '' ?>>Forecast</a>
                <a href="places.php"<?= $page === 'places' ? ' aria-current="page"' : '' ?>>Places</a>
                <a href="about.php"<?= $page === 'about' ? ' aria-current="page"' : '' ?>>About</a>
            </nav>
            <div class="unit-switch" role="group" aria-label="Temperature units">
                <a href="?units=metric<?= e($querySuffix) ?>"<?= $units === 'metric' ? ' aria-current="true"' : '' ?>>°C</a>
                <a href="?units=imperial<?= e($querySuffix) ?>"<?= $units === 'imperial' ? ' aria-current="true"' : '' ?>>°F</a>
            </div>
        </div>
    </header>
    <main id="content" class="page">
