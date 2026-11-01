<?php

namespace App\Filament\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Runtime checks for the admin System Health page.
 * Each check: ['key', 'label', 'status' => ok|warning|failed, 'value', 'detail'].
 */
final class SystemHealth
{
    public const HEARTBEAT_KEY = 'admin:scheduler:last_run';

    public const QUEUES = ['default', 'high'];

    /** @return list<array{key: string, label: string, status: string, value: string, detail: ?string}> */
    public function all(): array
    {
        return [
            $this->database(),
            $this->cache(),
            ...$this->queues(),
            $this->failedJobs(),
            $this->scheduler(),
            $this->reverb(),
            ...$this->disks(),
        ];
    }

    /** @return array{key: string, label: string, status: string, value: string, detail: ?string} */
    private static function check(string $key, string $label, string $status, string $value, ?string $detail = null): array
    {
        return compact('key', 'label', 'status', 'value', 'detail');
    }

    private static function ms(float $start): string
    {
        return number_format((microtime(true) - $start) * 1000, 1).' ms';
    }

    public function database(): array
    {
        $start = microtime(true);
        try {
            DB::select('select 1');

            return self::check('database', __('Database'), 'ok', self::ms($start), DB::connection()->getDriverName());
        } catch (\Throwable $e) {
            return self::check('database', __('Database'), 'failed', __('Unreachable'), $e->getMessage());
        }
    }

    public function cache(): array
    {
        $start = microtime(true);
        try {
            $key = 'admin:health:'.Str::random(8);
            Cache::put($key, 'pong', 10);
            $ok = Cache::get($key) === 'pong';
            Cache::forget($key);

            return self::check('cache', __('Cache'), $ok ? 'ok' : 'failed', $ok ? self::ms($start) : __('Read-back mismatch'), config('cache.default'));
        } catch (\Throwable $e) {
            return self::check('cache', __('Cache'), 'failed', __('Unreachable'), $e->getMessage());
        }
    }

    /** @return list<array{key: string, label: string, status: string, value: string, detail: ?string}> */
    public function queues(): array
    {
        $connection = (string) config('queue.default');

        return array_map(function (string $queue) use ($connection): array {
            try {
                $size = Queue::connection($connection)->size($queue);
                $status = $size > 1000 ? 'failed' : ($size > 100 ? 'warning' : 'ok');

                return self::check("queue_{$queue}", __('Queue “:queue”', ['queue' => $queue]), $status, __(':count pending', ['count' => number_format($size)]), $connection);
            } catch (\Throwable $e) {
                return self::check("queue_{$queue}", __('Queue “:queue”', ['queue' => $queue]), 'failed', __('Unavailable'), $e->getMessage());
            }
        }, self::QUEUES);
    }

    public function failedJobs(): array
    {
        try {
            $table = DB::table(config('queue.failed.table', 'failed_jobs'));
            $count = (clone $table)->count();
            $latest = (clone $table)->orderByDesc('failed_at')->first(['queue', 'payload', 'exception', 'failed_at']);
            $detail = null;
            if ($latest) {
                $payload = json_decode((string) $latest->payload, true);
                $detail = sprintf('%s · %s · %s',
                    $payload['displayName'] ?? __('Unknown job'),
                    Carbon::parse($latest->failed_at)->diffForHumans(),
                    Str::limit(strtok((string) $latest->exception, "\n") ?: '', 140));
            }

            return self::check('failed_jobs', __('Failed jobs'), $count > 0 ? 'warning' : 'ok', number_format($count), $detail);
        } catch (\Throwable $e) {
            return self::check('failed_jobs', __('Failed jobs'), 'failed', __('Unavailable'), $e->getMessage());
        }
    }

    public function scheduler(): array
    {
        $last = Cache::get(self::HEARTBEAT_KEY);
        if (! $last) {
            return self::check('scheduler', __('Scheduler'), 'failed', __('Never ran'), __('Run `php artisan schedule:work` or add the cron entry for schedule:run.'));
        }
        $at = Carbon::parse($last);
        $status = $at->lt(now()->subMinutes(10)) ? 'failed' : ($at->lt(now()->subMinutes(3)) ? 'warning' : 'ok');

        return self::check('scheduler', __('Scheduler'), $status, $at->diffForHumans(), $at->toDayDateTimeString().' UTC');
    }

    public function reverb(): array
    {
        $app = config('reverb.apps.apps.0', []);
        $missing = collect(['key', 'secret', 'app_id'])->filter(fn (string $k): bool => blank($app[$k] ?? null))->values()->all();
        $host = $app['options']['host'] ?? null;
        $broadcaster = (string) config('broadcasting.default');

        if ($missing !== []) {
            return self::check('reverb', __('Reverb'), 'failed', __('Not configured'), __('Missing: :keys', ['keys' => implode(', ', $missing)]));
        }

        return self::check('reverb', __('Reverb'), $host && $broadcaster === 'reverb' ? 'ok' : 'warning',
            $host ? (string) $host : __('No host'),
            __('Broadcaster: :driver', ['driver' => $broadcaster]));
    }

    /** @return list<array{key: string, label: string, status: string, value: string, detail: ?string}> */
    public function disks(): array
    {
        $disks = collect([config('filesystems.default'), 'public', config('workouts.media_disk')])
            ->filter(fn ($d): bool => is_string($d) && config("filesystems.disks.{$d}") !== null)
            ->unique()->values();

        return $disks->map(function (string $disk): array {
            $path = '.health/'.Str::ulid().'.txt';
            try {
                $fs = Storage::disk($disk);
                $ok = $fs->put($path, 'ok') && $fs->get($path) === 'ok';
                $fs->delete($path);

                return self::check("disk_{$disk}", __('Disk “:disk”', ['disk' => $disk]), $ok ? 'ok' : 'failed', $ok ? __('Writable') : __('Not writable'), (string) config("filesystems.disks.{$disk}.driver"));
            } catch (\Throwable $e) {
                return self::check("disk_{$disk}", __('Disk “:disk”', ['disk' => $disk]), 'failed', __('Not writable'), $e->getMessage());
            }
        })->all();
    }
}
