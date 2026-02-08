FROM php:8.4-fpm

WORKDIR /var/www/html

# 1. Dependencias del sistema y extensiones
RUN apt-get update && apt-get install -y \
    git curl wget libpng-dev libonig-dev libxml2-dev zip unzip nginx supervisor \
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

# 7. Configuración de Nginx y Supervisor
COPY docker/app.conf /etc/nginx/sites-available/default
RUN rm -f /etc/nginx/sites-enabled/default && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 8. Directorios, permisos y script de inicio
RUN mkdir -p /var/run/php storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/run/php \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/run/php
COPY docker/start-container.sh /usr/local/bin/start-container.sh
RUN chmod +x /usr/local/bin/start-container.sh

EXPOSE 80
CMD ["start-container.sh"]