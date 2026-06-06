FROM php:8.4-fpm

# Arguments (override in docker-compose.yml to match your host user).
ARG user=theonefact
ARG uid=1000

# Install system dependencies.
RUN apt-get update && apt-get install -y \
    git \
    curl \
    ca-certificates \
    unzip \
    zip \
    libonig-dev \
    libxml2-dev \
    libsqlite3-dev \
    nginx \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js 22.x (used only to build the front-end assets).
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Install the PHP extensions this app needs (dom/xml ship with the base image).
RUN docker-php-ext-install pdo_sqlite mbstring

# Composer.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create the application user and run php-fpm/nginx workers as it so files
# written to the mounted /data volume are owned by your host user.
RUN useradd -G www-data,root -u $uid -d /home/$user $user \
    && mkdir -p /home/$user/.composer \
    && chown -R $user:$user /home/$user \
    && sed -i "s/^user = www-data/user = $user/; s/^group = www-data/group = $user/" /usr/local/etc/php-fpm.d/www.conf \
    && echo "clear_env = no" >> /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www

# PHP dependencies first (better layer caching).
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --no-progress

# Front-end dependencies.
COPY package.json vite.config.js ./
RUN npm install

# Application source, then finish the Composer + asset builds.
COPY . .
RUN composer dump-autoload --no-dev --optimize \
    && npm run build \
    && rm -rf node_modules

# Web server, process manager and entrypoint configuration.
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/the-one-fact.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p /data storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R $user:www-data /var/www/storage /var/www/bootstrap/cache /data \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

ENV APP_USER=$user \
    DB_DATABASE=/data/database.sqlite

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s \
    CMD php -r '$c=@file_get_contents("http://127.0.0.1:80/up");exit($c!==false?0:1);'

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
