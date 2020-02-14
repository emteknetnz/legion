FROM php:7.1.20-apache

RUN apt-get update -y \
 && apt-get install -y \
    unzip wget \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
#    libmemcached-dev \
    zlib1g-dev libicu-dev libpq-dev libtidy-dev libzip-dev \
    libldap-dev libgmp-dev \
#    libmagickwand-dev  # for the image magick extension (imagick)
    nano

RUN docker-php-source extract \
 && docker-php-ext-install iconv \
 && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
 && docker-php-ext-install gd \
 && docker-php-ext-install intl \
 && docker-php-ext-install zip \
 && docker-php-ext-install ldap \
 && docker-php-ext-install gmp \
 && docker-php-ext-install mysqli \
 && docker-php-ext-install pgsql \
 && docker-php-ext-install pdo \
 && docker-php-ext-install pdo_mysql \
 && docker-php-ext-install pdo_pgsql \
 && docker-php-ext-install tidy \
 && docker-php-ext-install exif \
 && docker-php-ext-install bcmath \
 && docker-php-ext-install bz2 \
# commenting out to speed install up a little
# && yes '' | pecl install memcached && docker-php-ext-enable memcached \
# && yes '' | pecl install redis && docker-php-ext-enable redis \
# && yes '' | pecl install imagick && docker-php-ext-enable imagick \
# && yes '' | pecl install apcu && docker-php-ext-enable apcu \
 && docker-php-source delete

RUN a2enmod rewrite headers
