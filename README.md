# City Weather Dashboard

A PHP weather dashboard that searches worldwide locations, detects the browser's location and names its nearby city, shows current conditions and the next 12 hours, and provides a seven-day outlook. It uses [Open-Meteo](https://open-meteo.com/) for weather data and city search, plus OpenStreetMap Nominatim for reverse geocoding; no API key is needed

## Features

- Search cities and postal codes
- Current temperature, feels-like temperature, wind, humidity, pressure, visibility, UV, and sunrise/sunset
- Twelve-hour timeline and seven-day forecast
- Recent searches and saved places for each browser
- Metric and imperial units
- Short-lived server-side weather response cache

## Run it

Requires PHP 8.1 or newer. From this folder, start the built-in server:

```sh
php -S localhost:8000
```

Then open http://localhost:8000. The server needs internet access to reach the Open-Meteo APIs.

## Deploy to Vercel

This project uses Vercel's community PHP runtime (`vercel-php`) with a small function wrapper for each page. Vercel runs PHP as serverless functions rather than as a persistent PHP server.

1. Create a PostgreSQL database with a Vercel Marketplace integration or another PostgreSQL provider.
2. Import this GitHub repository into Vercel. Use the **Other** framework preset and leave the build command empty; Vercel serves the CSS directly from `assets/`.
3. In the Vercel project settings, add `DATABASE_URL` with the provider's PostgreSQL connection URL. `POSTGRES_URL` is also accepted. The app creates its `city_weather_visitor_store` table automatically on first request.
4. Deploy the project. The PHP runtime and page rewrites are configured in `vercel.json`.

Saved places and recent searches use PostgreSQL on Vercel. Weather responses and reverse-geocoding results use `/tmp` as an optional cache, so cache misses after a function restart are expected. Local development continues to use `data/store.json` and the repository's `cache/` directory when no database URL is set.

If you deploy without setting `DATABASE_URL` or `POSTGRES_URL`, requests that load saved-place data will return an error. Vercel functions have a read-only application filesystem, so the local JSON store cannot be used there.

## Deploy to Render

The `Dockerfile` runs Apache with PHP 8.4, the cURL and PostgreSQL PDO extensions, and listens on Render's expected port `10000`. It serves the PHP pages and the frontend assets from the same web service.

1. Push the repository to GitHub.
2. In Render, choose **New → Web Service**, connect the repository, and select **Docker** as the runtime.
3. Add `DATABASE_URL` with a PostgreSQL connection URL (or use `POSTGRES_URL`). This keeps saved places and recent searches outside the service's local filesystem.
4. Create the service. Render builds from the Dockerfile and publishes an `onrender.com` URL.

Without `DATABASE_URL` or `POSTGRES_URL`, the app falls back to local JSON storage. Render's filesystem is ephemeral, so saved places and recent searches can disappear after a restart or spin-down. Free web services also spin down after 15 minutes without traffic and can take about a minute to start again. Render's free PostgreSQL databases expire after 30 days, so use a database plan/provider that fits your needs for longer-term data.

## Deploy to InfinityFree

InfinityFree's free hosting runs standard PHP and can use this project's local JSON storage without a database. The project requires PHP 8.1 or newer.

1. Create a free hosting account and domain/subdomain in the InfinityFree client area.
2. Use the account's FTP details with an FTP client, or its online file manager, and upload the website files into `htdocs`. Put `index.php` directly in `htdocs`, not inside a nested project folder.
3. Upload the `api/`, `assets/`, `cache/`, `data/`, and `includes/` folders, including the hidden `.htaccess` files in `cache/` and `data/`.
4. Open the account's domain. The app creates `data/store.json` as visitors save places and browse cities.

The `data/.htaccess` file prevents visitors from downloading saved-place records, and `cache/.htaccess` protects cached files. If uploading with FTP, enable showing hidden files so the `.htaccess` files are included. InfinityFree allows outbound HTTP/HTTPS requests for PHP API clients, which the weather lookups need.

## Project files

```text
index.php                 Current conditions and 12-hour timeline
forecast.php              Seven-day outlook
places.php                Saved cities and search history
about.php                 Project information
save.php                  Save / remove a preferred city
api/weather.php           JSON weather endpoint
includes/                 API client, configuration, storage, and shared UI
assets/css/style.css      Page styles
data/store.json           Per-browser history and saved places (created at runtime)
cache/                    Short-lived API responses
```

Weather data is provided by [Open-Meteo](https://open-meteo.com/). City search uses the [Open-Meteo Geocoding API](https://open-meteo.com/en/docs/geocoding-api), and browser location names use [OpenStreetMap Nominatim](https://nominatim.org/release-docs/latest/api/Reverse/). Place data attribution: [© OpenStreetMap contributors](https://www.openstreetmap.org/copyright).
