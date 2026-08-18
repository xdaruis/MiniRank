FROM php:8.2-cli AS base

FROM base AS dev

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

EXPOSE 8000

CMD ["entrypoint.sh"]

FROM php:8.2-cli AS test

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apt-get update && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist

CMD ["sh", "-c", "composer install --no-interaction --prefer-dist && vendor/bin/phpunit"]

FROM php:8.2-fpm AS prod

WORKDIR /var/www

COPY . /var/www

RUN mkdir -p /var/www/data && chown -R www-data:www-data /var/www/data

USER www-data

EXPOSE 9000