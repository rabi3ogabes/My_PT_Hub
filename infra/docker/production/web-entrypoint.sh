#!/bin/sh
# Render the nginx server block from environment variables, then start nginx.
set -eu
envsubst '${PHP_UPSTREAM} ${REVERB_UPSTREAM} ${SERVER_NAME} ${DNS_RESOLVER}' \
    < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf
nginx -t -q
exec "$@"
