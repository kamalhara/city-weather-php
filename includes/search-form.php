<?php

declare(strict_types=1);

/** @var string $searchValue */
$searchValue = $searchValue ?? '';
$action = $action ?? current_page() . '.php';
?>
<form class="lookup" method="get" action="<?= e($action) ?>" role="search">
    <label for="city">City</label>
    <div class="lookup-row">
        <input id="city" name="city" type="search" value="<?= e($searchValue) ?>" placeholder="Chennai, London, Tokyo…" required autocomplete="off">
        <button type="submit">Look up</button>
    </div>
</form>
