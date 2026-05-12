# GuestFlow Makefile

.PHONY: build run stop clean logs

IMAGE_NAME = guestflow
CONTAINER_NAME = guestflow

build:
	@echo "Building Docker image..."
	docker compose build --no-cache
	@echo "Build complete. Image: $(IMAGE_NAME)"

run:
	@echo "Starting GuestFlow container..."
	docker compose up -d
	@echo "Container started. Access at https://localhost:8443/"

stop:
	@echo "Stopping container..."
	docker compose down 2>/dev/null || true

clean: stop
	@echo "Cleaning up..."
	docker rmi $(IMAGE_NAME) 2>/dev/null || true
	docker images "*guestflow" | grep guestflow | cut -f 1 -d \ | xargs -n1 docker rmi
	@echo "Cleanup complete"

logs:
	@docker compose logs -f 2>/dev/null || echo "Container not running"

# Generate self-signed certificates for testing
certs:
	@echo "Generating self-signed SSL certificates..."
	@mkdir -p ssl
	@openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
		-keyout ssl/private.key \
		-out ssl/cert.pem \
		-subj "/C=FR/ST=France/L=Local/O=GuestFlow/CN=localhost" 2>/dev/null
	@echo "Certificates generated in ssl/"

# Copy current installation to build directory
copy:
	@echo "Copying current installation to build directory..."
	@cp -r /var/www/html/* .
	@cp /data/reception.csv data/reception.csv 2>/dev/null || true
	@echo "Files copied to current directory."

# Full deployment (copy, build, run)
deploy: copy build certs run
	@echo "Deployment complete!"
