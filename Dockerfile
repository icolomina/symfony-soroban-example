FROM ubuntu:latest

# Set non-interactive mode for apt
ARG DEBIAN_FRONTEND=noninteractive

# Update and install required packages in a single RUN command
RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y \
        apt-utils \
        locales \
        software-properties-common \
        nginx \
        sqlite \
        curl \
        git 

RUN add-apt-repository ppa:ondrej/php
RUN apt-get update && \
    apt-get install -y \ 
    php8.2 \
    php8.2-fpm \
    php8.2-xml \
    php8.2-mbstring \
    php8.2-curl \
    php8.2-gmp \
    php8.2-gd \
    php8.2-sqlite3 \
    php8.2-bcmath 

RUN apt-get clean 


# Set the locale
RUN locale-gen es_ES.UTF-8
ENV LC_ALL es_ES.UTF-8

# Set working directory
WORKDIR /var/www/crypto-bills-dapp

# Copy application files
COPY . .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js using NVM
ENV NODE_VERSION=20.0.0
ENV NVM_DIR=/root/.nvm
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash && \
    . "$NVM_DIR/nvm.sh" && \
    nvm install ${NODE_VERSION} && \
    nvm alias default v${NODE_VERSION} && \
    ln -s "$NVM_DIR/versions/node/v${NODE_VERSION}/bin/node" /usr/local/bin/node && \
    ln -s "$NVM_DIR/versions/node/v${NODE_VERSION}/bin/npm" /usr/local/bin/npm

# Verify Node.js and npm installation
RUN node --version && npm --version

# Install PHP dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install && \
    bin/console cache:clear && \
    bin/console doctrine:schema:drop --force && \
    bin/console doctrine:schema:create && \
    bin/console app:setup

# Install Node.js dependencies
RUN npm install && npm run dev

# Configure Nginx
RUN rm /etc/nginx/sites-enabled/default && \
    cp nginx/vhost.conf /etc/nginx/sites-available/ && \
    ln -s /etc/nginx/sites-available/vhost.conf /etc/nginx/sites-enabled/vhost.conf

# Set permissions
RUN chmod -R 777 var/

# Define default command
CMD service php8.2-fpm start && nginx -g 'daemon off;'

# Expose ports
EXPOSE 80
