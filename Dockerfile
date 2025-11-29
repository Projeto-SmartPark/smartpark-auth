FROM php:8.2-fpm

# Dependências do Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev

# Extensões PHP necessárias
RUN docker-php-ext-install pdo pdo_mysql mbstring zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Diretório da aplicação
WORKDIR /var/www

# Copiar o código do auth service
COPY . .

# Instalar dependências
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Limpar cache
RUN php artisan config:clear || true

# Expor porta do Railway
EXPOSE 8080

# Iniciar o serviço Auth
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
