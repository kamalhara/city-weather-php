<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'About — City Weather';
require __DIR__ . '/includes/header.php';
?>

<section class="intro">
    <div>
        <p class="kicker">The assignment</p>
        <h1>About this dashboard</h1>
        <p class="lede">A PHP weather desk for looking up a city, reading current conditions, and keeping a short list of places you care about.</p>
    </div>
</section>

<section class="about-grid">
    <article class="panel">
        <h2>Open-Meteo</h2>
        <p>PHP uses Open-Meteo for forecasts and city search. When browser location is available, OpenStreetMap Nominatim resolves the nearby city. No API key is required.</p>
    </article>
    <article class="panel">
        <h2>Search</h2>
        <p>Type a city name. The server finds coordinates, then loads live temperature, wind, humidity, and the hours ahead.</p>
    </article>
    <article class="panel">
        <h2>History and places</h2>
        <p>Each browser gets a visitor cookie. Recent lookups and saved cities are written to a JSON file on the server, not only to localStorage.</p>
    </article>
</section>

<section class="panel team-panel" aria-labelledby="team-heading">
    <div class="panel-head">
        <h2 id="team-heading">Created by</h2>
        <p>Project team</p>
    </div>
    <ol class="team-list">
        <li><span>Kamalveer Singh</span><strong>Roll No. 2433538</strong></li>
        <li><span>Kamaldeep Kaur</span><strong>Roll No. 2433535</strong></li>
        <li><span>Khushpreet Kaur</span><strong>Roll No. 2433545</strong></li>
        <li><span>Kiranjot Kaur</span><strong>Roll No. 2433552</strong></li>
        <li><span>Kundan Kumar Jha</span><strong>Roll No. 2433557</strong></li>
    </ol>
</section>

<ol class="setup">
    <li>Install PHP 8.1 or newer.</li>
    <li>From this folder run <code>php -S localhost:8000</code>.</li>
    <li>Open <a href="index.php">Today</a> and search for a city. Weather data is provided by <a href="https://open-meteo.com/">Open-Meteo</a>.</li>
</ol>

<?php require __DIR__ . '/includes/footer.php'; ?>
