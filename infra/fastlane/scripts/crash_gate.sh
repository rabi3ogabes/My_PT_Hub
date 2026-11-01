#!/usr/bin/env bash
# Staged-rollout gate: fails unless the release's crash-free session rate is at or
# above the threshold (default 99.8 %) with enough sessions to be meaningful.
# Source: Sentry Release Health (sessions API). Crashlytics stays the triage tool;
# Sentry gives a scriptable number for CI.
#
#   SENTRY_AUTH_TOKEN=... SENTRY_ORG=aurum SENTRY_PROJECT=aurum-client \
#   RELEASE=coach.aurum.client@1.4.0+1234 bash infra/fastlane/scripts/crash_gate.sh
set -euo pipefail

: "${SENTRY_AUTH_TOKEN:?}" "${SENTRY_ORG:?}" "${SENTRY_PROJECT:?}" "${RELEASE:?}"
THRESHOLD="${CRASH_FREE_THRESHOLD:-99.8}"
MIN_SESSIONS="${MIN_SESSIONS:-1000}"
SENTRY_URL="${SENTRY_URL:-https://sentry.io}"

project_id=$(curl -fsS -H "Authorization: Bearer $SENTRY_AUTH_TOKEN" \
  "$SENTRY_URL/api/0/projects/$SENTRY_ORG/$SENTRY_PROJECT/" | python3 -c 'import json,sys; print(json.load(sys.stdin)["id"])')

resp=$(curl -fsS -G -H "Authorization: Bearer $SENTRY_AUTH_TOKEN" \
  "$SENTRY_URL/api/0/organizations/$SENTRY_ORG/sessions/" \
  --data-urlencode "project=$project_id" \
  --data-urlencode "field=crash_free_rate(session)" \
  --data-urlencode "field=sum(session)" \
  --data-urlencode "query=release:$RELEASE" \
  --data-urlencode "statsPeriod=${STATS_PERIOD:-48h}" \
  --data-urlencode "interval=1d")

read -r rate sessions < <(python3 -c '
import json, sys
d = json.loads(sys.argv[1])
totals = d["groups"][0]["totals"] if d.get("groups") else {}
rate = totals.get("crash_free_rate(session)")
print((rate * 100) if rate is not None else -1, int(totals.get("sum(session)", 0)))
' "$resp")

echo "release=$RELEASE crash_free=${rate}% sessions=${sessions} threshold=${THRESHOLD}% min_sessions=${MIN_SESSIONS}"
python3 - "$rate" "$sessions" "$THRESHOLD" "$MIN_SESSIONS" <<'PY'
import sys
rate, sessions, threshold, minimum = float(sys.argv[1]), int(sys.argv[2]), float(sys.argv[3]), int(sys.argv[4])
if sessions < minimum:
    sys.exit(f"Not enough sessions yet ({sessions} < {minimum}) — keep the current rollout and retry later.")
if rate < threshold:
    sys.exit(f"Crash-free sessions {rate:.3f}% < {threshold}% — rollout halted.")
print("Gate passed.")
PY
