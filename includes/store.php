<?php

declare(strict_types=1);

function store_path(): string
{
    $dir = dirname(__DIR__) . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    return $dir . '/store.json';
}

function store_database(): ?PDO
{
    static $connection = null;
    static $initialized = false;

    $databaseUrl = getenv('DATABASE_URL') ?: getenv('POSTGRES_URL');
    if ($databaseUrl === false || $databaseUrl === '') {
        if (getenv('VERCEL')) {
            throw new RuntimeException('Set DATABASE_URL or POSTGRES_URL to a PostgreSQL database in Vercel project settings.');
        }
        return null;
    }

    if ($connection instanceof PDO) {
        return $connection;
    }

    $parts = parse_url($databaseUrl);
    if (!is_array($parts) || empty($parts['host']) || empty($parts['path'])) {
        throw new RuntimeException('The database URL must be a valid PostgreSQL connection URL.');
    }
    $query = [];
    parse_str((string) ($parts['query'] ?? ''), $query);
    $host = trim((string) $parts['host'], '[]');
    $port = (int) ($parts['port'] ?? 5432);
    $database = ltrim((string) $parts['path'], '/');
    $sslMode = preg_replace('/[^a-z]/', '', (string) ($query['sslmode'] ?? 'require')) ?: 'require';
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};sslmode={$sslMode}";

    $connection = new PDO(
        $dsn,
        rawurldecode((string) ($parts['user'] ?? '')),
        rawurldecode((string) ($parts['pass'] ?? '')),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if (!$initialized) {
        $connection->exec(
            'CREATE TABLE IF NOT EXISTS city_weather_visitor_store (' .
            'visitor_id VARCHAR(32) PRIMARY KEY, record JSONB NOT NULL)'
        );
        $initialized = true;
    }

    return $connection;
}

function visitor_id(): string
{
    if (!empty($_COOKIE['cwd_visitor']) && preg_match('/^[a-f0-9]{32}$/', (string) $_COOKIE['cwd_visitor'])) {
        return (string) $_COOKIE['cwd_visitor'];
    }
    $id = bin2hex(random_bytes(16));
    setcookie('cwd_visitor', $id, [
        'expires' => time() + 60 * 60 * 24 * 365,
        'path' => '/',
        'secure' => is_https_request(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_COOKIE['cwd_visitor'] = $id;
    return $id;
}

function store_defaults(): array
{
    return [
        'saved' => [],
        'history' => [],
        'last_city' => null,
    ];
}

function store_load(): array
{
    $id = visitor_id();
    $database = store_database();
    if ($database instanceof PDO) {
        $statement = $database->prepare('SELECT record::text FROM city_weather_visitor_store WHERE visitor_id = :visitor_id');
        $statement->execute(['visitor_id' => $id]);
        $decoded = json_decode((string) ($statement->fetchColumn() ?: ''), true);
        return array_merge(store_defaults(), is_array($decoded) ? $decoded : []);
    }

    $all = [];
    $path = store_path();
    if (is_file($path)) {
        $decoded = json_decode((string) file_get_contents($path), true);
        if (is_array($decoded)) {
            $all = $decoded;
        }
    }
    $record = $all[$id] ?? store_defaults();
    return array_merge(store_defaults(), is_array($record) ? $record : []);
}

function store_save(array $record): void
{
    $database = store_database();
    if ($database instanceof PDO) {
        $statement = $database->prepare(
            'INSERT INTO city_weather_visitor_store (visitor_id, record) VALUES (:visitor_id, CAST(:record AS JSONB)) ' .
            'ON CONFLICT (visitor_id) DO UPDATE SET record = EXCLUDED.record'
        );
        $statement->execute([
            'visitor_id' => visitor_id(),
            'record' => json_encode(array_merge(store_defaults(), $record), JSON_UNESCAPED_UNICODE),
        ]);
        return;
    }

    $path = store_path();
    $handle = fopen($path, 'c+');
    if ($handle === false) {
        throw new RuntimeException('Could not save locations on this server.');
    }
    flock($handle, LOCK_EX);
    $contents = stream_get_contents($handle);
    $all = json_decode((string) $contents, true);
    if (!is_array($all)) {
        $all = [];
    }
    $all[visitor_id()] = array_merge(store_defaults(), $record);
    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    flock($handle, LOCK_UN);
    fclose($handle);
}

function remember_place(array $place): void
{
    global $config;
    $store = store_load();
    $entry = [
        'name' => $place['name'],
        'country' => $place['country'] ?? '',
        'state' => $place['state'] ?? '',
        'lat' => $place['lat'],
        'lon' => $place['lon'],
        'viewed_at' => date('c'),
    ];
    $history = array_values(array_filter(
        $store['history'],
        static fn (array $item) => !same_place($item, $entry)
    ));
    array_unshift($history, $entry);
    $store['history'] = array_slice($history, 0, (int) ($config['history_limit'] ?? 12));
    $store['last_city'] = $entry;
    store_save($store);
}

function toggle_saved_place(array $place): bool
{
    $store = store_load();
    $exists = false;
    $saved = [];
    foreach ($store['saved'] as $item) {
        if (same_place($item, $place)) {
            $exists = true;
            continue;
        }
        $saved[] = $item;
    }
    if (!$exists) {
        array_unshift($saved, [
            'name' => $place['name'],
            'country' => $place['country'] ?? '',
            'state' => $place['state'] ?? '',
            'lat' => $place['lat'],
            'lon' => $place['lon'],
            'saved_at' => date('c'),
        ]);
    }
    $store['saved'] = $saved;
    store_save($store);
    return !$exists;
}
