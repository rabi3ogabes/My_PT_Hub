<?php

namespace App\Domain\Workouts\Jobs;

use App\Domain\Workouts\Models\Exercise;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\ExecutableFinder;
use Throwable;

/**
 * Transcodes an uploaded exercise demo into adaptive HLS (240p/480p/720p),
 * a poster frame and a 3-second looping webp preview. Without an FFmpeg binary
 * the original upload is published as a playable `upload` video.
 */
class ProcessExerciseVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 1200;

    public function __construct(public readonly string $exerciseId) {}

    public function handle(): void
    {
        $exercise = Exercise::withoutGlobalScopes()->find($this->exerciseId);
        if (! $exercise || ! $exercise->video_path) {
            return;
        }
        $diskName = (string) config('workouts.media_disk', 'public');
        $disk = Storage::disk($diskName);

        $ffmpeg = self::ffmpegBinary();
        if ($ffmpeg === null) {
            $exercise->forceFill([
                'video_provider' => 'upload',
                'video_url' => $disk->url($exercise->video_path),
                'video_status' => 'ready',
            ])->save();

            return;
        }

        $exercise->forceFill(['video_status' => 'processing'])->save();
        $work = storage_path('app/tmp/hls-'.$exercise->id.'-'.bin2hex(random_bytes(4)));
        File::ensureDirectoryExists($work.'/out');

        try {
            $source = $work.'/source.'.pathinfo($exercise->video_path, PATHINFO_EXTENSION);
            $stream = $disk->readStream($exercise->video_path);
            if ($stream === null) {
                throw new \RuntimeException('Source video missing.');
            }
            file_put_contents($source, $stream);

            $sizes = $this->transcode($ffmpeg, $source, $work.'/out');

            $prefix = 'exercise-videos/'.$exercise->id.'/hls-'.now()->format('YmdHis');
            foreach (File::allFiles($work.'/out') as $file) {
                $relative = trim(str_replace($work.'/out', '', $file->getPath()), '/');
                $disk->putFileAs($relative === '' ? $prefix : $prefix.'/'.$relative, $file, $file->getFilename());
            }
            $clean = fn (string $p) => (string) preg_replace('~/+~', '/', $p);

            $exercise->forceFill([
                'video_provider' => 'hls',
                'video_url' => $disk->url($clean($prefix.'/master.m3u8')),
                'poster_url' => is_file($work.'/out/poster.jpg') ? $disk->url($clean($prefix.'/poster.jpg')) : null,
                'preview_url' => is_file($work.'/out/preview.webp') ? $disk->url($clean($prefix.'/preview.webp')) : null,
                'video_size_bytes' => $sizes[480] ?? (array_values($sizes)[0] ?? null),
                'video_status' => 'ready',
            ])->save();
        } catch (Throwable $e) {
            Log::warning('Exercise video processing failed', ['exercise_id' => $exercise->id, 'error' => $e->getMessage()]);
            // Keep the original playable so the coach is never left without a demo.
            $exercise->forceFill([
                'video_provider' => 'upload',
                'video_url' => $disk->url($exercise->video_path),
                'video_status' => 'failed',
            ])->save();
        } finally {
            File::deleteDirectory($work);
        }
    }

    public static function ffmpegBinary(): ?string
    {
        $configured = (string) config('workouts.ffmpeg_binary', 'ffmpeg');
        if ($configured === '') {
            return null;
        }
        if (str_contains($configured, '/')) {
            return is_executable($configured) ? $configured : null;
        }

        return (new ExecutableFinder)->find($configured);
    }

    /**
     * Writes master.m3u8, {height}p/index.m3u8 + segments, poster.jpg and preview.webp.
     *
     * @return array<int, int> bytes per rendition height
     */
    private function transcode(string $ffmpeg, string $source, string $out): array
    {
        $timeout = (int) config('workouts.ffmpeg_timeout', 900);
        $renditions = (array) config('workouts.hls_renditions', [240 => 400, 480 => 1000, 720 => 2500]);
        $master = ['#EXTM3U', '#EXT-X-VERSION:3'];
        $sizes = [];

        foreach ($renditions as $height => $kbps) {
            $dir = $out.'/'.$height.'p';
            File::ensureDirectoryExists($dir);
            $result = Process::timeout($timeout)->run([
                $ffmpeg, '-y', '-i', $source,
                // Auto-rotate is applied by default; scale keeps aspect ratio with even width.
                '-vf', 'scale=-2:'.$height,
                '-c:v', 'libx264', '-preset', 'veryfast', '-profile:v', 'main', '-crf', '23',
                '-maxrate', $kbps.'k', '-bufsize', ($kbps * 2).'k', '-g', '48', '-sc_threshold', '0',
                '-c:a', 'aac', '-b:a', '96k', '-ac', '2', '-af', 'loudnorm=I=-16:TP=-1.5:LRA=11',
                '-hls_time', '4', '-hls_playlist_type', 'vod',
                '-hls_segment_filename', $dir.'/seg_%03d.ts',
                $dir.'/index.m3u8',
            ]);
            if (! $result->successful()) {
                throw new \RuntimeException('FFmpeg HLS failed: '.substr($result->errorOutput(), -500));
            }
            $sizes[(int) $height] = array_sum(array_map(fn ($f) => $f->getSize(), File::files($dir)));
            $width = (int) round($height * 16 / 9 / 2) * 2;
            $master[] = '#EXT-X-STREAM-INF:BANDWIDTH='.(($kbps + 96) * 1000).',RESOLUTION='.$width.'x'.$height;
            $master[] = $height.'p/index.m3u8';
        }
        file_put_contents($out.'/master.m3u8', implode("\n", $master)."\n");

        Process::timeout(120)->run([$ffmpeg, '-y', '-ss', '1', '-i', $source, '-frames:v', '1', '-vf', 'scale=-2:720', '-q:v', '3', $out.'/poster.jpg']);
        if (! is_file($out.'/poster.jpg')) {
            Process::timeout(120)->run([$ffmpeg, '-y', '-i', $source, '-frames:v', '1', '-vf', 'scale=-2:720', '-q:v', '3', $out.'/poster.jpg']);
        }
        Process::timeout(180)->run([$ffmpeg, '-y', '-t', '3', '-i', $source, '-vf', 'fps=12,scale=-2:240', '-an', '-loop', '0', '-c:v', 'libwebp', '-quality', '60', $out.'/preview.webp']);

        return $sizes;
    }
}
