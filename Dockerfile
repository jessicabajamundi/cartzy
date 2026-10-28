# Cartzy (Laravel 12) — production image.
# The database stays on Aiven; this image only runs the PHP app.
# Build:  docker build -t cartzy .
# Run:    docker run -p 8080:80 --env-file .env cartzy

# ---------- 1. PHP dependencies ----------
FROM composer:2.8 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts

# ---------- 2. Frontend assets (Vite) ----------
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json ./
RUN npm install --no-audit --no-fund
COPY . .
RUN npm run build

# ---------- 3. Runtime: PHP 8.2 + Apache ----------
FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libonig-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring zip gd bcmath exif opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Serve Laravel's public/ folder and allow its .htaccess rewrites.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf \
    && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

# Production PHP settings (uploads up to 10 MB, matching the product photo limit).
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf "upload_max_filesize=12M\npost_max_size=40M\nmemory_limit=256M\nopcache.enable=1\nopcache.validate_timestamps=0\n" > "$PHP_INI_DIR/conf.d/cartzy.ini"

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN php artisan package:discover --ansi \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Hosts such as Render/Railway pass the port in $PORT; default 80.
EXPOSE 80
CMD ["sh", "-c", "PORT=${PORT:-80}; sed -i \"s/Listen 80/Listen $PORT/\" /etc/apache2/ports.conf; sed -i \"s/:80>/:$PORT>/\" /etc/apache2/sites-available/000-default.conf; php artisan storage:link --force; php artisan migrate --force; php artisan optimize; exec apache2-foreground"]
