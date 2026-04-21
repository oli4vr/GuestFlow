# GuestFlow - AGENTS.md

## Architecture

Simple PHP app for guest check-in via QR code scanning:

- **`index.php`** - Main scanning interface (QR reader + backend API)
- **`admin.php`** - Statistics dashboard (pie chart of attendance)
- **`includes/config.php`** - Environment-aware CSV path configuration
- **`data/reception.csv`** - Guest list: `qr_unique,nom,prenom,presence_status`

## Critical Details

### CSV Format & Presence Tracking
- File: `data/reception.csv`
- Columns: `qr_unique,nom,prenom,presence_status`
- Presence marked as `présent` (case-insensitive check in `admin.php`)
- QR codes are normalized: uppercase, alphanumeric only (`preg_replace('/[^A-Z0-9]/', '', $id)`)

### Environment Detection
`includes/config.php:12-22` checks server IP/name to select CSV path:
- LAN (`192.168.*`): `/var/www/data/reception.csv`
- Public domain (`guestflow.domaine.tld`): `../data/reception.csv`
- Unknown env: **script dies** - ensure proper `SERVER_NAME` or `SERVER_ADDR`

### Scan Workflow
1. User scans QR code via `html5-qrcode.js` (uses rear camera on mobile)
2. Extracts `qr_unique` query param from URL
3. POST to `index.php` → updates CSV → returns JSON status:
   - `success`: newly checked in
   - `already_present`: already scanned
   - `not_found`: invalid QR code
4. Cooldown: 3 seconds between scans (`scanEnabled` flag)

### Dependencies
- JS: `js/html5-qrcode.js`, `js/chart.js` (for admin)
- CSS: `includes/guestflow.css`
- No composer.json - pure PHP5+ with no external dependencies

## Common Pitfalls

- **CSV write permissions**: `config.php:29-31` checks `is_writable()` and dies if false
- **QR normalization**: The `qr_unique` in CSV must match normalized format (uppercase, no special chars)
- **Camera access**: Requires HTTPS or localhost; mobile browsers block HTTP camera access
- **No authentication**: Admin page has no login - secure via obscurity only (footer link 🔒)

## Development

- No build/test/lint steps - edit PHP files directly
- Test locally with PHP built-in server: `php -S localhost:8000`
- Admin chart shows: Présents vs Absents count

## License

GPL-3.0
