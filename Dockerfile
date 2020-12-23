FROM php:5.6-apache
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions mysql
VOLUME /var/www/html/
COPY . /var/www/html/
EXPOSE 80