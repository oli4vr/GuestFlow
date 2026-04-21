# GuestFlow Docker Image
FROM php:8.2-apache

# Enable Apache SSL and rewrite modules
RUN a2enmod ssl rewrite

# Create data and defaults directories
RUN mkdir -p /data /defaults /docker-entrypoint.d && chown -R www-data:www-data /data /defaults

# Copy application files
COPY index.php /var/www/html/
COPY admin.php /var/www/html/
COPY includes/ /var/www/html/includes/
COPY js/ /var/www/html/js/

# Copy data files to /defaults for reference
COPY data/reception.csv /defaults/reception.csv
COPY ssl.conf /etc/apache2/sites-available/000-default.conf

# Copy SSL certificates to /defaults for reference
COPY ssl/cert.pem /defaults/cert.pem
COPY ssl/private.key /defaults/private.key

# Create entrypoint script to handle missing files
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set permissions
RUN chown -R www-data:www-data /var/www/html /defaults

# Expose ports
EXPOSE 80 443

# Use entrypoint script
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
