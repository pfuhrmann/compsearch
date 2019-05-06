FROM php:7.3-fpm

# Composer install.
RUN if [ ! -f /usr/bin/composer ]; then curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/bin/composer; fi

# Install server dependencies.
RUN apt-get update
RUN apt-get install -y git zip unzip curl nano net-tools zlib1g-dev mysql-client libzip-dev
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug
RUN docker-php-ext-install pdo_mysql zip

COPY ./ /code
RUN ln -s /code/console /bin/console

# Update permissions to make sure www-data can write to required files
RUN usermod -u 1000 www-data
RUN touch /var/log/xdebug.log
RUN chown -R www-data:www-data /var/log/xdebug.log

# Easy cli debugging with XDebug
RUN echo "export PHP_IDE_CONFIG=\"serverName=search\"" >> ~/.bashrc
