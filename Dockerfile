FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install intl mysqli pdo pdo_mysql zip opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Opcache optimization for smooth performance
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Production PHP settings
RUN echo "upload_max_filesize=50M" >> /usr/local/etc/php/conf.d/production.ini \
    && echo "post_max_size=50M" >> /usr/local/etc/php/conf.d/production.ini \
    && echo "memory_limit=256M" >> /usr/local/etc/php/conf.d/production.ini \
    && echo "display_errors=Off" >> /usr/local/etc/php/conf.d/production.ini \
    && echo "date.timezone=Asia/Jakarta" >> /usr/local/etc/php/conf.d/production.ini

WORKDIR /var/www/html

COPY apps/admin/ /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html
