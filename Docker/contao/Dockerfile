######
# How to build
# sudo docker build --tag phpcontaoxdebug:latest .
######

FROM php:7.4.7-apache

ENV COMPOSER_MEMORY_LIMIT -1
ENV APACHE_DOCUMENT_ROOT /var/www/html/web

RUN apt-get update
RUN apt-get install -y libfreetype6-dev
RUN apt-get install -y libjpeg62-turbo-dev
RUN apt-get install -y libpng-dev
RUN apt-get install -y libicu-dev
RUN apt-get install -y zlib1g-dev
RUN apt-get install -y zip
RUN apt-get install -y libzip-dev
RUN apt-get install -y git
RUN pecl install xdebug
RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip
RUN docker-php-ext-install -j$(nproc) iconv
RUN docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-install -j$(nproc) intl
RUN docker-php-ext-install -j$(nproc) pdo_mysql
RUN docker-php-ext-install mysqli
RUN docker-php-ext-enable mysqli
RUN docker-php-ext-enable xdebug

RUN mkdir /var/www/html/web

RUN a2enmod rewrite

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# RUN echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.remote_connect_back=on" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.remote_host=192.168.1.126" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.profiler_enable=0" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.profiler_enable_trigger=1" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.profiler_enable_trigger_value=''" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.profiler_output_dir=/var/www/html" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.profiler_output_name='cachegrind.out.%p'" >> /usr/local/etc/php/conf.d/xdebug.ini

RUN echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.remote_connect_back=on" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.remote_host=192.168.1.126" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.profiler_enable=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.profiler_enable_trigger=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.profiler_enable_trigger_value=''" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.profiler_output_dir=/var/www/html" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.profiler_output_name='cachegrind.out.%p'" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

EXPOSE 80
