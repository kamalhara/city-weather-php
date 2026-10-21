<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/request-weather.php';

$pageTitle = $bundle
    ? $bundle['place']['name'] . ' — City Weather'
    : 'City Weather Dashboard';

require __DIR__ . '/includes/header.php';

$current = $bundle['current'] ?? null;
$place = $bundle['place'] ?? null;
$useBrowserLocation = !isset($_GET['lat']) && !isset($_GET['city']);
?>

<section class="intro">
    <div>
        <p class="kicker">Today</p>
        <h1>Weather by city</h1>
        <p class="lede">Local conditions, a 12-hour timeline, and a seven-day outlook for cities around the world.</p>
    </div>
    <?php $action = 'index.php'; require __DIR__ . '/includes/search-form.php'; ?>
</section>

<?php if ($error): ?>
    <p class="notice" role="alert"><?= e($error) ?></p>
<?php endif; ?>

<?php if ($useBrowserLocation): ?>
    <p class="location-status hint" id="location-status" role="status" aria-live="polite">Finding your local forecast…</p>
<?php endif; ?>

<?php if (count($matches) > 1): ?>
    <p class="hint">Showing <?= e(place_line($matches[0])) ?>. Other matches:</p>
    <ul class="match-list">
        <?php foreach (array_slice($matches, 1) as $match): ?>
            <li><a href="index.php?<?= e(place_query($match)) ?>"><?= e(place_line($match)) ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($bundle && $current && $place): ?>
    <section class="now">
        <article class="now-card">
            <p class="now-place"><?= e(place_line($place)) ?></p>
            <p class="now-date"><?= e(format_day($current['dt'], $bundle['utc_offset'])) ?></p>
            <div class="now-temp">
                <?= weather_icon($current['icon'], 56) ?>
                <p>
                    <span class="temp"><?= e((string) round($current['temp'])) ?></span>
                    <span class="deg"><?= e(unit_symbol($units)) ?></span>
                </p>
            </div>
            <p class="now-desc"><?= e($current['description']) ?></p>
            <p class="now-feels">Feels like <?= e((string) round($current['feels_like'])) ?><?= e(unit_symbol($units)) ?></p>
            <p class="now-sun">Sunrise <?= e(format_clock($current['sunrise'], $bundle['utc_offset'])) ?> · Sunset <?= e(format_clock($current['sunset'], $bundle['utc_offset'])) ?></p>
            <form method="post" action="save.php" class="save-form">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="name" value="<?= e($place['name']) ?>">
                <input type="hidden" name="country" value="<?= e($place['country']) ?>">
                <input type="hidden" name="state" value="<?= e((string) ($place['state'] ?? '')) ?>">
                <input type="hidden" name="lat" value="<?= e((string) $place['lat']) ?>">
                <input type="hidden" name="lon" value="<?= e((string) $place['lon']) ?>">
                <input type="hidden" name="return" value="index.php?<?= e(place_query($place)) ?>">
                <button type="submit"><?= is_saved($store, $place) ? 'Remove from saved places' : 'Save this place' ?></button>
            </form>
        </article>

        <dl class="facts">
            <div>
                <dt>Humidity</dt>
                <dd><?= e((string) $current['humidity']) ?>%</dd>
            </div>
            <div>
                <dt>Wind</dt>
                <dd><?= e((string) round($current['wind_speed'])) ?> <?= e(wind_unit($units)) ?> <?= e(compass($current['wind_deg'])) ?></dd>
            </div>
            <div>
                <dt>Pressure</dt>
                <dd><?= e((string) $current['pressure']) ?> hPa</dd>
            </div>
            <div>
                <dt>Cloud cover</dt>
                <dd><?= e((string) $current['clouds']) ?>%</dd>
            </div>
            <div>
                <dt>UV index</dt>
                <dd><?= e((string) round($current['uv_index'], 1)) ?></dd>
            </div>
            <div>
                <dt>Wind gusts</dt>
                <dd><?= e((string) round($current['wind_gusts'])) ?> <?= e(wind_unit($units)) ?></dd>
            </div>
            <div>
                <dt>Rain now</dt>
                <dd><?= e((string) round($current['precipitation'], 1)) ?> <?= $units === 'imperial' ? 'in' : 'mm' ?></dd>
            </div>
            <div>
                <dt>Visibility</dt>
                <dd><?php
                    if ($current['visibility'] === null) {
                        echo '—';
                    } else {
                        echo e($units === 'imperial'
                            ? round($current['visibility'] / 1609.34, 1) . ' mi'
                            : round($current['visibility'] / 1000, 1) . ' km');
                    }
                ?></dd>
            </div>
            <div>
                <dt>Updated</dt>
                <dd><?= e(format_clock($current['dt'], $bundle['utc_offset'])) ?></dd>
            </div>
        </dl>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h2>Next 12 hours</h2>
            <a href="forecast.php?<?= e(place_query($place)) ?>">Seven-day forecast</a>
        </div>
        <ol class="hours">
            <?php foreach ($bundle['hourly'] as $hour): ?>
                <li>
                    <span><?= e($hour['label']) ?></span>
                    <?= weather_icon($hour['icon'], 28) ?>
                    <strong><?= e((string) round($hour['temp'])) ?>°</strong>
                    <em><?= e((string) $hour['pop']) ?>%</em>
                    <small><?= e((string) round($hour['wind_speed'])) ?> <?= e(wind_unit($units)) ?> wind</small>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>
<?php elseif (!$error): ?>
    <section class="empty">
        <p>Search for a city to see current conditions and a seven-day forecast from Open-Meteo.</p>
        <p class="hint">
            Try
            <a href="index.php?city=Chennai">Chennai</a>,
            <a href="index.php?city=London">London</a>,
            <a href="index.php?city=New York">New York</a>,
            or
            <a href="index.php?city=Tokyo">Tokyo</a>.
        </p>
    </section>
<?php endif; ?>

<?php if ($store['history'] !== []): ?>
    <aside class="recent">
        <h2>Recent lookups</h2>
        <ul>
            <?php foreach (array_slice($store['history'], 0, 6) as $item): ?>
                <li><a href="index.php?<?= e(place_query($item)) ?>"><?= e(place_line($item)) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </aside>
<?php endif; ?>

<?php if ($useBrowserLocation): ?>
<script>
    (() => {
        const status = document.getElementById('location-status');
        if (!navigator.geolocation) {
            status.textContent = 'Location is unavailable in this browser. Search for a city to get started.';
            return;
        }

        navigator.geolocation.getCurrentPosition(
            ({ coords }) => {
                const params = new URLSearchParams({
                    lat: String(coords.latitude),
                    lon: String(coords.longitude),
                    geo: '1',
                });
                window.location.replace(`index.php?${params.toString()}`);
            },
            () => {
                status.textContent = 'Could not access your location. Search for a city or open a saved place.';
            },
            { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 }
        );
    })();
</script>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
