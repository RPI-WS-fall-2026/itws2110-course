FROM php:8.3-apache

# No database driver. This example does not have a database.
COPY src/ /var/www/html/

# Create the data directory in the IMAGE, owned by the user Apache runs as.
# A fresh named volume mounted here is seeded from this path -- contents and
# ownership both. Without this, Docker creates the volume owned by root and
# the web server cannot write to it.
RUN mkdir -p /var/www/html/data && chown www-data:www-data /var/www/html/data
