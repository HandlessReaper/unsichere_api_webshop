FROM php:8-apache

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf
COPY ./www/ /var/www/html/

EXPOSE 80
