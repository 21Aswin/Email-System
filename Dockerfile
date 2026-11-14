# ===================================================================
# Novalyx Cloud Webmail - Production Dockerfile for Render / Cloud
# ===================================================================
FROM php:8.2-apache

# Install required system packages, MariaDB server (self-contained fallback), and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    mariadb-server \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) pdo_mysql gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite and headers
RUN a2enmod rewrite headers

# Copy custom Apache virtualhost config
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy application code into container
COPY . /var/www/html/

# Create persistent storage and uploads directories with permissions
RUN mkdir -p /var/www/html/storage/attachments \
    /var/www/html/public/uploads/avatars \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/public/uploads \
    && chmod -R 775 /var/www/html/storage /var/www/html/public/uploads

# Configure entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose standard ports (Render uses dynamic $PORT, defaulting to 80 or 10000)
EXPOSE 80 10000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
