FROM php:8.2-apache

RUN echo "ServerName 127.0.0.1" >> /etc/apache2/apache2.conf

RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apt-get update &&\
    apt-get install -y zip

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions opcache intl pdo_pgsql xdebug openssl

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
    mv composer.phar /usr/local/bin/composer

RUN curl -1sLf https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh | bash - &&\
    apt-get install -y symfony-cli

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - &&\
    apt-get install -y nodejs &&\
    npm install -g npm@latest

COPY ./config/apache.conf /etc/apache2/sites-available/000-default.conf
COPY ./config/php-config.ini /usr/local/etc/php/conf.d/php-config-overrides.ini

WORKDIR /var/www/

ENTRYPOINT ["bash", "./zythologue-api/scripts.sh"]
