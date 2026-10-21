# City Weather Dashboard

A PHP weather dashboard that searches worldwide locations, detects the browser's location and names its nearby city, shows current conditions and the next 12 hours, and provides a seven-day outlook. It uses [Open-Meteo](https://open-meteo.com/) for weather data and city search, plus OpenStreetMap Nominatim for reverse geocoding; no API key is needed.

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
