FROM php:8.3-cli

ARG CACHEBUSTER=1

RUN apt-get update && apt-get install -y \
    zip unzip git \
    libpng-dev libonig-dev libxml2-dev libzip-dev libicu-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 775 storage bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080

CMD php artisan config:clear && \
    php artisan l5-swagger:generate && \
    php artisan serve --host=0.0.0.0 --port=8080
