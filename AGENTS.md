# GuestFlow - AGENTS.md

## Architecture

PHP 8.2 app for guest check-in via QR code scanning. Dockerized with `php:8.2-apache`.

- **`index.php`** — QR scanner UI + POST API (scanning + presence update)
- **`admin.php`** — Attendance pie chart (Chart.js), Présents vs Absents
- **`includes/config.php`** — Sets `$csvFile = '/data/reception.csv'`, loads language files
- **`includes/lang-{en,fr,nl}.php`** — Translations, selected via `?lang=en|fr|nl`
- **`data/reception.csv`** — `qr_unique,nom,prenom,presence_status` (no header row)

## Critical Details

### CSV & Presence
- `presence_status` is set to `présent` on check-in
- `admin.php` uses **case-insensitive** check (`trim(strtolower($data[3])) === 'présent'`)
- `index.php` uses **case-sensitive** check (`trim($data[3]) === "présent"`)
- QR normalized: `strtoupper()` + `preg_replace('/[^A-Z0-9]/', '', $id)` — only A-Z, 0-9

### Scan Workflow
1. POST to `index.php` with `identifier` (the scanned URL)
2. Server extracts `qr_unique` query param, normalizes it, updates CSV
3. Returns JSON: `success` / `already_present` / `not_found` / `invalid`
4. 3-second cooldown between scans (`scanEnabled` flag in JS)

### Language
- `?lang=en|fr|nl` in URL; persisted across pages via query param
- Sanitized: `preg_replace('/[^a-z]/', '', $lang)` — lowercase letters only
- Defaults to `en`

### Deployment (Docker)
- `make build` → `make run` → https://localhost:8443/
- `make certs` generates self-signed SSL certs in `ssl/`
- Host `/data` mounted to container `/data` — CSV and SSL certs persist there
- Entrypoint copies defaults from `/defaults/` to `/data/` on first run
- CSV must be writable by `www-data` in the container

### No build/test/lint steps
- Pure PHP files + vendored JS (`js/html5-qrcode.js`, `js/chart.js`)
- Local dev: `php -S localhost:8000` (but camera needs HTTPS)
- Admin page has no auth — only linked via 🔒 in footer

## Gotchas
- Camera requires HTTPS or localhost — mobile browsers block HTTP camera
- Self-signed cert = browser warning on first visit
- `data/reception.csv` has **no header row** — first line is data
- `config.php` calls `die()` if CSV does not exist or is not writable
