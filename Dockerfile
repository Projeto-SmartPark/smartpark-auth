FROM php:8.3-fpm

# Dependências do Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev

# Extensões PHP necessárias
RUN docker-php-ext-install pdo pdo_mysql mbstring zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# GARANTIR QUE NENHUM .env LOCAL ENTRE NA IMAGEM
RUN rm -f .env

# Copiar código
COPY . .

# Instalar dependências
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# limpar cache
RUN php artisan config:clear || true

# Porta RailWay
EXPOSE 8080

# Iniciar Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
