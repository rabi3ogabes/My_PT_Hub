# 0008 — Self-hosted FFmpeg → HLS video pipeline behind a driver

- Status: Accepted · Date: 2026-01-09

## Context
Custom exercise videos "look bad", fail to play and burn mobile data in competitor apps. Coaches
upload from phones (portrait, variable codecs, rotation metadata). Videos must become adaptive
streams within 2 minutes; storage counts against plan limits.

## Decision
- Direct-to-storage resumable uploads (S3 multipart, presigned) into `aurum-private/uploads/raw/`.
- `TranscodeVideo` job on the `media` queue: ffprobe → FFmpeg (auto-rotate, loudnorm,
  H.264 ladder 240/480/720/1080p capped at source, 4 s HLS segments, master playlist) + poster +
  3 s WebP preview → `aurum-media` behind the CDN. FFmpeg ships in the app image.
- `VideoDriver` abstraction: `ffmpeg` default; `mux` / `cloudflare_stream` drivers for tenants or
  regions where managed encoding is cheaper or faster.
- YouTube/Vimeo URLs stored as embeds (no downloading third-party content).

## Alternatives considered
- **Managed only (Mux / Cloudflare Stream)**: excellent quality, but per-minute cost scales with
  every coach's library; vendor lock-in for a core feature.
- **Progressive MP4 only**: simpler, but no adaptive bitrate → buffering and data burn.

## Consequences
- Media workers need CPU headroom; Horizon `media` queue runs on separate worker containers with
  concurrency limits so transcodes never delay payments or notifications.
- Raw uploads expire after 7 days (bucket lifecycle) once renditions exist.
- Signed URLs for private videos (form checks, attachments); public CDN for exercise library.
