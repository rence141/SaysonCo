# Use official PHP + Apache image
FROM php:8.2-apache

# Enable PHP extensions if needed
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html/

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port 80
EXPOSE 80
