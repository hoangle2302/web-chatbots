# =========================
# PHP Backend Dockerfile
# =========================

# Base image with FPM
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# -------------------------
# Install system dependencies & PHP extensions
# -------------------------
RUN apt-get update && apt-get install -y \
        zip unzip git curl libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli mbstring zip xml \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# -------------------------
# Install Composer
# -------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# -------------------------
# Copy PHP error config
# -------------------------
COPY deploy/docker/php/docker-php-errors.ini /usr/local/etc/php/conf.d/docker-php-errors.ini

# -------------------------
# Copy source code
# -------------------------
COPY src/php-backend/ /var/www/html/

# -------------------------
# Set permissions (if using mounted volumes)
# -------------------------
RUN chown -R www-data:www-data /var/www/html

# -------------------------
# Install PHP dependencies via Composer
# -------------------------
RUN composer install --no-dev --optimize-autoloader --no-interaction

# -------------------------
# Configure PHP-FPM
# -------------------------
# 1. Logs → stdout/stderr
# 2. Listen on 0.0.0.0:8000 so NGINX container can reach it
RUN sed -i "s|^error_log = .*|error_log = /proc/self/fd/2|" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s|^;catch_workers_output = .*|catch_workers_output = yes|" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s|^listen = .*|listen = 0.0.0.0:8000|" /usr/local/etc/php-fpm.d/www.conf
# -------------------------
# Expose FPM port (internal only)
# -------------------------
EXPOSE 8000

# -------------------------
# Start PHP-FPM in foreground (for Docker)
# -------------------------
CMD ["php-fpm", "-F"]
