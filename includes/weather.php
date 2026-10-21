<?php

declare(strict_types=1);

function geocode_city(string $query): array
{
    $results = fetch_json('https://geocoding-api.open-meteo.com/v1/search?' . http_build_query([
        'name' => $query,
        'count' => 5,
        'language' => 'en',
        'format' => 'json',
    ]));
    $places = [];
    foreach (($results['results'] ?? []) as $row) {
        if (!isset($row['latitude'], $row['longitude'], $row['name'])) {
            continue;
        }
        $places[] = [
            'name' => (string) $row['name'],
            'state' => (string) ($row['admin1'] ?? ''),
            'country' => (string) ($row['country'] ?? ''),
            'lat' => (float) $row['latitude'],
            'lon' => (float) $row['longitude'],
        ];
    }
    if ($places === []) {
        throw new RuntimeException('That city was not found. Check the spelling and try again.');
    }
    return $places;
}

function reverse_geocode_location(float $latitude, float $longitude): array
{
    // Cache a nearby place name for a month so app visits do not repeatedly
    // query the public Nominatim service for the same area.
    $cacheKey = 'place_' . md5(json_encode([round($latitude, 3), round($longitude, 3)]));
    $cached = cache_get($cacheKey, 30 * 24 * 60 * 60);
    if ($cached !== null) {
        return $cached;
    }

    // Nominatim's public endpoint allows at most one request per second per app.
    $lockPath = dirname(__DIR__) . '/cache/nominatim-rate-limit.lock';
    $lock = @fopen($lockPath, 'c+');
    if ($lock !== false) {
        flock($lock, LOCK_EX);
        rewind($lock);
        $lastRequest = (float) stream_get_contents($lock);
        $wait = 1.1 - (microtime(true) - $lastRequest);
        if ($wait > 0) {
            usleep((int) ($wait * 1_000_000));
        }
        ftruncate($lock, 0);
        rewind($lock);
        fwrite($lock, (string) microtime(true));
        fflush($lock);
        flock($lock, LOCK_UN);
        fclose($lock);
    }

    try {
        $result = fetch_json('https://nominatim.openstreetmap.org/reverse?' . http_build_query([
            'lat' => $latitude,
            'lon' => $longitude,
            'format' => 'jsonv2',
            'zoom' => 10,
            'addressdetails' => 1,
            'accept-language' => 'en',
        ]));
        $address = $result['address'] ?? [];
        $name = (string) ($address['city'] ?? $address['town'] ?? $address['village']
            ?? $address['municipality'] ?? $address['county'] ?? $address['state'] ?? 'Your location');
        $place = [
            'name' => $name,
            'state' => (string) ($address['state'] ?? ''),
            'country' => (string) ($address['country'] ?? ''),
        ];
        cache_put($cacheKey, $place);
        return $place;
    } catch (Throwable) {
        return ['name' => 'Your location', 'state' => '', 'country' => ''];
    }
}

