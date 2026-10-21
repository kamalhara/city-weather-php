<?php

declare(strict_types=1);

function weather_icon(string $code, int $size = 36): string
{
    $stroke = 1.7;
    $common = 'fill="none" stroke="currentColor" stroke-width="' . $stroke . '" stroke-linecap="square" stroke-linejoin="miter"';
    $body = match (substr($code, 0, 2)) {
        '01' => '<circle cx="12" cy="12" r="4" ' . $common . '/><path d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M5.6 18.4L7 17M17 7l1.4-1.4" ' . $common . '/>',
        '02' => '<circle cx="9" cy="10" r="3" ' . $common . '/><path d="M7 17h9a3.5 3.5 0 0 0 .2-7 5 5 0 0 0-9.6 1.4" ' . $common . '/>',
        '03', '04' => '<path d="M6.5 17h10a3.5 3.5 0 0 0 .3-7A5.2 5.2 0 0 0 7 11.4 3.2 3.2 0 0 0 6.5 17z" ' . $common . '/>',
        '09' => '<path d="M6.5 14h10a3.5 3.5 0 0 0 .3-7A5.2 5.2 0 0 0 7 8.4 3.2 3.2 0 0 0 6.5 14z" ' . $common . '/><path d="M8 17v3M12 17v3M16 17v3" ' . $common . '/>',
        '10' => '<path d="M7 14h9.5a3.2 3.2 0 0 0 .2-6.4 5 5 0 0 0-9.5 1.5A3 3 0 0 0 7 14z" ' . $common . '/><path d="M8.5 17l-1 3M12 17l-1 3M15.5 17l-1 3" ' . $common . '/>',
        '11' => '<path d="M7 13h9.5a3.2 3.2 0 0 0 .2-6.4 5 5 0 0 0-9.5 1.5A3 3 0 0 0 7 13z" ' . $common . '/><path d="M11 14l-2 4h3l-2 4" ' . $common . '/>',
        '13' => '<path d="M12 5v14M6 8l12 8M6 16l12-8" ' . $common . '/>',
        '50' => '<path d="M4 8h16M5 12h14M6 16h12" ' . $common . '/>',
        default => '<circle cx="12" cy="12" r="4" ' . $common . '/>',
    };

    return '<svg class="wx-icon" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" aria-hidden="true">' . $body . '</svg>';
}
