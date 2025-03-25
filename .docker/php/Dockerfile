FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    libzip-dev \
    unzip \
    && docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    sockets \
    pcntl \
    zip

# Install Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /var/www

RUN chown -R www-data:www-data /var/www
