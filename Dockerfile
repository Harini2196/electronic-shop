# Use official PHP image with Apache
FROM php:8.2-apache

# Install common PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY *.php /var/www/html/ && \
     /database/* /var/www/html/database/ && \
     /IFU_Assests/* /var/www/html/IFU_Assests/
# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Apache configuration: allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Expose port 80
EXPOSE 80

# Apache starts automatically with this base image