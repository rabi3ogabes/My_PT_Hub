<?php

namespace App\Livewire\App;

use Livewire\Attributes\On;
use Livewire\Component;

/** Top-bar notifications (database channel) with mark-all-read. */
class NotificationsBell extends Component
{
    public function markAllRead(): void
    {
        auth()->user()?->unreadNotifications()->update(['read_at' => now()]);
    }

    public function open(string $id): mixed
    {
        $notification = auth()->user()?->notifications()->whereKey($id)->first();
        if (! $notification) {
            return null;
        }
        $notification->markAsRead();
        $link = $notification->data['link'] ?? null;

        return is_string($link) && str_starts_with($link, '/') ? $this->redirect($link) : null;
    }

    #[On('au-notification')]
    public function refreshList(): void
    {
        // Re-render on realtime push.
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.app.notifications-bell', [
            'items' => $user ? $user->notifications()->latest()->limit(8)->get() : collect(),
            'unread' => $user ? $user->unreadNotifications()->count() : 0,
        ]);
    }
}
