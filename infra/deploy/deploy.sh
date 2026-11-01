#!/usr/bin/env bash
# Aurum zero-downtime blue/green deploy. Invoked by .github/actions/ssh-deploy:
#   REGISTRY=ghcr.io/<org> TAG=v1.4.0 GHCR_USER=... GHCR_TOKEN=... bash /opt/aurum/deploy/deploy.sh
#
#   1. pull images for TAG
#   2. run migrations once from the NEW image (migrations must be backward compatible:
#      expand → deploy → contract, see docs/runbooks/outage.md#migrations)
#   3. start app+web of the idle colour, wait until /up is healthy through the edge network
#   4. switch Caddy upstream (graceful reload) → traffic moves with zero dropped requests
#   5. terminate Horizon on the old colour, start workers (horizon, scheduler, reverb) on the new
#   6. smoke-test; on failure switch back to the previous colour (automatic rollback)
#   7. stop the old colour's app/web after a drain period
set -Eeuo pipefail

: "${REGISTRY:?}" "${TAG:?}"
BASE=/opt/aurum
DEPLOY_DIR="$BASE/deploy"
SHARED="$BASE/shared"
STATE="$SHARED/active_colour"
UPSTREAM_FILE="$SHARED/active_upstream.caddy"
DRAIN_SECONDS="${DRAIN_SECONDS:-45}"

log() { printf '[deploy %s] %s\n' "$(date -u +%H:%M:%S)" "$*"; }
compose() { COLOUR="$1" REGISTRY="$REGISTRY" TAG="$2" docker compose -p "aurum-$1" -f "$DEPLOY_DIR/compose.prod.yml" "${@:3}"; }

ACTIVE="$(cat "$STATE" 2>/dev/null || echo none)"
if [ "$ACTIVE" = "blue" ]; then IDLE=green; else IDLE=blue; fi
PREV_TAG="$(cat "$SHARED/active_tag" 2>/dev/null || echo "")"
log "active=$ACTIVE ($PREV_TAG) → deploying $TAG to $IDLE"

if [ -n "${GHCR_TOKEN:-}" ]; then
  echo "$GHCR_TOKEN" | docker login ghcr.io -u "${GHCR_USER:-deploy}" --password-stdin >/dev/null
fi

docker network inspect aurum-edge >/dev/null 2>&1 || docker network create aurum-edge >/dev/null
compose "$IDLE" "$TAG" pull app web horizon scheduler reverb

log "running migrations from $TAG"
compose "$IDLE" "$TAG" run --rm --no-deps -e RUN_MIGRATIONS=true app php artisan migrate:status --no-interaction | tail -n 5

rollback_idle() {
  log "deploy failed before traffic switch — removing $IDLE, $ACTIVE keeps serving"
  compose "$IDLE" "$TAG" --profile workers down --remove-orphans || true
}
trap rollback_idle ERR

log "starting $IDLE app+web"
compose "$IDLE" "$TAG" up -d --remove-orphans app web

log "waiting for web-$IDLE to become healthy"
for i in $(seq 1 60); do
  if docker run --rm --network aurum-edge curlimages/curl:8.11.1 -fsS -o /dev/null "http://web-$IDLE/up"; then
    healthy=1; break
  fi
  sleep 2
done
[ "${healthy:-0}" = "1" ] || { log "web-$IDLE never became healthy"; false; }

switch_to() {
  printf 'reverse_proxy web-%s:80 {\n\theader_up X-Request-Start "t=${now}"\n\thealth_uri /up\n\thealth_interval 10s\n}\n' "$1" > "$UPSTREAM_FILE.tmp"
  mv "$UPSTREAM_FILE.tmp" "$UPSTREAM_FILE"
  docker compose -f "$DEPLOY_DIR/edge/compose.edge.yml" exec -T caddy caddy reload --config /etc/caddy/Caddyfile
}

log "switching traffic to $IDLE"
switch_to "$IDLE"
trap - ERR

if [ "$ACTIVE" != "none" ]; then
  log "terminating Horizon on $ACTIVE (finishes running jobs)"
  compose "$ACTIVE" "$PREV_TAG" exec -T horizon php artisan horizon:terminate || true
  compose "$ACTIVE" "$PREV_TAG" --profile workers stop horizon scheduler reverb || true
fi
log "starting workers on $IDLE"
compose "$IDLE" "$TAG" --profile workers up -d horizon scheduler reverb

log "post-switch smoke test"
if ! docker run --rm --network aurum-edge curlimages/curl:8.11.1 -fsS -o /dev/null "http://web-$IDLE/up"; then
  log "smoke test failed — rolling back to $ACTIVE"
  if [ "$ACTIVE" != "none" ]; then
    switch_to "$ACTIVE"
    compose "$IDLE" "$TAG" --profile workers stop horizon scheduler reverb || true
    compose "$ACTIVE" "$PREV_TAG" --profile workers up -d horizon scheduler reverb
  fi
  exit 1
fi

echo "$IDLE" > "$STATE"
echo "$TAG" > "$SHARED/active_tag"
echo "$(date -u +%FT%TZ) $TAG $IDLE" >> "$SHARED/deploy_history"

if [ "$ACTIVE" != "none" ]; then
  log "draining $ACTIVE for ${DRAIN_SECONDS}s, then stopping it (kept on disk for instant rollback)"
  sleep "$DRAIN_SECONDS"
  compose "$ACTIVE" "$PREV_TAG" stop app web || true
fi

compose "$IDLE" "$TAG" exec -T app php artisan optimize --no-interaction >/dev/null || true
docker image prune -af --filter "until=168h" >/dev/null || true
log "✓ $TAG live on $IDLE"
