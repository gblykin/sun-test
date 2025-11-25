FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libxml2-dev \
    zlib1g-dev \
    default-libmysqlclient-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libzip-dev \
    libonig-dev \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mysqli \
    curl \
    intl \
    opcache \
    bcmath \
    iconv \
    sockets \
    dom \
    xml \
    gd \
    mbstring \
    zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure xdebug
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.max_nesting_level=512" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /srv/www/app


