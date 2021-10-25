FROM php:7.4-fpm

# Install selected extensions and other stuff
RUN apt-get update && apt-get install -y \
        zip unzip git \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd intl mysqli pdo_mysql

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR "/var/www/html"

RUN composer create-project --prefer-dist cakephp/app:4.* .
RUN composer require firebase/php-jwt
RUN bin/cake bake controller Auth