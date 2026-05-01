FROM php:8.4-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    autoconf \
    build-essential \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    libmagick++-dev \
    libmagickwand-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    zip \
    unzip \
    zlib1g-dev \
    libmcrypt-dev \
    libpng-dev \
    libzip-dev \
    libsodium-dev \
    poppler-utils

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Configurando a extensão do GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg=/usr/include/ --enable-gd


# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring xml exif pcntl bcmath gd zip sodium intl
COPY docker-compose/app/php.ini /usr/local/etc/php
# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# get node and npm

COPY --from=node:lts-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:lts-slim /usr/local/lib/node_modules/npm /usr/local/lib/node_modules/npm
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory

WORKDIR /var/www

USER $user