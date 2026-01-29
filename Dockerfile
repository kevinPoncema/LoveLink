FROM php:8.4-fpm

WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    xml

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --no-progress --prefer-dist --no-dev --optimize-autoloader

# Install Node dependencies
RUN npm install

# Create necessary directories
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views database && \
    touch database/database.sqlite && \
    chown -R www-data:www-data /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8000 5173

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
