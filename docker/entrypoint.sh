#!/bin/bash
set -e

# 1. Dynamic port binding on Render ($PORT env var, default 80 or 10000)
PORT_TO_USE="${PORT:-80}"
echo "=> [1/4] Configuring Apache to listen on port ${PORT_TO_USE}..."
echo "ServerName localhost" >> /etc/apache2/apache2.conf 2>/dev/null || true
sed -i "s/<VirtualHost \*:.*>/<VirtualHost \*:${PORT_TO_USE}>/" /etc/apache2/sites-available/000-default.conf
sed -i "s/Listen .*/Listen ${PORT_TO_USE}/" /etc/apache2/ports.conf

# 2. Ensure writable directories exist with correct permissions
mkdir -p /var/www/html/storage/attachments /var/www/html/public/uploads/avatars
chown -R www-data:www-data /var/www/html/storage /var/www/html/public/uploads 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/public/uploads 2>/dev/null || true

# 3. Determine if we should start built-in MariaDB database
IS_LOCAL=false
if [ -z "$DB_HOST" ] || [ "$DB_HOST" = "127.0.0.1" ] || [ "$DB_HOST" = "localhost" ]; then
    IS_LOCAL=true
fi

if [ "$IS_LOCAL" = "true" ] && [ -z "$DATABASE_URL" ]; then
    echo "=> [2/4] Starting internal MariaDB database server..."
    mkdir -p /var/run/mysqld /var/lib/mysql
    chown -R mysql:mysql /var/run/mysqld /var/lib/mysql 2>/dev/null || true
    
    # Initialize data directory if empty
    if [ ! -d "/var/lib/mysql/mysql" ]; then
        mysql_install_db --user=mysql --ldata=/var/lib/mysql >/dev/null 2>&1 || true
    fi

    service mariadb start || /etc/init.d/mariadb start || true

    # Wait for MariaDB to become ready
    echo "=> Waiting for MariaDB service to accept connections..."
    for i in $(seq 1 30); do
        if mysqladmin ping --silent 2>/dev/null; then
            echo "=> MariaDB is ready (attempt $i)."
            break
        fi
        sleep 1
    done

    # Configure root and database users
    echo "=> Configuring database and authentication..."
    mysql -u root <<EOSQL
CREATE DATABASE IF NOT EXISTS nkinfyx_email CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('Aswin@21');
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;

CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED VIA mysql_native_password USING PASSWORD('Aswin@21');
GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION;

CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED VIA mysql_native_password USING PASSWORD('Aswin@21');
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;

FLUSH PRIVILEGES;
EOSQL

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

# 4. Generate .env file for Apache PHP runtime
echo "=> Writing production .env configuration..."
cat <<EOF > /var/www/html/.env
APP_ENV=${APP_ENV:-production}
APP_DEBUG=${APP_DEBUG:-false}
APP_NAME="${APP_NAME:-Novalyx Cloud Mail}"
DB_HOST=${DB_HOST:-127.0.0.1}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-nkinfyx_email}
DB_USERNAME=${DB_USERNAME:-root}
DB_PASSWORD=${DB_PASSWORD:-Aswin@21}
SMTP_ENABLED=${SMTP_ENABLED:-true}
SMTP_HOST=${SMTP_HOST:-smtp.gmail.com}
SMTP_PORT=${SMTP_PORT:-587}
SMTP_ENCRYPTION=${SMTP_ENCRYPTION:-tls}
SMTP_USERNAME=${SMTP_USERNAME:-novalyx.workspace@gmail.com}
SMTP_PASSWORD=${SMTP_PASSWORD:-zbmw uyom twin cnnu}
SMTP_FROM_ADDRESS=${SMTP_FROM_ADDRESS:-novalyx.workspace@gmail.com}
SMTP_FROM_NAME="${SMTP_FROM_NAME:-Novalyx Technologies}"
MAX_ATTACHMENT_SIZE_MB=${MAX_ATTACHMENT_SIZE_MB:-15}
STORAGE_PATH=${STORAGE_PATH:-storage/attachments}
EOF
chown www-data:www-data /var/www/html/.env 2>/dev/null || true
chmod 644 /var/www/html/.env 2>/dev/null || true

# 5. Run database migrations & seeding (with retry)
echo "=> [3/4] Running automated database migrations & seeding..."
php database/run_all_migrations.php || {
    echo "=> Migration retry in 3 seconds..."
    sleep 3
    php database/run_all_migrations.php
}

echo "=> [4/4] Launching Apache web server on port ${PORT_TO_USE}..."
exec apache2-foreground
