<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Saved places and history — City Weather';
$searchValue = '';
require __DIR__ . '/includes/header.php';
?>

<section class="intro">
    <div>
        <p class="kicker">Your list</p>
        <h1>Saved places and history</h1>
        <p class="lede">Preferred cities are stored on the server for this browser. Search history is kept from recent lookups.</p>
    </div>
    <?php $action = 'index.php'; require __DIR__ . '/includes/search-form.php'; ?>
</section>

<section class="split">
    <article class="panel">
        <div class="panel-head">
            <h2>Preferred locations</h2>
            <p><?= count($store['saved']) === 1 ? '1 place' : count($store['saved']) . ' places' ?></p>
        </div>
        <?php if ($store['saved'] === []): ?>
            <p class="empty-copy">Nothing saved yet. Open a city and choose “Save this place”.</p>
        <?php else: ?>
            <ul class="place-list">
                <?php foreach ($store['saved'] as $place): ?>
                    <li>
                        <a href="index.php?<?= e(place_query($place)) ?>">
                            <strong><?= e($place['name']) ?></strong>
                            <span><?= e(place_line($place)) ?></span>
                        </a>
                        <form method="post" action="save.php">
                            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                            <input type="hidden" name="name" value="<?= e($place['name']) ?>">
                            <input type="hidden" name="country" value="<?= e($place['country'] ?? '') ?>">
                            <input type="hidden" name="state" value="<?= e($place['state'] ?? '') ?>">
                            <input type="hidden" name="lat" value="<?= e((string) $place['lat']) ?>">
                            <input type="hidden" name="lon" value="<?= e((string) $place['lon']) ?>">
                            <input type="hidden" name="return" value="places.php">
                            <button type="submit">Remove</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </article>

    <article class="panel">
        <div class="panel-head">
            <h2>Search history</h2>
            <p><?= count($store['history']) === 1 ? '1 city' : count($store['history']) . ' cities' ?></p>
        </div>
        <?php if ($store['history'] === []): ?>
            <p class="empty-copy">Looked-up cities will appear here.</p>
        <?php else: ?>
            <ul class="place-list">
                <?php foreach ($store['history'] as $place): ?>
                    <li>
                        <a href="index.php?<?= e(place_query($place)) ?>">
                            <strong><?= e($place['name']) ?></strong>
                            <span><?= e(place_line($place)) ?></span>
                        </a>
                        <?php if (!empty($place['viewed_at'])): ?>
                            <time datetime="<?= e($place['viewed_at']) ?>"><?= e(date('j M, g:i A', strtotime($place['viewed_at']))) ?></time>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </article>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
