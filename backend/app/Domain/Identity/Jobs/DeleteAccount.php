<?php

namespace App\Domain\Identity\Jobs;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Erases personal data. Health, nutrition, training logs and photos are deleted;
 * financial records (orders/invoices) are kept for legal retention but de-identified;
 * messages the user sent are replaced by a tombstone; the user row is anonymised.
 */
class DeleteAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public readonly string $userId) {}

    public function handle(): void
    {
        $user = User::withTrashed()->find($this->userId);
        if (! $user) {
            return;
        }

        DB::transaction(function () use ($user) {
            foreach (DB::table('progress_photos')->where('client_id', $user->id)->get() as $p) {
                Storage::disk($p->disk ?: 'local')->delete($p->path);
            }
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $erase = [
                'progress_photos' => 'client_id', 'body_metrics' => 'client_id', 'food_logs' => 'client_id', 'water_logs' => 'client_id',
                'nutrition_targets' => 'client_id', 'habit_assignments' => 'client_id', 'checkin_responses' => 'client_id',
                'checkin_schedules' => 'client_id', 'personal_records' => 'client_id', 'workout_sessions' => 'client_id',
                'program_assignments' => 'client_id', 'health_samples' => 'user_id', 'health_daily' => 'user_id',
                'integrations' => 'user_id', 'devices' => 'user_id', 'ai_settings' => 'user_id', 'exercise_favorites' => 'user_id',
                'food_favorites' => 'user_id', 'challenge_entries' => 'user_id', 'user_badges' => 'user_id', 'reactions' => 'user_id',
                'message_reactions' => 'user_id', 'client_group_members' => 'user_id', 'reviews' => 'client_id',
            ];
            foreach ($erase as $table => $column) {
                DB::table($table)->where($column, $user->id)->delete();
            }
            DB::table('messages')->where('sender_id', $user->id)->update(['body' => null, 'meta' => null, 'deleted_at' => now()]);
            DB::table('posts')->where('author_id', $user->id)->update(['body' => null, 'deleted_at' => now()]);
            DB::table('comments')->where('author_id', $user->id)->update(['body' => '', 'deleted_at' => now()]);
            DB::table('orders')->where('client_id', $user->id)->update(['customer_email' => null, 'customer_name' => null]);
            DB::table('client_profiles')->where('user_id', $user->id)->update(['date_of_birth' => null, 'injuries' => null, 'medical_notes' => null, 'onboarding' => null, 'status' => 'archived', 'deleted_at' => now()]);
            DB::table('tenant_user')->where('user_id', $user->id)->update(['status' => 'archived']);
            DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();

            // Personal (standalone) workspaces are deleted with the user.
            foreach (Tenant::query()->where('type', 'personal')->whereIn('id', DB::table('tenant_user')->where('user_id', $user->id)->pluck('tenant_id'))->get() as $t) {
                $t->delete();
            }

            $user->forceFill([
                'name' => 'Deleted user',
                'email' => 'deleted+'.Str::lower($user->id).'@invalid.aurum',
                'phone' => null, 'password' => null, 'avatar_path' => null, 'apple_id' => null, 'google_id' => null,
                'two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'notification_preferences' => null,
                'current_tenant_id' => null, 'remember_token' => null,
            ])->save();
            $user->delete();
        });

        ActivityLogger::log('account.deleted', null, ['user_id' => $user->id], causerId: $user->id);
    }
}
