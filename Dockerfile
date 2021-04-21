ARG PHP_VERSION=7.2

FROM spaceonfire/nginx-php-fpm:2.5.0-${PHP_VERSION}

ENV PATH="${PATH}:/var/www/html/vendor/bin" \
    COMPOSER_VERSION="v2" \
    APPLICATION_ENV="development" \
    SOF_PRESET="default"
