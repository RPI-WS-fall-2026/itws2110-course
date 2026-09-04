FROM php:8.3-apache

# The PHP base image ships without database drivers. Add the one we need.
RUN docker-php-ext-install pdo_mysql

COPY src/ /var/www/html/
