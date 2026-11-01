<?php

return [
    // Disk for exercise demo videos (originals + HLS renditions). Use an S3/CDN disk in production.
    'media_disk' => env('WORKOUT_MEDIA_DISK', 'public'),

    // FFmpeg binary for the HLS pipeline; when it is not found the original upload is served as-is.
    'ffmpeg_binary' => env('FFMPEG_BINARY', 'ffmpeg'),
    'ffmpeg_timeout' => (int) env('FFMPEG_TIMEOUT', 900),

    // HLS renditions: height => video bitrate (kbit/s).
    'hls_renditions' => [240 => 400, 480 => 1000, 720 => 2500],

    // Upload limit in kilobytes (mp4/mov).
    'max_video_kb' => (int) env('WORKOUT_MAX_VIDEO_KB', 512000),

    // How far back the first /sync/pull (without `since`) reaches.
    'sync_initial_days' => 90,
];
