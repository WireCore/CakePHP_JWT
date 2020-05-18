FROM phpdockerio/php74-fpm:latest

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install  php7.4-mysql php7.4-intl php7.4-mbstring php7.4-gd php7.4-bz2 php7.4-curl php7.4-fileinfo php7.4-gettext php7.4-mbstring php7.4-exif php7.4-mysqli php7.4-pdo-mysql php7.4-pdo-sqlite php7.4-soap php7.4-sqlite3\
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

WORKDIR "/var/www/html"

RUN composer create-project --prefer-dist cakephp/app:4.* .