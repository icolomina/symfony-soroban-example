FROM ubuntu:latest

ARG DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get upgrade -y

RUN apt-get -y install apt-utils
RUN apt-get -y install locales

# Set the locale
RUN locale-gen es_ES.UTF-8
ENV LANG es_ES.UTF-8
ENV LANGUAGE es_ES:en
ENV LC_ALL es_ES.UTF-8

RUN apt-get install -y software-properties-common
RUN apt-get install -y nginx sqlite curl git php8.1 php8.1-fpm php8.1-xml php8.1-mbstring php8.1-curl php8.1-gmp php8.1-gd php8.1-sqlite3 php8.1-bcmath

WORKDIR /var/www/crypto-bills-dapp
COPY . /var/www/crypto-bills-dapp

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

# Node
ENV NODE_VERSION=18.16.1
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
ENV NVM_DIR=/root/.nvm
RUN . "$NVM_DIR/nvm.sh" && nvm install ${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm use v${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm alias default v${NODE_VERSION}
ENV PATH="/root/.nvm/versions/node/v${NODE_VERSION}/bin/:${PATH}"
RUN node --version
RUN npm --version

WORKDIR /var/www/crypto-bills-dapp

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install
RUN bin/console doctrine:schema:create
RUN bin/console app:setup
RUN npm install
RUN npm run dev

RUN rm /etc/nginx/sites-enabled/default
COPY nginx/vhost.conf /etc/nginx/sites-available/
RUN ln -s /etc/nginx/sites-available/vhost.conf /etc/nginx/sites-enabled/vhost.conf

RUN chmod -R 777 var/

# Define default command.
CMD service php8.1-fpm start && nginx && tail -f /dev/null

# Expose ports.
EXPOSE 80