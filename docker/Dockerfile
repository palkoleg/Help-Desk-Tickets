FROM php:7.4-fpm

RUN apt-get update -qq \
    && apt-get install -qy --no-install-recommends \
        git \
        openssl \
        librecode0 \
        uuid-dev \
        libmagickwand-dev \
        libsasl2-dev \
        imagemagick \
        libmagickwand-dev \
        libmagickcore-dev \
        libsqlite3-0 \
        libxml2

RUN apt-get update -qq \
    && apt-get install -qy --no-install-recommends \
        autoconf \
        file \
        g++ \
        gcc \
        libc-dev \
        make \
        cmake \
        curl \
        pkg-config \
        libtool \
        tar \
        libmcrypt-dev \
        libpng-dev \
        zip \
        unzip \
        wget


# Install composer
UN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
#Install xdebug
RUN pecl install xdebug-3.0.4 && docker-php-ext-enable xdebug

CMD ["php-fpm"]