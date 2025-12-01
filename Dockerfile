FROM php:8.3-fpm

# Instalar dependências do Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    nginx \
    supervisor \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev

# Extensões PHP
RUN docker-php-ext-install pdo pdo_mysql mbstring zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar código
COPY . .

# Instalar dependências
RUN composer install --no-dev --optimize-autoloader --prefer-dist

# Remover .env local para usar variáveis do Railway
RUN rm -f .env

# Permissões
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Copiar configs de produção
COPY deployment/nginx.conf /etc/nginx/nginx.conf
COPY deployment/supervisor.conf /etc/supervisor/conf.d/supervisor.conf

EXPOSE 8080

CMD ["supervisord", "-n"]
