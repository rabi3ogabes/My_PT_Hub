<?php

namespace App\Domain\Payments\Support;

use App\Domain\Identity\Models\User;
use App\Domain\Tenancy\Models\Tenant;
use App\Notifications\GenericNotification;

/** Localised payment notifications (rendered in the recipient's language). */
final class Notify
{
    /** @param array<string, mixed> $params */
    public static function user(?User $user, string $key, array $params = [], ?string $link = null): void
    {
        if (! $user) {
            return;
        }
        $locale = $user->preferredLocale();
        $user->notify(new GenericNotification(
            'payments',
            __("payments.notify.{$key}.title", $params, $locale),
            __("payments.notify.{$key}.body", $params, $locale),
            $link,
        ));
    }

    /** @param array<string, mixed> $params */
    public static function staff(Tenant $tenant, string $key, array $params = [], ?string $link = null, ?string $coachId = null): void
    {
        $users = $tenant->users()->wherePivotIn('role', ['owner'])->wherePivot('status', 'active')->get();
        if ($coachId && ! $users->contains('id', $coachId) && ($coach = User::find($coachId))) {
            $users->push($coach);
        }
        foreach ($users as $user) {
            self::user($user, $key, $params, $link);
        }
    }
}
