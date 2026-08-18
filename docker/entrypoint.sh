#!/bin/sh
set -e

echo "Seeding demo data..."
php /var/www/html/database/seed.php

exec php -S 0.0.0.0:8000 -t public