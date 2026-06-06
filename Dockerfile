# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1 — Build the Tailwind/Vite assets (Node only lives in this stage)
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json vite.config.js ./
RUN npm install
COPY resources ./resources
RUN npm run build

# ---------------------------------------------------------------------------
# Stage 2 — Install PHP dependencies (Composer only lives in this stage)
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --no-progress
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts

# ---------------------------------------------------------------------------
# Stage 3 — Lean runtime: php-fpm + nginx + supervisor on Alpine
# ---------------------------------------------------------------------------
FROM php:8.4-fpm-alpine AS app

ARG user=theonefact
ARG uid=1000

WORKDIR /var/www

# Runtime services and the few PHP extensions this app needs.
RUN apk add --no-cache nginx supervisor su-exec
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions pdo_sqlite mbstring \
    && rm /usr/local/bin/install-php-extensions

# Application user. php-fpm/nginx workers and the scheduler run as it so files
# written to the mounted /data volume are owned by your host user (uid 1000).
RUN adduser -D -u $uid -h /home/$user $user \
    && addgroup $user www-data \
    && sed -i "s/^user = www-data/user = $user/; s/^group = www-data/group = $user/" /usr/local/etc/php-fpm.d/www.conf \
    && echo "clear_env = no" >> /usr/local/etc/php-fpm.d/www.conf

# Application source plus the artifacts produced by the build stages.
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p /data /run/nginx \
        storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R $user:www-data storage bootstrap/cache /data \
    && chmod -R 775 storage bootstrap/cache

ENV APP_USER=$user \
    DB_DATABASE=/data/database.sqlite

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s \
    CMD php -r '$c=@file_get_contents("http://127.0.0.1:80/up");exit($c!==false?0:1);'

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
