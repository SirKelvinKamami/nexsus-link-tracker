# syntax=docker/dockerfile:1

# ---------- base: PHP 8.2 fpm + all app extensions ----------
FROM php:8.2-fpm-alpine AS base

RUN apk add --no-cache \
        unzip \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        libxml2-dev \
        icu-dev \
        libzip-dev \
        postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        xml \
        zip \
        intl \
        opcache \
    && apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# ---------- build: composer vendor dir + compiled frontend assets ----------
FROM base AS build

RUN apk add --no-cache nodejs npm

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
    && npm ci --no-audit --no-fund \
    && npm run production \
    && rm -rf node_modules

# ---------- runtime: slim image, non-root, production opcache ----------
FROM base AS runtime

# runtime shared libraries for the compiled extensions (no -dev packages)
RUN apk add --no-cache \
        libpng \
        libjpeg-turbo \
        freetype \
        oniguruma \
        icu-libs \
        libzip \
        postgresql-libs \
        nginx \
        supervisor

WORKDIR /var/www/html

COPY --from=build --chown=www-data:www-data /var/www/html /var/www/html

# www-data is the built-in php-fpm pool user on the official image.
# /etc/nginx/http.d is chowned so the entrypoint can rewrite the listen
# port from $PORT at boot; /var/lib/nginx so nginx can run unprivileged.
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chown -R www-data:www-data /etc/nginx/http.d /var/lib/nginx \
    && mkdir -p /var/lib/nginx/logs /run/nginx /var/log/supervisor \
    && chown -R www-data:www-data /run/nginx /var/log/supervisor

# Production opcache: timestamps disabled (immutable image), generous caches
RUN { \
        echo "opcache.enable=1"; \
        echo "opcache.enable_cli=0"; \
        echo "opcache.memory_consumption=192"; \
        echo "opcache.interned_strings_buffer=16"; \
        echo "opcache.max_accelerated_files=20000"; \
        echo "opcache.validate_timestamps=0"; \
    } > /usr/local/etc/php/conf.d/opcache-prod.ini

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Run everything (entrypoint, php-fpm, nginx) as the unprivileged pool user.
# The entrypoint binds nginx to the platform-provided port (Render sets PORT;
# default 8080) regardless of which listen the build baked in.
USER www-data

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
