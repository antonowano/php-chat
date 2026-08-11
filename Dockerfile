FROM openswoole/swoole:25.2-php8.4-alpine

COPY . /opt/app

WORKDIR /opt/app

EXPOSE 9501

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install

CMD php app/api.php