function load_weather_bundle(array $place, string $units, int $ttl): array
{
    $cacheKey = 'openmeteo_' . md5(json_encode([$place['lat'], $place['lon'], $units]));
    $cached = cache_get($cacheKey, $ttl);
    if ($cached !== null) {
        return $cached;
    }

    $query = [
        'latitude' => $place['lat'],
        'longitude' => $place['lon'],
        'current' => implode(',', [
            'temperature_2m', 'relative_humidity_2m', 'apparent_temperature', 'is_day',
            'precipitation', 'weather_code', 'pressure_msl', 'surface_pressure',
            'cloud_cover', 'visibility', 'wind_speed_10m', 'wind_direction_10m', 'wind_gusts_10m',
        ]),
        'hourly' => implode(',', [
            'temperature_2m', 'apparent_temperature', 'precipitation_probability', 'weather_code',
            'relative_humidity_2m', 'wind_speed_10m', 'wind_direction_10m', 'uv_index', 'visibility',
        ]),
        'daily' => implode(',', [
            'weather_code', 'temperature_2m_max', 'temperature_2m_min', 'sunrise', 'sunset',
            'precipitation_probability_max', 'precipitation_sum', 'uv_index_max',
            'wind_speed_10m_max', 'wind_direction_10m_dominant',
        ]),
        'temperature_unit' => $units === 'imperial' ? 'fahrenheit' : 'celsius',
        'wind_speed_unit' => $units === 'imperial' ? 'mph' : 'ms',
        'precipitation_unit' => $units === 'imperial' ? 'inch' : 'mm',
        'timezone' => 'auto',
        'forecast_days' => 7,
    ];
    $data = fetch_json('https://api.open-meteo.com/v1/forecast?' . http_build_query($query));
    if (empty($data['current']) || empty($data['hourly']) || empty($data['daily'])) {
        throw new RuntimeException('Open-Meteo returned incomplete weather data. Please try again.');
    }

    $offset = (int) ($data['utc_offset_seconds'] ?? 0);
    $now = $data['current'];
    $code = (int) ($now['weather_code'] ?? 0);
    $currentTimestamp = local_weather_timestamp((string) ($now['time'] ?? ''), $offset);
    $currentHourlyIndex = 0;
    foreach (($data['hourly']['time'] ?? []) as $index => $hourTime) {
        if (local_weather_timestamp((string) $hourTime, $offset) >= $currentTimestamp - 3600) {
            $currentHourlyIndex = $index;
            break;
        }
    }
    $current = [
        'dt' => $currentTimestamp,
        'temp' => (float) ($now['temperature_2m'] ?? 0),
        'feels_like' => (float) ($now['apparent_temperature'] ?? 0),
        'humidity' => (int) ($now['relative_humidity_2m'] ?? 0),
        'pressure' => (int) round((float) ($now['surface_pressure'] ?? $now['pressure_msl'] ?? 0)),
        'visibility' => isset($now['visibility']) ? (int) $now['visibility'] : null,
        'clouds' => (int) ($now['cloud_cover'] ?? 0),
        'wind_speed' => (float) ($now['wind_speed_10m'] ?? 0),
        'wind_deg' => isset($now['wind_direction_10m']) ? (int) $now['wind_direction_10m'] : null,
        'wind_gusts' => (float) ($now['wind_gusts_10m'] ?? 0),
        'precipitation' => (float) ($now['precipitation'] ?? 0),
        'sunrise' => local_weather_timestamp((string) ($data['daily']['sunrise'][0] ?? ''), $offset),
        'sunset' => local_weather_timestamp((string) ($data['daily']['sunset'][0] ?? ''), $offset),
        'description' => weather_description($code),
        'main' => weather_description($code),
        'icon' => weather_icon_code($code, (int) ($now['is_day'] ?? 1) === 1),
        'id' => $code,
        'uv_index' => (float) ($data['hourly']['uv_index'][$currentHourlyIndex] ?? 0),
    ];
    $bundle = [
        'place' => [
            'name' => $place['name'] ?? 'Selected location',
            'state' => $place['state'] ?? '',
            'country' => $place['country'] ?? '',
            'lat' => (float) $place['lat'],
            'lon' => (float) $place['lon'],
        ],
        'utc_offset' => $offset,
        'timezone' => (string) ($data['timezone'] ?? ''),
        'units' => $units,
        'current' => $current,
        'hourly' => build_hourly($data['hourly'], $offset, $current['dt']),
        'daily' => build_daily($data['daily'], $offset),
    ];
    cache_put($cacheKey, $bundle);
    return $bundle;
}

function build_hourly(array $hourly, int $offset, int $now): array
{
    $hours = [];
    $times = $hourly['time'] ?? [];
    $start = 0;
    foreach ($times as $index => $time) {
        if (local_weather_timestamp((string) $time, $offset) >= $now - 3600) {
            $start = $index;
            break;
        }
    }
    for ($index = $start; $index < min(count($times), $start + 12); $index++) {
        $timestamp = local_weather_timestamp((string) $times[$index], $offset);
        $code = (int) ($hourly['weather_code'][$index] ?? 0);
        $hours[] = [
            'dt' => $timestamp,
            'label' => $index === $start ? 'Now' : format_clock($timestamp, $offset, 'g A'),
            'temp' => (float) ($hourly['temperature_2m'][$index] ?? 0),
            'pop' => (int) ($hourly['precipitation_probability'][$index] ?? 0),
            'description' => weather_description($code),
            'icon' => weather_icon_code($code, (int) gmdate('G', $timestamp + $offset) >= 6 && (int) gmdate('G', $timestamp + $offset) < 18),
            'humidity' => (int) ($hourly['relative_humidity_2m'][$index] ?? 0),
            'wind_speed' => (float) ($hourly['wind_speed_10m'][$index] ?? 0),
            'uv_index' => (float) ($hourly['uv_index'][$index] ?? 0),
        ];
    }
    return $hours;
}

