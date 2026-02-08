FROM php:8.4-fpm

WORKDIR /var/www/html

# 1. Dependencias del sistema y extensiones (incluye herramientas SSL)
RUN apt-get update && apt-get install -y \
    git curl wget libpng-dev libonig-dev libxml2-dev zip unzip nginx supervisor \
    cron certbot python3-certbot-nginx openssl ca-certificates \
    && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd xml
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs

# 2. CACHÉ DE COMPOSER: Copiar solo archivos de dependencias
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --no-scripts --no-autoloader --prefer-dist --no-dev

# 3. CACHÉ DE NPM: Copiar solo package.json
COPY package.json package-lock.json ./
RUN npm install

# 4. Copiar el resto del código
COPY . .

# 5. Finalizar instalaciones y BUILD
RUN composer dump-autoload --optimize
RUN npm run build

# 6. Linkear storage público
RUN php artisan storage:link || echo "Storage link ya existe"

# 7. Configuración de Nginx, PHP y Supervisor
COPY docker/app.conf /etc/nginx/sites-available/default
RUN rm -f /etc/nginx/sites-enabled/default && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
COPY docker/php-uploads.ini /usr/local/etc/php/conf.d/uploads.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 8. Directorios, permisos y scripts de inicio
RUN mkdir -p /var/run/php storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/run/php \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/run/php
COPY docker/start-container.sh /usr/local/bin/start-container.sh
COPY docker/ssl-check.sh /var/www/html/docker/ssl-check.sh
RUN chmod +x /usr/local/bin/start-container.sh /var/www/html/docker/ssl-check.sh

# 9. Configurar cron service
RUN service cron start || true

EXPOSE 80 443
CMD ["start-container.sh"]