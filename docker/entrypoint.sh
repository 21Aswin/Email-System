#!/bin/bash
set -e

# Support dynamic port binding on Render ($PORT env var, default 80 or 10000)
PORT_TO_USE="${PORT:-80}"
echo "=> [1/4] Configuring Apache to listen on port ${PORT_TO_USE}..."
echo "ServerName localhost" >> /etc/apache2/apache2.conf 2>/dev/null || true
sed -i "s/<VirtualHost \*:.*>/<VirtualHost \*:${PORT_TO_USE}>/" /etc/apache2/sites-available/000-default.conf
sed -i "s/Listen .*/Listen ${PORT_TO_USE}/" /etc/apache2/ports.conf

# Ensure writable directories exist with correct permissions
mkdir -p /var/www/html/storage/attachments /var/www/html/public/uploads/avatars
chown -R www-data:www-data /var/www/html/storage /var/www/html/public/uploads 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/public/uploads 2>/dev/null || true

# Determine if we should start built-in MariaDB database
IS_LOCAL=false
if [ -z "$DB_HOST" ] || [ "$DB_HOST" = "127.0.0.1" ] || [ "$DB_HOST" = "localhost" ]; then
    IS_LOCAL=true
fi

if [ "$IS_LOCAL" = "true" ] && [ -z "$DATABASE_URL" ]; then
    echo "=> [2/4] No external DB_HOST provided. Starting internal MariaDB database engine..."
    mkdir -p /var/run/mysqld /var/lib/mysql
    chown -R mysql:mysql /var/run/mysqld /var/lib/mysql 2>/dev/null || true
    service mariadb start || /etc/init.d/mariadb start || mysqld_safe &
    sleep 3

    # Configure root access and create database
    mysql -e "CREATE DATABASE IF NOT EXISTS nkinfyx_email CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true
    mysql -e "CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY 'Aswin@21'; GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION; FLUSH PRIVILEGES;" 2>/dev/null || true
    mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'Aswin@21'; FLUSH PRIVILEGES;" 2>/dev/null || true

    # Write /root/.my.cnf so mariadb-admin and service commands authenticate smoothly
    mkdir -p /root
    cat <<EOF > /root/.my.cnf
[client]
user=root
password=Aswin@21
EOF
    chmod 600 /root/.my.cnf 2>/dev/null || true

    export DB_HOST="127.0.0.1"
    export DB_PORT="3306"
    export DB_DATABASE="nkinfyx_email"
    export DB_USERNAME="root"
    export DB_PASSWORD="Aswin@21"
else
    echo "=> [2/4] Connecting to external MySQL database: ${DB_HOST:-$DATABASE_URL}"
fi

# Run database migrations & seeding
echo "=> [3/4] Running automated database migrations & seeding..."
php database/run_all_migrations.php || echo "=> Warning: Migrations encountered an issue or database is starting up. Retrying..."

echo "=> [4/4] Launching Apache web server on port ${PORT_TO_USE}..."
exec apache2-foreground
