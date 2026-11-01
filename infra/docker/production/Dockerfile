# syntax=docker/dockerfile:1.7
# Aurum — production image. Build from the REPOSITORY ROOT:
#
#   docker build -f infra/docker/production/Dockerfile --target app -t ghcr.io/<org>/aurum-app:<tag> .
#   docker build -f infra/docker/production/Dockerfile --target web -t ghcr.io/<org>/aurum-web:<tag> .
#
# Targets
#   app  php-fpm 8.3 (non-root www-data) — also runs horizon, reverb and the scheduler
#        by overriding the command: `php artisan horizon|reverb:start|schedule:work`.
#   web  nginx + brotli serving /public and proxying PHP to the app container.

ARG PHP_VERSION=8.3
ARG NODE_VERSION=22

# ---------------------------------------------------------------- composer deps
FROM composer:2.8 AS vendor
WORKDIR /app
COPY backend/composer.json backend/composer.lock ./
# Extensions are present in the runtime image; the composer image does not need them.
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --no-autoloader \
        --prefer-dist --ignore-platform-reqs
COPY backend/ ./
RUN composer dump-autoload --no-dev --classmap-authoritative --no-scripts \
    && rm -rf tests node_modules

# ---------------------------------------------------------------- front-end build
FROM node:${NODE_VERSION}-alpine AS assets
WORKDIR /app
COPY backend/package.json backend/package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY backend/vite.config.js ./
COPY backend/resources ./resources
COPY backend/public ./public
# Livewire/Filament views reference vendor assets through the Tailwind @source scan.
COPY --from=vendor /app/vendor ./vendor
RUN npm run build \
    && find public/build -type f \( -name '*.js' -o -name '*.css' -o -name '*.svg' -o -name '*.json' \) \
       -exec gzip -9 -k {} \;

# ---------------------------------------------------------------- runtime (php-fpm)
FROM php:${PHP_VERSION}-fpm-bookworm AS app
LABEL org.opencontainers.image.title="aurum-app" \
      org.opencontainers.image.description="Aurum Laravel API, web and workers" \
      org.opencontainers.image.source="https://github.com/aurum-coach/aurum"

COPY --from=mlocati/php-extension-installer:2.7 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_pgsql pgsql redis intl gd zip pcntl bcmath opcache exif sockets \
    && apt-get update \
    && apt-get install -y --no-install-recommends ffmpeg libfcgi-bin postgresql-client ca-certificates tini \
    && rm -rf /var/lib/apt/lists/* /usr/local/bin/install-php-extensions

COPY infra/docker/php/php.ini /usr/local/etc/php/conf.d/zz-aurum.ini
COPY infra/docker/php/php-prod.ini /usr/local/etc/php/conf.d/zz-aurum-prod.ini
COPY infra/docker/php/www.conf /usr/local/etc/php-fpm.d/zz-www.conf
COPY --chmod=0755 infra/docker/production/entrypoint.sh /usr/local/bin/aurum-entrypoint

WORKDIR /var/www/html
COPY --chown=www-data:www-data --from=vendor /app ./
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs storage/app/private storage/app/public bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && rm -f .env

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PHP_FPM_PM_MAX_CHILDREN=24

USER www-data
EXPOSE 9000 8080
HEALTHCHECK --interval=15s --timeout=3s --start-period=20s --retries=3 \
    CMD SCRIPT_NAME=/fpm-ping SCRIPT_FILENAME=/fpm-ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -q pong || exit 1
ENTRYPOINT ["tini", "--", "aurum-entrypoint"]
CMD ["php-fpm", "--nodaemonize"]

# ---------------------------------------------------------------- web (nginx)
FROM alpine:3.20 AS web
RUN apk add --no-cache nginx nginx-mod-http-brotli curl gettext \
    && mkdir -p /run/nginx /var/cache/nginx \
    && ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log
COPY infra/docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY infra/docker/nginx/snippets/ /etc/nginx/snippets/
COPY infra/docker/production/nginx.conf.template /etc/nginx/templates/default.conf.template
COPY --chmod=0755 infra/docker/production/web-entrypoint.sh /usr/local/bin/web-entrypoint
COPY --from=app /var/www/html/public /var/www/html/public
ENV PHP_UPSTREAM=app:9000 \
    REVERB_UPSTREAM=reverb:8080 \
    SERVER_NAME=_ \
    DNS_RESOLVER=127.0.0.11
EXPOSE 80
HEALTHCHECK --interval=15s --timeout=3s CMD curl -fsS http://127.0.0.1/nginx-health || exit 1
ENTRYPOINT ["web-entrypoint"]
CMD ["nginx", "-g", "daemon off;"]
