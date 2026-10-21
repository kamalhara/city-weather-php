<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path, true, 303);
    exit;
}

function is_https_request(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

function current_page(): string
{
    $script = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    return pathinfo($script, PATHINFO_FILENAME);
}

function unit_symbol(string $units): string
{
    return $units === 'imperial' ? '°F' : '°C';
}

function wind_unit(string $units): string
{
    return $units === 'imperial' ? 'mph' : 'm/s';
}

function compass(int|float|null $degrees): string
{
    if ($degrees === null) {
        return '—';
    }
    $points = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
    $index = (int) round(((float) $degrees % 360) / 45) % 8;
    return $points[$index];
}

function format_clock(int $timestamp, int $offset, string $pattern = 'g:i A'): string
{
    return gmdate($pattern, $timestamp + $offset);
}

function format_day(int $timestamp, int $offset, string $pattern = 'l, j F'): string
{
    return format_clock($timestamp, $offset, $pattern);
}

function weekday_label(string $date, int $offset): string
{
    $today = gmdate('Y-m-d', time() + $offset);
    if ($date === $today) {
        return 'Today';
    }
    $parts = explode('-', $date);
    if (count($parts) !== 3) {
        return $date;
    }
    return gmdate('D', gmmktime(12, 0, 0, (int) $parts[1], (int) $parts[2], (int) $parts[0]));
}

function place_query(array $place): string
{
    return http_build_query([
        'city' => $place['name'] ?? '',
        'lat' => $place['lat'] ?? '',
        'lon' => $place['lon'] ?? '',
        'country' => $place['country'] ?? '',
        'state' => $place['state'] ?? '',
    ]);
}

function place_line(array $place): string
{
    $parts = array_filter([
        $place['name'] ?? '',
        $place['state'] ?? '',
        $place['country'] ?? '',
    ], static fn ($part) => $part !== '');
    $unique = [];
    foreach ($parts as $part) {
        if (!in_array($part, $unique, true)) {
            $unique[] = $part;
        }
    }
    return implode(', ', $unique);
}

function is_saved(array $store, array $place): bool
{
    foreach ($store['saved'] as $saved) {
        if (same_place($saved, $place)) {
            return true;
        }
    }
    return false;
}

function same_place(array $a, array $b): bool
{
    if (isset($a['lat'], $a['lon'], $b['lat'], $b['lon']) && $a['lat'] !== '' && $b['lat'] !== '') {
        return abs((float) $a['lat'] - (float) $b['lat']) < 0.03
            && abs((float) $a['lon'] - (float) $b['lon']) < 0.03;
    }
    return strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')) === 0
        && strcasecmp((string) ($a['country'] ?? ''), (string) ($b['country'] ?? '')) === 0;
}

function fetch_json(string $url): array
{
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_TIMEOUT => 14,
            CURLOPT_USERAGENT => 'CityWeatherDashboard/1.0',
        ]);
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($body === false) {
            throw new RuntimeException('Could not reach Open-Meteo. Check your internet connection.');
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'timeout' => 14,
                'ignore_errors' => true,
                'header' => "User-Agent: CityWeatherDashboard/1.0\r\n",
            ],
        ]);
        $body = @file_get_contents($url, false, $context);
        $status = 0;
        if ($body === false) {
            throw new RuntimeException('Could not reach Open-Meteo. Check your internet connection.');
        }
    }

    $data = json_decode((string) $body, true);
    if (!is_array($data)) {
        throw new RuntimeException('Open-Meteo returned an unreadable response.');
    }
    if ($status >= 400 || isset($data['error'])) {
        $message = (string) ($data['reason'] ?? $data['error'] ?? 'Open-Meteo is unavailable right now.');
        throw new RuntimeException('Open-Meteo: ' . $message);
    }
    return $data;
}

function cache_get(string $key, int $ttl): ?array
{
    $file = cache_directory() . '/' . $key . '.json';
    if (!is_file($file)) {
        return null;
    }
    if (filemtime($file) + $ttl < time()) {
        return null;
    }
    $decoded = json_decode((string) file_get_contents($file), true);
    return is_array($decoded) ? $decoded : null;
}

function cache_put(string $key, array $value): void
{
    $dir = cache_directory();
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    @file_put_contents($dir . '/' . $key . '.json', json_encode($value));
}

function cache_directory(): string
{
    if (getenv('VERCEL')) {
        $directory = rtrim(sys_get_temp_dir(), '/') . '/city-weather-cache';
    } else {
        $directory = dirname(__DIR__) . '/cache';
    }

    if (!is_dir($directory)) {
        @mkdir($directory, 0775, true);
    }
    return $directory;
}

function uv_badge(float $uv): array
{
    if ($uv < 3) {
        return ['level' => 'Low', 'class' => 'uv-low'];
    }
    if ($uv < 6) {
        return ['level' => 'Moderate', 'class' => 'uv-mod'];
    }
    if ($uv < 8) {
        return ['level' => 'High', 'class' => 'uv-high'];
    }
    if ($uv < 11) {
        return ['level' => 'Very High', 'class' => 'uv-vhigh'];
    }
    return ['level' => 'Extreme', 'class' => 'uv-extreme'];
}

