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

# Criar pasta da aplicação
WORKDIR /var/www

# Copiar código
COPY . .

# Instalar dependências PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Remover .env local (para Railway usar envs próprias)
RUN rm -f .env

# ------------------------------
# NGINX CONFIG
# ------------------------------

COPY ./deployment/nginx.conf /etc/nginx/nginx.conf

# ------------------------------
# SUPERVISOR CONFIG
# ------------------------------
COPY ./deployment/supervisor.conf /etc/supervisor/conf.d/supervisor.conf

# Permissões storage
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Porta do Railway
EXPOSE 8080

CMD ["supervisord", "-n"]
