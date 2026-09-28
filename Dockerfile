FROM php:8.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends libssl-dev && pecl install redis && docker-php-ext-enable redis && docker-php-ext-install pdo pdo_mysql && rm -rf /var/lib/apt/lists/* && a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
