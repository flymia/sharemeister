# Stage 1: PHP & Nginx Runtime
FROM php:8.3-fpm-alpine

# Install System Dependencies
RUN apk add --no-cache \
    nginx \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    zip \
    libzip-dev \
    unzip \
    git \
    oniguruma-dev

# Install PHP Extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \ 
    && docker-php-ext-install pdo_mysql mbstring gd zip bcmath

# Configure Nginx
COPY ./Docker/Production/nginx.conf /etc/nginx/http.d/default.conf

# Set Workdir
WORKDIR /var/www/html

# Copy Application Code
COPY . .

# Install Composer Dependencies (Production mode)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Set Permissions for Linux Webserver
# We use 'www-data' which is the standard user for php-fpm in Alpine
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose Port
EXPOSE 80

# Start Nginx and PHP-FPM
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
