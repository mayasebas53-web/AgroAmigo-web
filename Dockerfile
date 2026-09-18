FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pgsql pdo_pgsql \
    && apt-get purge -y --auto-remove libpq-dev \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod headers

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

COPY docker-entrypoint.sh /usr/local/bin/agroamigo-entrypoint
RUN chmod +x /usr/local/bin/agroamigo-entrypoint

ENTRYPOINT ["agroamigo-entrypoint"]