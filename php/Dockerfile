FROM php:8.4-fpm-alpine

RUN apk add --no-cache icu-dev oniguruma-dev zip unzip git curl \
    && apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_mysql intl \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apk del .phpize-deps \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

COPY ./php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY ./app /var/www/html
COPY ./.env /var/www/.env

RUN curl -sS https://getcomposer.org/installer | tee composer-setup.php \
    && php composer-setup.php && rm composer-setup.php* \
    && chmod +x composer.phar && mv composer.phar /usr/bin/composer

EXPOSE 9000

CMD sh -c "composer install --no-dev --prefer-dist --no-interaction && php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration && php-fpm"

