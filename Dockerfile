# syntax=docker/dockerfile:1

FROM php:8.5-apache

# Szükséges kiegészítők telepítése
# mysqli: adatbázis kapcsolathoz
# headers, rewrite: .htaccess és biztonsági beállításokhoz
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite headers

# Production php.ini használata
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Kód másolása
COPY . /var/www/html/

# Port expose 
EXPOSE 80

# Jogosultságok beállítása
RUN chown -R www-data:www-data /var/www/html

USER www-data
