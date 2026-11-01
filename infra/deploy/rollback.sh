#!/usr/bin/env bash
# Instant rollback to the previous colour (its containers are stopped, not removed).
# Usage on the host: bash /opt/aurum/deploy/rollback.sh
# Database migrations are NOT reverted automatically — see docs/runbooks/outage.md#rollback.
set -Eeuo pipefail
BASE=/opt/aurum; SHARED="$BASE/shared"; DEPLOY_DIR="$BASE/deploy"
ACTIVE="$(cat "$SHARED/active_colour")"
if [ "$ACTIVE" = "blue" ]; then TARGET=green; else TARGET=blue; fi
PREV_LINE="$(grep " $TARGET\$" "$SHARED/deploy_history" | tail -n 1)"
PREV_TAG="$(echo "$PREV_LINE" | awk '{print $2}')"
[ -n "$PREV_TAG" ] || { echo "no previous deploy recorded for $TARGET"; exit 1; }
REGISTRY="${REGISTRY:?set REGISTRY=ghcr.io/<org>}"
compose() { COLOUR="$1" REGISTRY="$REGISTRY" TAG="$2" docker compose -p "aurum-$1" -f "$DEPLOY_DIR/compose.prod.yml" "${@:3}"; }

echo "rolling back $ACTIVE → $TARGET ($PREV_TAG)"
compose "$TARGET" "$PREV_TAG" up -d app web
sleep 5
printf 'reverse_proxy web-%s:80 {\n\thealth_uri /up\n\thealth_interval 10s\n}\n' "$TARGET" > "$SHARED/active_upstream.caddy"
docker compose -f "$DEPLOY_DIR/edge/compose.edge.yml" exec -T caddy caddy reload --config /etc/caddy/Caddyfile
compose "$ACTIVE" "$(cat "$SHARED/active_tag")" exec -T horizon php artisan horizon:terminate || true
compose "$ACTIVE" "$(cat "$SHARED/active_tag")" --profile workers stop horizon scheduler reverb || true
compose "$TARGET" "$PREV_TAG" --profile workers up -d horizon scheduler reverb
echo "$TARGET" > "$SHARED/active_colour"; echo "$PREV_TAG" > "$SHARED/active_tag"
echo "$(date -u +%FT%TZ) $PREV_TAG $TARGET rollback" >> "$SHARED/deploy_history"
echo "✓ rolled back to $PREV_TAG on $TARGET"
