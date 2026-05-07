FROM php:8.2-apache

# Install system dependencies and locales
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    locales \
    && rm -rf /var/lib/apt/lists/*

# Configure locales
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    echo "cs_CZ.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen

# Set environment variables for locale
ENV LANG=en_US.UTF-8 \
    LANGUAGE=en_US:en \
    LC_ALL=en_US.UTF-8

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite and headers
RUN a2enmod rewrite headers

# Set Apache default charset
RUN echo "AddDefaultCharset UTF-8" >> /etc/apache2/apache2.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Configure Apache DocumentRoot to point to current directory (default is /var/www/html)
# We need to allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
