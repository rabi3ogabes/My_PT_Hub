<?php

namespace App\Notifications;

use App\Domain\Identity\Models\User;
use App\Notifications\Channels\FcmChannel;
use App\Notifications\Channels\FcmMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Base class for all product notifications. Channels are chosen from the
 * user's per-category preferences; push respects quiet hours.
 * Subclasses implement category(), title(), body() and optionally deepLink()/actionLabel().
 */
abstract class AppNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** Categories: messages, workouts, nutrition, habits, checkins, bookings, payments, community, billing, system, marketing */
    abstract public function category(): string;

    abstract public function title(object $notifiable): string;

    abstract public function body(object $notifiable): string;

    public function deepLink(): ?string
    {
        return null;
    }

    public function actionLabel(object $notifiable): ?string
    {
        return null;
    }

    /** @return array<string, string> */
    public function pushData(): array
    {
        return [];
    }

    /** Categories that always email regardless of preferences (legal/billing trust). */
    protected function critical(): bool
    {
        return in_array($this->category(), ['billing', 'system'], true);
    }

    /** @return list<string|class-string> */
    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof User) {
            return ['mail'];
        }
        $prefs = $notifiable->notification_preferences[$this->category()] ?? null;
        $channels = ['database'];
        if ($this->critical() || ($prefs['email'] ?? $this->defaultEmail())) {
            $channels[] = 'mail';
        }
        if (($prefs['push'] ?? true) && ! $this->inQuietHours($notifiable)) {
            $channels[] = FcmChannel::class;
        }

        return $channels;
    }

    protected function defaultEmail(): bool
    {
        return in_array($this->category(), ['billing', 'payments', 'bookings', 'checkins', 'system'], true);
    }

    protected function inQuietHours(User $user): bool
    {
        $q = $user->quiet_hours;
        if (empty($q['enabled']) || empty($q['from']) || empty($q['to']) || in_array($this->category(), ['billing', 'system'], true)) {
            return false;
        }
        $now = now($user->timezone ?: 'UTC')->format('H:i');

        return $q['from'] <= $q['to'] ? ($now >= $q['from'] && $now < $q['to']) : ($now >= $q['from'] || $now < $q['to']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title($notifiable))
            ->greeting(__('notifications.greeting', ['name' => $notifiable instanceof User ? $notifiable->firstName() : '']))
            ->line($this->body($notifiable));
        if ($label = $this->actionLabel($notifiable)) {
            $mail->action($label, $this->webUrl());
        }

        return $mail->salutation(__('notifications.salutation', ['brand' => config('brand.name')]));
    }

    protected function webUrl(): string
    {
        return url($this->deepLink() ?? '/');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category(),
            'title' => $this->title($notifiable),
            'body' => $this->body($notifiable),
            'link' => $this->deepLink(),
            'data' => $this->pushData(),
        ];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        return new FcmMessage(
            title: $this->title($notifiable),
            body: $this->body($notifiable),
            data: array_merge($this->pushData(), ['category' => $this->category(), 'link' => (string) $this->deepLink()]),
            threadId: $this->pushData()['conversation_id'] ?? $this->category(),
        );
    }
}
