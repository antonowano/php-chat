FROM openswoole/swoole:25.2-php8.4-alpine

COPY . /opt/app

WORKDIR /opt/app

EXPOSE 9501

CMD php app/api.php
