FROM php:7.1.20-apache

RUN apt-get update -y \
 && apt-get install -y \
    unzip wget \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
# commenting out to speed install up a little
# TODO renable
#    libmemcached-dev \
    zlib1g-dev libicu-dev libpq-dev libtidy-dev libzip-dev \
    libldap-dev libgmp-dev \
#    libmagickwand-dev  # for the image magick extension (imagick)
    nano

# install docker
RUN apt install -y apt-transport-https ca-certificates curl gnupg2 software-properties-common
RUN curl -fsSL https://download.docker.com/linux/debian/gpg | apt-key add -
RUN add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/debian $(lsb_release -cs) stable"
RUN apt update
RUN apt install -y docker-ce docker-ce-cli containerd.io

# install docker-compose
RUN curl -L "https://github.com/docker/compose/releases/download/1.25.3/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
RUN chmod +x /usr/local/bin/docker-compose

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
# commenting out bz2 because it failed to install, not sure if used anywhere
# "configure: error: Please reinstall the BZip2 distribution"
# && docker-php-ext-install bz2 \
# commenting out to speed install up a little
# TODO renable
# && yes '' | pecl install memcached && docker-php-ext-enable memcached \
# && yes '' | pecl install redis && docker-php-ext-enable redis \
# && yes '' | pecl install imagick && docker-php-ext-enable imagick \
# && yes '' | pecl install apcu && docker-php-ext-enable apcu \
 && docker-php-source delete

# set identifier file used by dockerenv/bootstrap.php
RUN touch /home/is_legion_docker.txt

# create a new group and add www-data (apache user) to it
# this is for when you're using a virtualbox on Win10 shared folder i.e.
# Win10 -> Ubuntu VM -> Dockerfile
RUN groupadd -g 998 myvboxsf
RUN usermod -aG myvboxsf www-data

RUN a2enmod rewrite headers
