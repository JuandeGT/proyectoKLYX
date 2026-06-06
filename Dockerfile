FROM php:8.4-apache

# ——— Extensiones del sistema ———
RUN apt-get update && apt-get install -y \
        libpq-dev libzip-dev zip unzip git \
    && docker-php-ext-install pdo pdo_pgsql zip opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Opcache para producción
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.validate_timestamps=0'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# ——— Composer ———
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ——— Apache: apuntar a /public de Laravel ———
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
    && printf '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# ——— Código de la app ———
WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ——— Entrypoint: migra y arranca ———
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
