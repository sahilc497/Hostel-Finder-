FROM php:8.2-apache

# Install PostgreSQL extensions
RUN docker-php-ext-install pdo pdo_pgsql

# Enable Apache rewrite (for clean URLs)
RUN a2enmod rewrite

# Copy project files to Apache root
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
