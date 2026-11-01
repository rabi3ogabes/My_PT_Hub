<?php

namespace App\Livewire\App\Settings;

use App\Livewire\App\Concerns\WithTenant;
use Livewire\Attributes\Layout;
use Livewire\Component;

/** Per-category channel preferences (push / email) + quiet hours, read by AppNotification. */
#[Layout('layouts.app')]
class Notifications extends Component
{
    use WithTenant;

    public const CATEGORIES = ['messages', 'checkins', 'workouts', 'nutrition', 'habits', 'bookings', 'payments', 'community', 'billing', 'system', 'marketing'];

    /** Always emailed regardless of preference (billing trust, security). */
    public const CRITICAL = ['billing', 'system'];

    public const DEFAULT_EMAIL = ['billing', 'payments', 'bookings', 'checkins', 'system'];

    /** @var array<string, array{push: bool, email: bool}> */
    public array $prefs = [];

    public bool $quietEnabled = false;

    public string $quietFrom = '22:00';

    public string $quietTo = '07:00';

    public function mount(): void
    {
        $stored = (array) ($this->user()->notification_preferences ?? []);
        foreach (self::CATEGORIES as $c) {
            $this->prefs[$c] = [
                'push' => (bool) ($stored[$c]['push'] ?? ($c !== 'marketing')),
                'email' => in_array($c, self::CRITICAL, true) ? true : (bool) ($stored[$c]['email'] ?? in_array($c, self::DEFAULT_EMAIL, true)),
            ];
        }
        $q = (array) ($this->user()->quiet_hours ?? []);
        $this->quietEnabled = (bool) ($q['enabled'] ?? false);
        $this->quietFrom = (string) ($q['from'] ?? '22:00');
        $this->quietTo = (string) ($q['to'] ?? '07:00');
    }

    public function save(): void
    {
        $this->validate([
            'prefs' => ['array'],
            'prefs.*.push' => ['boolean'],
            'prefs.*.email' => ['boolean'],
            'quietEnabled' => ['boolean'],
            'quietFrom' => ['required', 'date_format:H:i'],
            'quietTo' => ['required', 'date_format:H:i'],
        ]);
        $prefs = [];
        foreach (self::CATEGORIES as $c) {
            $prefs[$c] = [
                'push' => (bool) ($this->prefs[$c]['push'] ?? true),
                'email' => in_array($c, self::CRITICAL, true) ? true : (bool) ($this->prefs[$c]['email'] ?? false),
            ];
        }
        $this->user()->forceFill([
            'notification_preferences' => $prefs,
            'quiet_hours' => ['enabled' => $this->quietEnabled, 'from' => $this->quietFrom, 'to' => $this->quietTo],
        ])->save();
        $this->toast(__('web.settings.saved'));
    }

    public function render()
    {
        return view('livewire.app.settings.notifications', [
            'categories' => self::CATEGORIES,
            'critical' => self::CRITICAL,
        ])->title(__('web.settings.notifications'));
    }
}
