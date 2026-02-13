FROM php:8.3-cli

LABEL org.opencontainers.image.authors="Sharemeister Dev Team"

# 1. Update and install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    git \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install mysqli pdo_mysql

# 3. SET PHP LIMITS (The fix for your PostTooLargeException)
# We create a custom .ini file in the conf.d directory
RUN echo "upload_max_filesize=64M" > /usr/local/etc/php/conf.d/sharemeister-limits.ini \
    && echo "post_max_size=128M" >> /usr/local/etc/php/conf.d/sharemeister-limits.ini \
    && echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/sharemeister-limits.ini

# 4. Install Composer (Pro-tip: copy from official image is faster and safer)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
