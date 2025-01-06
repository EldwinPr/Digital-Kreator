FROM php:8.1-apache
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    zip \
    unzip \
    mariadb-client
RUN docker-php-ext-install mysqli pdo pdo_mysql mbstring intl
RUN a2enmod rewrite
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
WORKDIR /var/www/html
COPY . .
RUN chown -R www-data:www-data /var/www/html
