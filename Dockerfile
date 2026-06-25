FROM dunglas/frankenphp:php8.4

RUN install-php-extensions pdo_mysql mysqli

WORKDIR /app

COPY . /app

CMD ["frankenphp", "run", "--config", "/app/Caddyfile"]
