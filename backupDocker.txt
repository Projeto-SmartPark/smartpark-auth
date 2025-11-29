FROM php:8.4-cli

WORKDIR /var/www

COPY . .

RUN apt-get update && apt-get install -y git unzip libzip-dev \
    && docker-php-ext-install pdo_mysql zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

CMD ["bash", "-c", "composer install && php artisan serve --host=0.0.0.0 --port=8000"]
