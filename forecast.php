<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/request-weather.php';

$pageTitle = $bundle
    ? 'Forecast for ' . $bundle['place']['name']
    : 'Seven-day forecast — City Weather';

require __DIR__ . '/includes/header.php';
?>

<section class="intro">
    <div>
        <p class="kicker">Outlook</p>
        <h1>Seven-day forecast</h1>
        <p class="lede">A full week of temperatures, rain chances, wind, and UV outlook.</p>
    </div>
    <?php $action = 'forecast.php'; require __DIR__ . '/includes/search-form.php'; ?>
</section>

<?php if ($error): ?>
    <p class="notice" role="alert"><?= e($error) ?></p>
<?php endif; ?>

<?php if ($bundle): ?>
    <section class="panel">
        <div class="panel-head">
            <h2><?= e(place_line($bundle['place'])) ?></h2>
            <p><?= e($bundle['current']['description']) ?>, <?= e((string) round($bundle['current']['temp'])) ?><?= e(unit_symbol($units)) ?></p>
        </div>
        <table class="days">
            <thead>
                <tr>
                    <th class="col-day">Day</th>
                    <th class="col-cond">Conditions</th>
                    <th class="col-num col-high">High</th>
                    <th class="col-num col-low">Low</th>
                    <th class="col-num col-rain">Rain %</th>
                    <th class="col-num col-rain-total">Rain total</th>
                    <th class="col-num col-wind">Wind</th>
                    <th class="col-num col-uv">UV</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bundle['daily'] as $day): ?>
                    <?php $uv = uv_badge((float) $day['uv_index']); ?>
                    <tr>
                        <th class="col-day">
                            <span><?= e($day['label']) ?></span>
                            <small><?= e($day['pretty']) ?></small>
                        </th>
                        <td class="col-cond">
                            <div class="cond">
                                <?= weather_icon($day['icon'], 26) ?>
                                <span><?= e($day['description']) ?></span>
                            </div>
                        </td>
                        <td class="col-num col-high">
                            <span class="temp-high"><?= e((string) round($day['max'])) ?><?= e(unit_symbol($units)) ?></span>
                        </td>
                        <td class="col-num col-low">
                            <span class="temp-low"><?= e((string) round($day['min'])) ?><?= e(unit_symbol($units)) ?></span>
                        </td>
                        <td class="col-num col-rain">
                            <span class="val-rain <?= $day['pop'] > 0 ? 'is-wet' : '' ?>"><?= e((string) $day['pop']) ?>%</span>
                        </td>
                        <td class="col-num col-rain-total">
                            <span class="val-total <?= $day['precipitation'] > 0 ? 'is-wet' : '' ?>">
                                <?= e((string) round($day['precipitation'], 1)) ?> <small><?= $units === 'imperial' ? 'in' : 'mm' ?></small>
                            </span>
                        </td>
                        <td class="col-num col-wind">
                            <span class="val-wind">
                                <?= e((string) round($day['wind_speed'])) ?> <small><?= e(wind_unit($units)) ?></small>
                                <span class="wind-dir"><?= e(compass($day['wind_deg'])) ?></span>
                            </span>
                        </td>
                        <td class="col-num col-uv">
                            <span class="uv-pill <?= e($uv['class']) ?>" title="UV index <?= e((string) round($day['uv_index'], 1)) ?> (<?= e($uv['level']) ?>)">
                                <?= e((string) round($day['uv_index'], 1)) ?>
                                <small><?= e($uv['level']) ?></small>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php elseif (!$error): ?>
    <section class="empty">
        <p>Look up a city to see the seven-day outlook.</p>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
