<?php

namespace App\Domain\Identity\Jobs;

use App\Domain\Identity\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use ZipArchive;

/**
 * Builds a portable archive of everything the user owns across tenants:
 * profile, memberships, workouts, nutrition, habits, check-ins, metrics,
 * messages they sent, bookings, orders, plus progress photos and avatar.
 */
class ExportUserData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $timeout = 600;

    /** table => column holding the user id */
    public const TABLES = [
        'tenant_user' => 'user_id', 'client_profiles' => 'user_id', 'devices' => 'user_id',
        'workout_sessions' => 'client_id', 'personal_records' => 'client_id', 'program_assignments' => 'client_id',
        'food_logs' => 'client_id', 'water_logs' => 'client_id', 'nutrition_targets' => 'client_id',
        'habit_assignments' => 'client_id', 'checkin_responses' => 'client_id', 'body_metrics' => 'client_id',
        'progress_photos' => 'client_id', 'messages' => 'sender_id', 'bookings' => 'client_id', 'credit_ledger' => 'client_id',
        'orders' => 'client_id', 'client_subscriptions' => 'client_id', 'invoices' => 'client_id', 'contracts' => 'client_id',
        'health_samples' => 'user_id', 'integrations' => 'user_id', 'posts' => 'author_id', 'comments' => 'author_id',
        'challenge_entries' => 'user_id', 'user_badges' => 'user_id', 'reviews' => 'client_id',
    ];

    public function __construct(public readonly string $userId) {}

    public function handle(): void
    {
        $user = User::withTrashed()->findOrFail($this->userId);
        $dir = 'exports/'.$user->id;
        $path = $dir.'/aurum-export-'.now()->format('Ymd-His').'.zip';
        Storage::disk('local')->makeDirectory($dir);
        $full = Storage::disk('local')->path($path);

        $zip = new ZipArchive;
        $zip->open($full, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('profile.json', json_encode($user->makeHidden([])->only(['id', 'name', 'email', 'phone', 'locale', 'timezone', 'units', 'created_at']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        foreach (self::TABLES as $table => $column) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            $rows = DB::table($table)->where($column, $user->id)->get();
            if ($table === 'messages') {
                // bodies are encrypted at rest — decrypt for the owner's export
                $rows = $rows->map(function ($r) {
                    try {
                        $r->body = $r->body ? decrypt($r->body, false) : null;
                    } catch (\Throwable) {
                    }

                    return $r;
                });
            }
            if ($table === 'integrations') {
                $rows = $rows->map(fn ($r) => collect((array) $r)->except(['access_token', 'refresh_token'])->all());
            }
            if ($rows->isNotEmpty()) {
                $zip->addFromString("data/{$table}.json", json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        foreach (DB::table('progress_photos')->where('client_id', $user->id)->get() as $photo) {
            $disk = Storage::disk($photo->disk ?: 'local');
            if ($disk->exists($photo->path)) {
                $zip->addFromString('photos/'.$photo->taken_on.'-'.$photo->pose.'-'.basename($photo->path), $disk->get($photo->path));
            }
        }
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            $zip->addFromString('avatar/'.basename($user->avatar_path), Storage::disk('public')->get($user->avatar_path));
        }
        $zip->close();

        $url = URL::temporarySignedRoute('account.export.download', now()->addDays(3), ['user' => $user->id, 'file' => basename($path)]);
        $user->notify(new GenericNotification('system', __('clients_api.export_ready_title'), __('clients_api.export_ready_body'), $url, action: __('clients_api.export_download')));
    }
}
