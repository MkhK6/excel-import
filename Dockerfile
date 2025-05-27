FROM php:8.4-fpm-alpine

# Установка системных зависимостей
RUN apk add --no-cache \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev

# Установка PHP расширений
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    gd

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Копирование файлов проекта
COPY . .

# Установка зависимостей Composer
RUN composer install --no-scripts --no-dev --optimize-autoloader

# Настройка прав
RUN chown -R www-data:www-data /var/www/html/storage

CMD ["php", "artisan", "serve", "--host=0.0.0.0"]