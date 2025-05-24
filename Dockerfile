FROM php:8.4-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

COPY . .

RUN curl -sS https://getcomposer.org/installer  | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-scripts --no-dev

CMD ["php", "artisan", "serve", "--host=0.0.0.0"]