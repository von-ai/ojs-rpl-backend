# =============================================================================
# Stage 1: Build Frontend Assets (Vite)
# =============================================================================
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci --ignore-scripts
COPY resources ./resources
COPY vite.config.js ./
RUN mkdir -p public/build && (npm run build || true)

# =============================================================================
# Stage 2: Install Composer Dependencies
# =============================================================================
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# =============================================================================
# Stage 3: Production Runtime (PHP 8.4-FPM + Nginx on Alpine)
# =============================================================================
FROM php:8.4-fpm-alpine AS runner

LABEL maintainer="Benediktus Gianto Jarod"
LABEL description="SIRIDA Backend Production Container (Nginx + PHP 8.4 FPM) for Azure Container Apps"

WORKDIR /var/www/html

# Salin helper script untuk instalasi ekstensi PHP yang andal dan bebas timeout PECL
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install package sistem dan ekstensi PHP
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    ca-certificates \
    && install-php-extensions \
        pdo_mysql \
        bcmath \
        pcntl \
        opcache \
        zip \
        intl \
        redis \
    && rm -rf /var/cache/apk/*

# Salin binary composer untuk kemudahan operasional / artisan CLI di dalam container
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Salin file konfigurasi Nginx, PHP, Supervisor, dan Entrypoint
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Salin kode aplikasi proyek
COPY . .

# Salin vendor PHP dari stage builder
COPY --from=vendor /app/vendor ./vendor

# Salin aset frontend dari stage frontend
COPY --from=frontend /app/public/build ./public/build

# Atur kepemilikan dan hak akses berkas
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Port HTTP standar ACA / Nginx
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Perintah default: menjalankan Web Server (Nginx + PHP-FPM via Supervisord)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
