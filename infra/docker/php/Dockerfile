# syntax=docker/dockerfile:1.7
# Aurum — local development PHP-FPM image (used by docker-compose.yml for
# app, reverb, horizon and scheduler). Production uses infra/docker/production/Dockerfile.
FROM php:8.3-fpm-bookworm

ARG UID=1000
ARG GID=1000

# install-php-extensions resolves build deps and cleans up after itself.
COPY --from=mlocati/php-extension-installer:2.7 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
        pdo_pgsql pgsql redis intl gd zip pcntl bcmath opcache exif sockets \
    && apt-get update \
    && apt-get install -y --no-install-recommends ffmpeg git unzip postgresql-client curl ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Node 22 LTS for `npm run dev|build` inside the container.
COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

COPY php/php.ini /usr/local/etc/php/conf.d/zz-aurum.ini
COPY php/php-dev.ini /usr/local/etc/php/conf.d/zz-aurum-dev.ini
COPY php/www.conf /usr/local/etc/php-fpm.d/zz-www.conf

# Run as the host user's UID/GID so bind-mounted files stay editable.
RUN groupadd --gid ${GID} aurum \
    && useradd --uid ${UID} --gid ${GID} --create-home --shell /bin/bash aurum \
    && mkdir -p /var/www/html && chown aurum:aurum /var/www/html

WORKDIR /var/www/html
USER aurum

EXPOSE 9000
CMD ["php-fpm", "--nodaemonize"]
