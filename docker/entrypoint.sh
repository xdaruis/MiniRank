#!/bin/sh
set -e

DB_FILE=${DATABASE_PATH:-/var/www/html/database/minirank.db}

if [ ! -f "$DB_FILE" ]; then
    echo "No database detected. Seeding demo data..."
    php /var/www/html/database/seed.php
fi

exec php -S 0.0.0.0:8000 -t public