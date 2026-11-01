# syntax=docker/dockerfile:1.7
# Aurum — nginx with gzip + brotli (Alpine's nginx-mod-http-brotli dynamic module).
# Dev: serves the bind-mounted backend/public. Production: the `web` target in
# infra/docker/production/Dockerfile reuses these configs and bakes in /public.
FROM alpine:3.20

RUN apk add --no-cache nginx nginx-mod-http-brotli curl \
    && mkdir -p /run/nginx /var/cache/nginx \
    && ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

COPY nginx.conf /etc/nginx/nginx.conf
COPY snippets/ /etc/nginx/snippets/
COPY default.conf /etc/nginx/http.d/default.conf

EXPOSE 80
HEALTHCHECK --interval=15s --timeout=3s CMD curl -fsS http://127.0.0.1/nginx-health || exit 1
CMD ["nginx", "-g", "daemon off;"]
