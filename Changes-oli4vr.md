# GuestFlow Changes - oli4vr

## Summary of Changes

This document summarizes all changes made to the original GuestFlow source code since the containerization project began.

---

## 1. Source Code Changes

### 1.1 Multilingual Support

**Files modified:**
- `includes/config.php` - Added language detection and loading logic
- `includes/lang-en.php` - English translations (new)
- `includes/lang-fr.php` - French translations (new)
- `includes/lang-nl.php` - Dutch translations (new)
- `index.php` - Updated to use translation strings
- `admin.php` - Updated to use translation strings

**Details:**
- Added `lang` query parameter support (`lang=en`, `lang=nl`, `lang=fr`)
- Default language is English
- All user-facing text is now translatable
- Source code comments are in English

### 1.2 Camera Access Fix

**File modified:** `index.php`

**Details:**
- Changed from automatic camera start on page load to user-triggered button
- Added "Enable Camera" button for modern browser compatibility
- Fixed `facingMode` from `{ exact: "environment" }` to `"environment"`
- Camera now starts automatically but falls back to button if needed

### 1.3 Language Parameter in Links

**Files modified:**
- `index.php` - Admin link now includes `?lang=<?php echo $lang; ?>`
- `admin.php` - Home link now includes `?lang=<?php echo $lang; ?>`

**Details:**
- Language preference is preserved when navigating between pages

### 1.4 CSS Styling

**File modified:** `includes/guestflow.css`

**Details:**
- No functional changes - only formatting cleanup

---

## 2. Docker Container Build

### 2.1 New Files Created

**`Dockerfile`** - Docker image definition
- Base image: `php:8.2-apache`
- Enables SSL and rewrite modules
- Copies application files to `/var/www/html/`
- Copies default files to `/defaults/` for reference
- Includes entrypoint script for file initialization

**`docker-entrypoint.sh`** - Container entrypoint script
- Copies files from `/defaults/` to `/data/` if they don't exist
- Handles missing files gracefully with warnings
- Sets proper file ownership and permissions

**`docker-compose.yml`** - Docker Compose configuration
- Defines guestflow service
- Maps ports 8080:80 and 8443:443
- Mounts `/data:/data` for persistent storage
- Configures Apache user/group

**`Makefile`** - Build and deployment automation
- `make build` - Build Docker image with no cache
- `make run` - Start container via docker-compose
- `make stop` - Stop and remove container
- `make clean` - Stop and remove image
- `make certs` - Generate self-signed SSL certificates
- `make deploy` - Full deployment workflow

**`ssl.conf`** - Apache SSL configuration
- HTTP to HTTPS redirect
- SSL certificate paths: `/data/cert.pem` and `/data/private.key`

### 2.2 Directory Structure

```
gfdocker/
├── Dockerfile              # Docker image definition
├── docker-entrypoint.sh    # Container initialization script
├── docker-compose.yml      # Docker Compose configuration
├── Makefile                # Build and deployment commands
├── ssl.conf                # Apache SSL configuration
├── index.php               # Main application (updated)
├── admin.php               # Admin dashboard (updated)
├── includes/
│   ├── config.php          # Config + language loading
│   ├── guestflow.css       # Styles
│   ├── lang-en.php         # English translations
│   ├── lang-fr.php         # French translations
│   └── lang-nl.php         # Dutch translations
├── js/                     # JavaScript libraries (unchanged)
├── data/
│   └── reception.csv       # Guest list (copied to /defaults)
├── ssl/                    # SSL certificates
│   ├── cert.pem
│   └── private.key
└── README.md               # Updated documentation
```

### 2.3 File Organization

**Container filesystem:**
- `/var/www/html/` - Application code
- `/defaults/` - Default/example files (cert.pem, private.key, reception.csv)
- `/data/` - Runtime files (mounted to host `/data`)

**Build context:**
- Docker image is self-contained (no external dependencies)
- SSL certificates included in `/defaults/`

### 2.4 SSL Certificates

**Generation:**
- Self-signed certificates created with `make certs`
- Valid for 365 days
- CN=localhost for development

**Runtime:**
- Copied from `/defaults/` to `/data/` on first run
- Private key has 600 permissions

### 2.5 Persistence

**Host directory:** `/data/`

**Files stored:**
- `reception.csv` - Guest list with check-in status
- `cert.pem` - SSL certificate
- `private.key` - SSL private key

**User can:**
- Modify reception.csv to add/remove guests
- Replace SSL certificates with valid ones
- Changes persist across container restarts

### 2.6 Image Size and Optimization

**Image size:** ~400MB (php:8.2-apache base)

**Optimizations:**
- Multi-stage build not used (simple single-stage)
- No unnecessary packages installed
- Only required Apache modules enabled (ssl, rewrite)

### 2.7 Running the Container

**Quick start:**
```bash
cd gfdocker
make build
make run
```

**Access:**
- HTTPS: https://localhost:8443/
- HTTP auto-redirects to HTTPS

**Stop:**
```bash
make stop
```

**Rebuild:**
```bash
make clean
make build
make run
```

---

## 3. Testing

### 3.1 Functional Tests

- ✅ Container builds successfully
- ✅ HTTPS access works
- ✅ QR code scanning interface loads
- ✅ Check-in functionality works
- ✅ CSV updates persist in /data
- ✅ Language switching works (en/nl/fr)
- ✅ Admin page shows statistics

### 3.2 Browser Compatibility

- Tested with Chrome, Firefox, Safari
- Camera access requires HTTPS
- Modern browsers with MediaDevices API support

---

## 4. Known Limitations

### 4.1 SSL Certificate

- Self-signed certificate for development
- Production should use valid SSL certificate
- Certificate must be placed in `/data/`

### 4.2 CSV Permissions

- Container expects `/data/` to be writable by www-data
- Host `/data/` must have proper permissions

### 4.3 Camera Access

- Requires HTTPS (not HTTP)
- Mobile browsers require user interaction
- Rear camera preferred on mobile devices

---

## 5. Migration from Original

### 5.1 Original Files

The following files were part of the original application:
- `index.php` - Updated with translations and camera fix
- `admin.php` - Updated with translations
- `includes/config.php` - Updated with language support
- `includes/guestflow.css` - No functional changes
- `js/html5-qrcode.js` - Unchanged
- `js/chart.js` - Unchanged

### 5.2 New Dependencies

**Container:**
- Docker (version 20+)
- Docker Compose (version 2+)

**Build host:**
- OpenSSL (for certificate generation)
- Make (for build automation)

---

## 6. Version History

| Version | Date | Changes |
|---------|------|---------|
| 0.1 | 2026-04-21 | Initial containerization |

---

## 7. Credits

- Original application: cybermonde.org
- Containerization: oli4vr
- Date: 2026-04-21

---

## 8. License

GPL-3.0 - Same as original application
