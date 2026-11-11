
FROM php:8.2-apache

WORKDIR /var/www/html

# Enable Apache rewrite module
RUN a2enmod rewrite

# Configure Apache to listen on Render's port
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:10000>/' /etc/apache2/sites-available/000-default.conf

COPY . .

# Prepare writable application data directory
RUN mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html/data \
    && chmod 755 /var/www/html/data

EXPOSE 10000

CMD ["apache2-foreground"]
