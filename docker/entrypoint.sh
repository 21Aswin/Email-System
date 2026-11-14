#!/bin/bash
set -e

# Support dynamic port binding on Render, Railway, or Heroku ($PORT env var)
PORT_TO_USE="${PORT:-80}"
echo "=> Configuring Apache to listen on port ${PORT_TO_USE}..."
sed -i "s/<VirtualHost \*:.*>/<VirtualHost \*:${PORT_TO_USE}>/" /etc/apache2/sites-available/000-default.conf
sed -i "s/Listen .*/Listen ${PORT_TO_USE}/" /etc/apache2/ports.conf

# Ensure writable directories exist with correct permissions
mkdir -p /var/www/html/storage/attachments /var/www/html/public/uploads/avatars
chown -R www-data:www-data /var/www/html/storage /var/www/html/public/uploads 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/public/uploads 2>/dev/null || true

# Run database migrations if DB_HOST or DATABASE_URL is set
if [ -n "$DB_HOST" ] || [ -n "$DATABASE_URL" ]; then
    echo "=> Database credentials detected. Running migrations..."
    php database/run_all_migrations.php || echo "=> Warning: Migrations will retry on next deployment or when DB is fully reachable."
else
    echo "=> Note: No DB_HOST or DATABASE_URL specified yet. Please set environment variables in Render Dashboard."
fi

echo "=> Launching Apache in foreground on port ${PORT_TO_USE}..."
exec apache2-foreground
