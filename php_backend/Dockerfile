FROM php:8.1-apache

# Install mysqli and other necessary PHP extensions
RUN apt-get update \
    && docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

# Enable Apache mod_rewrite (if needed)
RUN a2enmod rewrite
