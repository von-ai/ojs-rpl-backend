#!/bin/sh
set -e

# Pastikan folder penyimpanan runtime Laravel ada
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Hapus cache bootstrap lama (jika termount dari host) agar package discovery sinkron
rm -f /var/www/html/bootstrap/cache/packages.php \
      /var/www/html/bootstrap/cache/services.php \
      /var/www/html/bootstrap/cache/config.php \
      /var/www/html/bootstrap/cache/routes-*.php

# Pastikan folder aplikasi ada
mkdir -p /var/www/html/storage/app/private /var/www/html/storage/app/public 2>/dev/null || true

# Atur izin direktori agar bisa ditulis oleh web server / php-fpm (user www-data)
# Gunakan || true agar tidak error saat storage/app dimount dari CIFS / Azure Files
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true


# Jika di environment production dan file .env tersedia, optimasi cache Laravel
if [ "$APP_ENV" = "production" ]; then
    echo "Running Laravel production optimizations..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Jika container dipanggil dengan perintah khusus (misal: php artisan queue:work, artisan schedule:run, atau sh), jalankan perintah tersebut
if [ $# -gt 0 ]; then
    exec "$@"
fi

# Default: Menjalankan Supervisord (Nginx + PHP-FPM)
echo "Starting Nginx and PHP-FPM via Supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
