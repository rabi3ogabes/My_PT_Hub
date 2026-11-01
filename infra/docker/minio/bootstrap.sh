#!/bin/sh
# Creates Aurum's local buckets (mirrors production layout):
#   aurum-media    public-read  — exercise posters, HLS renditions, recipe/coach images (served via CDN in prod)
#   aurum-private  private      — progress photos, voice notes, attachments, receipts, contracts (signed URLs only)
#   aurum-backups  private      — spatie-style database/media backups (restore drill target)
set -eu

mc alias set local http://minio:9000 "$MINIO_ROOT_USER" "$MINIO_ROOT_PASSWORD" >/dev/null

for bucket in aurum-media aurum-private aurum-backups; do
    mc mb --ignore-existing "local/$bucket"
done

mc anonymous set download local/aurum-media
mc anonymous set none local/aurum-private
mc anonymous set none local/aurum-backups

# Keep raw uploads only 7 days once transcoded renditions exist; backups 35 days.
mc ilm rule add --prefix "uploads/raw/" --expire-days 7 local/aurum-private >/dev/null 2>&1 || true
mc ilm rule add --expire-days 35 local/aurum-backups >/dev/null 2>&1 || true

echo "MinIO buckets ready: aurum-media (public-read), aurum-private, aurum-backups"
