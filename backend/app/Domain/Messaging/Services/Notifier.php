<?php

namespace App\Domain\Messaging\Services;

use App\Domain\Identity\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Support\Traits\Localizable;

/**
 * Sends a GenericNotification whose title/body are rendered in the
 * recipient's own locale (not the locale of the request that triggered it).
 */
final class Notifier
{
    use Localizable;

    /**
     * @param  array<string, mixed>  $params  translation replacements
     * @param  array<string, string>  $data  push payload (deep-link ids)
     */
    public function send(User $user, string $category, string $titleKey, string $bodyKey, array $params = [], ?string $link = null, array $data = []): void
    {
        $notification = $this->withLocale($user->preferredLocale(), fn () => new GenericNotification(
            $category,
            (string) __($titleKey, $params),
            (string) __($bodyKey, $params),
            $link,
            array_map('strval', $data),
        ));

        $user->notify($notification->locale($user->preferredLocale()));
    }

    /**
     * Sends pre-rendered text (already localized by the caller).
     *
     * @param  array<string, string>  $data
     */
    public function sendText(User $user, string $category, string $title, string $body, ?string $link = null, array $data = []): void
    {
        $user->notify((new GenericNotification($category, $title, $body, $link, array_map('strval', $data)))->locale($user->preferredLocale()));
    }

    /**
     * Renders a callback in the user's locale.
     *
     * @template T
     *
     * @param  \Closure(): T  $callback
     * @return T
     */
    public function inLocale(User $user, \Closure $callback): mixed
    {
        return $this->withLocale($user->preferredLocale(), $callback);
    }
}
