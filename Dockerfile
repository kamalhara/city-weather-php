FROM php:8.4-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev libpq-dev \
    && docker-php-ext-install -j"$(nproc)" curl pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

RUN sed -ri 's/^Listen 80$/Listen 10000/' /etc/apache2/ports.conf \
    && sed -ri 's/<VirtualHost \*:80>/<VirtualHost *:10000>/' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
COPY . /var/www/html

RUN mkdir -p /var/www/html/cache /var/www/html/data \
    && chown -R www-data:www-data /var/www/html/cache /var/www/html/data \
    && apache2ctl -t

EXPOSE 10000
