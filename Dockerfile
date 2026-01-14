# syntax=docker/dockerfile:1

FROM php:8.3-apache

# Szükséges kiegészítők telepítése
# mysqli: adatbázis kapcsolathoz
# headers, rewrite: .htaccess és biztonsági beállításokhoz
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite headers

# Production php.ini használata
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Port expose 
EXPOSE 80

# Jogosultságok beállítása
COPY --chown=www-data:www-data app assets *.php /var/www/html/

USER www-data
