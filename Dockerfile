# Use official PHP + Apache image
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies for PHP packages
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    curl \
    libicu-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip intl \
    && rm -rf /var/lib/apt/lists/*

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Copy project files
COPY . /var/www/html/

# Create uploads folder and set permissions
RUN mkdir -p /var/www/html/main/php/uploads \
    && chown -R www-data:www-data /var/www/html/main/php/uploads \
    && chmod -R 775 /var/www/html/main/php/uploads

# Declare uploads as a volume for persistence
VOLUME /var/www/html/main/php/uploads

# Install Composer (from official Composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Increase Composer memory limit and install PHP dependencies
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