function build_daily(array $daily, int $offset): array
{
    $days = [];
    foreach (($daily['time'] ?? []) as $index => $date) {
        $code = (int) ($daily['weather_code'][$index] ?? 0);
        $days[] = [
            'date' => (string) $date,
            'label' => weekday_label((string) $date, $offset),
            'pretty' => gmdate('j M', strtotime((string) $date . ' UTC')),
            'min' => (float) ($daily['temperature_2m_min'][$index] ?? 0),
            'max' => (float) ($daily['temperature_2m_max'][$index] ?? 0),
            'pop' => (int) ($daily['precipitation_probability_max'][$index] ?? 0),
            'precipitation' => (float) ($daily['precipitation_sum'][$index] ?? 0),
            'humidity' => 0,
            'wind_speed' => (float) ($daily['wind_speed_10m_max'][$index] ?? 0),
            'wind_deg' => isset($daily['wind_direction_10m_dominant'][$index]) ? (int) $daily['wind_direction_10m_dominant'][$index] : null,
            'uv_index' => (float) ($daily['uv_index_max'][$index] ?? 0),
            'sunrise' => local_weather_timestamp((string) ($daily['sunrise'][$index] ?? ''), $offset),
            'sunset' => local_weather_timestamp((string) ($daily['sunset'][$index] ?? ''), $offset),
            'icon' => weather_icon_code($code, true),
            'description' => weather_description($code),
        ];
    }
    return $days;
}

function local_weather_timestamp(string $time, int $offset): int
{
    $timestamp = strtotime($time . ' UTC');
    return $timestamp === false ? 0 : $timestamp - $offset;
}

function weather_description(int $code): string
{
    return match ($code) {
        0 => 'Clear sky',
        1 => 'Mainly clear',
        2 => 'Partly cloudy',
        3 => 'Overcast',
        45, 48 => 'Fog',
        51, 53, 55 => 'Drizzle',
        56, 57 => 'Freezing drizzle',
        61 => 'Slight rain',
        63 => 'Moderate rain',
        65 => 'Heavy rain',
        66, 67 => 'Freezing rain',
        71 => 'Slight snow',
        73 => 'Moderate snow',
        75, 77 => 'Heavy snow',
        80 => 'Rain showers',
        81 => 'Moderate rain showers',
        82 => 'Violent rain showers',
        85, 86 => 'Snow showers',
        95 => 'Thunderstorm',
        96, 99 => 'Thunderstorm with hail',
        default => 'Conditions unavailable',
    };
}

function weather_icon_code(int $code, bool $day): string
{
    $suffix = $day ? 'd' : 'n';
    $prefix = match (true) {
        $code === 0 => '01',
        $code === 1 || $code === 2 => '02',
        $code === 3 => '04',
        in_array($code, [45, 48], true) => '50',
        $code >= 51 && $code <= 67, $code >= 80 && $code <= 82 => '10',
        $code >= 71 && $code <= 77, $code === 85 || $code === 86 => '13',
        $code >= 95 => '11',
        default => '03',
    };
    return $prefix . $suffix;
}

function resolve_place_from_request(array $store): ?array
{
    $lat = trim((string) ($_GET['lat'] ?? ''));
    $lon = trim((string) ($_GET['lon'] ?? ''));
    $city = trim((string) ($_GET['city'] ?? ''));

    if ($lat !== '' && $lon !== '' && is_numeric($lat) && is_numeric($lon)
        && abs((float) $lat) <= 90 && abs((float) $lon) <= 180) {
        $location = ($_GET['geo'] ?? '') === '1'
            ? reverse_geocode_location((float) $lat, (float) $lon)
            : [];
        return [
            'name' => $city !== '' ? $city : ($location['name'] ?? 'Selected location'),
            'state' => trim((string) ($_GET['state'] ?? $location['state'] ?? '')),
            'country' => trim((string) ($_GET['country'] ?? $location['country'] ?? '')),
            'lat' => (float) $lat,
            'lon' => (float) $lon,
        ];
    }
    if ($city !== '') {
        return geocode_city($city)[0];
    }
    return !empty($store['last_city']) ? $store['last_city'] : null;
}
