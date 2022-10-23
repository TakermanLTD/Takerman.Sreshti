FROM wordpress:latest

RUN apt update && \
    apt upgrade -y && \
    apt autoremove && \
    apt install -y \
    vim \
    wget \
    mariadb-client

COPY php.ini /usr/local/etc/php
COPY wp-content/plugins/ /var/www/html/wp-content/plugins
COPY wp-content/themes/ /var/www/html/wp-content/themes
COPY wp-content/uploads/ /var/www/html/wp-content/uploads

VOLUME ["/wp-content/themes","/wp-content/plugins","/wp-content/uploads"]

RUN wget https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    php wp-cli.phar --info&& \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp && \
    rm /usr/local/etc/php/php.ini-development && \
    rm /usr/local/etc/php/php.ini-production