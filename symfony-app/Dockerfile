FROM php:8.1-cli AS base

RUN apt-get update && apt-get install -y git unzip libpq-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql xml

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd -g 1000 app && useradd -u 1000 -g app -m app

WORKDIR /app
RUN chown app:app /app

FROM base AS dev

COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

USER app

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]

FROM base AS prod

USER app

COPY --chown=app:app . .

RUN composer install

EXPOSE 8000

ENTRYPOINT ["php"]
CMD ["-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]
