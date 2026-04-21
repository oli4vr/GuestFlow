# GuestFlow - Guest Check-in Application

A lightweight PHP web application for guest check-in via QR code scanning, now containerized with Docker.

## Features

- ✅ QR code scanning for guest check-in
- 📊 Real-time attendance statistics
- 📱 Mobile-friendly interface
- 🔒 SSL/TLS encrypted HTTPS
- 🐳 Dockerized for easy deployment

---

## Quick Start (Docker)

### Prerequisites

- Docker installed on your system
- Docker Compose (version 2+)

### Installation

1. **Navigate to the project directory:**
   ```bash
   cd /home/olivier/cdev/pfb/gfdocker
   ```

2. **Build the Docker image:**
   ```bash
   sudo make build
   ```
   This creates a Docker image with the application, SSL certificates, and default guest list.

3. **Start the container:**
   ```bash
   sudo make run
   ```
   This starts the container and maps ports 8080 (HTTP) and 8443 (HTTPS).

4. **Access the application:**
   Open your browser and go to: **https://localhost:8443/**

---

## How It Works

### Architecture

The application runs in a Docker container with the following structure:

```
Container:
├── /var/www/html/     # Application code
├── /defaults/         # Default/example files (cert.pem, private.key, reception.csv)
└── /data/             # Runtime files (mounted to host /data)
```

### Data Persistence

The `/data` directory is mounted from the host system, allowing you to:

- **Modify the guest list** (`reception.csv`) - add/remove guests
- **Replace SSL certificates** - use valid certificates for production
- **Changes persist** across container restarts

### File Initialization

On first run, the container automatically:
1. Copies default files from `/defaults/` to `/data/`
2. Sets proper file ownership and permissions
3. Starts the Apache web server

### Guest Check-in Flow

1. **Guest Registration**: Guests receive a personalized invitation with a unique QR code
2. **Check-in**: At the event, scan the QR code using the application's camera
3. **Verification**: The app verifies the QR code against the guest list (CSV)
4. **Status**: Updates presence in real-time and shows confirmation

---

## QR Code Format

The QR code should contain a URL with the `qr_unique` parameter:

```
https://example.com?qr_unique=ABC123
```

The `qr_unique` value is normalized to uppercase alphanumeric characters only (A-Z, 0-9).

**Example valid QR codes:**
- `https://event.com?qr_unique=TEST123`
- `https://example.com?qr_unique=abc-456` (becomes `ABC456` after normalization)

---

## Multilingual Support

The application supports three languages:

| Language | Parameter | Example URL |
|----------|-----------|-------------|
| English | `lang=en` | `https://localhost:8443/?lang=en` |
| Dutch | `lang=nl` | `https://localhost:8443/?lang=nl` |
| French | `lang=fr` | `https://localhost:8443/?lang=fr` |

Language is preserved when navigating between pages.

---

## CSV Format

The guest list file (`reception.csv`) uses the following format:

```
qr_unique,nom,prenom,presence_status
ABC123,Smith,John,présent
DEF456,Johnson,Mary
```

- **Column 1**: QR unique identifier
- **Column 2**: Last name (nom)
- **Column 3**: First name (prénom)
- **Column 4**: Presence status - `présent` if scanned (empty if not)

---

## SSL Certificates

### Default (Development)

The container includes self-signed SSL certificates for development:

- **Certificate**: `/defaults/cert.pem`
- **Private Key**: `/defaults/private.key`
- Valid for 365 days
- CN=localhost

These are automatically copied to `/data/` on first run.

### Production

For production use, replace the certificates:

1. **Using Docker:**
   ```bash
   # Stop the container
   sudo make stop
   
   # Replace certificates in /data/
   sudo cp your-cert.pem /data/cert.pem
   sudo cp your-private.key /data/private.key
   sudo chown www-data:www-data /data/cert.pem /data/private.key
   
   # Restart
   sudo make run
   ```

2. **Or generate new self-signed certificates:**
   ```bash
   make certs
   ```

---

## Make Commands

| Command | Description |
|---------|-------------|
| `make build` | Build Docker image (no cache) |
| `make run` | Start container via docker-compose |
| `make stop` | Stop and remove container |
| `make clean` | Stop container and remove image |
| `make certs` | Generate self-signed SSL certificates |
| `make deploy` | Full deployment (copy, build, certs, run) |

### Using Docker Compose Directly

```bash
# Build and start
docker-compose up -d --build

# Stop
docker-compose down

# View logs
docker-compose logs -f
```

---

## Project Structure

```
gfdocker/
├── index.php               # Main scanning interface (QR reader + API)
├── admin.php               # Statistics dashboard (attendance pie chart)
├── includes/
│   ├── config.php          # Config + language loading
│   ├── guestflow.css       # Application styles
│   ├── lang-en.php         # English translations
│   ├── lang-fr.php         # French translations
│   └── lang-nl.php         # Dutch translations
├── js/                     # JavaScript libraries
│   ├── html5-qrcode.js     # QR code scanning
│   └── chart.js            # Charts for admin page
├── data/
│   └── reception.csv       # Guest list
├── ssl/                    # SSL certificates (for build)
│   ├── cert.pem
│   └── private.key
├── Dockerfile              # Docker image definition
├── docker-compose.yml      # Docker Compose configuration
├── docker-entrypoint.sh    # Container initialization script
├── Makefile                # Build and deployment commands
├── ssl.conf                # Apache SSL configuration
├── Changes-oli4vr.md       # Detailed change log
├── README.md               # This file
└── LICENSE                 # GPL-3.0 license
```

---

## Technical Details

### Container

- **Base Image**: `php:8.2-apache`
- **PHP Version**: 8.2
- **Web Server**: Apache with mod_ssl, mod_rewrite
- **Ports**: 8080 (HTTP), 8443 (HTTPS)

### Application

- **Backend**: PHP 8.2 + Apache
- **Frontend**: HTML5, JavaScript, CSS3
- **QR Scanning**: html5-qrcode library (uses camera API)
- **Charts**: Chart.js
- **Data Storage**: CSV file

---

## Browser Requirements

- Camera access (HTTPS or localhost required)
- Modern browser with MediaDevices API support
- JavaScript enabled
- Rear camera recommended for mobile devices

---

## Troubleshooting

### Container Won't Start

1. **Check logs:**
   ```bash
   sudo docker logs guestflow
   ```

2. **Common issues:**
   - Port 8443 already in use: Stop other services or change port mapping
   - SSL errors: Check certificate files in `/data/`
   - Permission denied: Ensure `/data/` is writable

### Camera Not Working

1. **Verify HTTPS:** Browser requires HTTPS or localhost
2. **Check permissions:** Grant camera access when prompted
3. **Mobile:** Ensure rear camera is selected

### CSV Not Updating

1. **Check permissions:** `/data/reception.csv` must be writable by www-data
2. **Verify mount:** Ensure `/data:/data` volume is mounted correctly
3. **Check logs:** Look for file access errors

---

## License

GPL-3.0 - See LICENSE file for details.

---

## Credits

- **Original application**: cybermonde.org
- **Containerization**: oli4vr
- **Date**: 2026-04-21

---

## Changelog

See `Changes-oli4vr.md` for detailed information about all changes made during containerization.
