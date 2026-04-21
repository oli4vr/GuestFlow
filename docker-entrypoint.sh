#!/bin/bash
# Docker entrypoint script for GuestFlow
# Creates missing files in /data from defaults if they don't exist

set -e

# Check and create reception.csv if missing
if [ ! -f /data/reception.csv ] || [ -d /data/reception.csv ]; then
    if [ -f /defaults/reception.csv ]; then
        echo "Creating reception.csv from defaults..."
        rm -rf /data/reception.csv 2>/dev/null || true
        cp /defaults/reception.csv /data/reception.csv
        chown www-data:www-data /data/reception.csv
    else
        echo "Warning: /defaults/reception.csv not found, creating empty file"
        echo "# qr_unique,nom,prenom" > /data/reception.csv
        chown www-data:www-data /data/reception.csv
    fi
fi

# Check and create cert.pem if missing
if [ ! -f /data/cert.pem ] || [ -d /data/cert.pem ]; then
    if [ -f /defaults/cert.pem ]; then
        echo "Creating cert.pem from defaults..."
        rm -rf /data/cert.pem 2>/dev/null || true
        cp /defaults/cert.pem /data/cert.pem
        chown www-data:www-data /data/cert.pem
    else
        echo "Warning: /defaults/cert.pem not found"
    fi
fi

# Check and create private.key if missing
if [ ! -f /data/private.key ] || [ -d /data/private.key ]; then
    if [ -f /defaults/private.key ]; then
        echo "Creating private.key from defaults..."
        rm -rf /data/private.key 2>/dev/null || true
        cp /defaults/private.key /data/private.key
        chown www-data:www-data /data/private.key
        chmod 600 /data/private.key
    else
        echo "Warning: /defaults/private.key not found"
    fi
fi

# Execute the original command
exec "$@"
