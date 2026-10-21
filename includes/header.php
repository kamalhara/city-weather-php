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
                <a href="index.php"<?= $page === 'home' ? ' aria-current="page"' : '' ?>>Today</a>
                <a href="forecast.php"<?= $page === 'forecast' ? ' aria-current="page"' : '' ?>>Forecast</a>
                <a href="places.php"<?= $page === 'places' ? ' aria-current="page"' : '' ?>>Places</a>
                <a href="about.php"<?= $page === 'about' ? ' aria-current="page"' : '' ?>>About</a>
                <a class="github-link" href="https://github.com/kamalhara/city-weather-php" target="_blank" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 .9a11.1 11.1 0 0 0-3.51 21.63c.55.1.76-.24.76-.53v-2.07c-3.1.67-3.76-1.32-3.76-1.32-.5-1.29-1.24-1.63-1.24-1.63-1.01-.69.08-.68.08-.68 1.12.08 1.71 1.15 1.71 1.15 1 .1.54 2.02 3.55 1.02.1-.72.39-1.21.7-1.49-2.48-.28-5.09-1.24-5.09-5.52 0-1.22.44-2.22 1.15-3-.12-.28-.5-1.42.11-2.95 0 0 .94-.3 3.05 1.15a10.6 10.6 0 0 1 5.55 0c2.11-1.45 3.05-1.15 3.05-1.15.61 1.53.23 2.67.11 2.95.72.78 1.15 1.78 1.15 3 0 4.29-2.61 5.23-5.1 5.51.4.35.75 1.03.75 2.08V22c0 .29.2.63.77.52A11.1 11.1 0 0 0 12 .9Z"/></svg>
                    GitHub
                </a>
            </nav>
            <div class="unit-switch" role="group" aria-label="Temperature units">
                <a href="?units=metric<?= e($querySuffix) ?>"<?= $units === 'metric' ? ' aria-current="true"' : '' ?>>°C</a>
                <a href="?units=imperial<?= e($querySuffix) ?>"<?= $units === 'imperial' ? ' aria-current="true"' : '' ?>>°F</a>
            </div>
        </div>
    </header>
    <main id="content" class="page">
