FROM phpdockerio/php73-fpm:latest

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install  php7.3-mysql php7.3-intl php7.3-mbstring php7.3-gd php7.3-bz2 php7.3-curl php7.3-fileinfo php7.3-gettext php7.3-mbstring php7.3-exif php7.3-mysqli php7.3-pdo-mysql php7.3-pdo-sqlite php7.3-soap php7.3-sqlite3\
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*


WORKDIR "/var/www/managerpanel-usermanagement"
