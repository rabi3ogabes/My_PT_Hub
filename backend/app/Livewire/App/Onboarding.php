<?php

namespace App\Livewire\App;

use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Services\InvitationService;
use App\Http\Middleware\SetLocale;
use App\Livewire\App\Concerns\WithTenant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/** First-run wizard: business → brand → region → invite the first client. */
#[Layout('layouts.app')]
class Onboarding extends Component
{
    use WithFileUploads, WithTenant;

    public const CURRENCIES = ['USD', 'QAR', 'SAR', 'AED', 'KWD', 'BHD', 'OMR', 'EGP', 'MAD', 'EUR', 'GBP'];

    public const COLORS = ['#D4AF6A', '#3DBE8B', '#5B8DEF', '#E76F6F', '#B07CF2', '#F2B544', '#2DD4BF', '#F5F3EE'];

    public int $step = 1;

    public string $name = '';

    /** @var TemporaryUploadedFile|null */
    public $logo = null;

    public string $brandColor = '#D4AF6A';

    public string $currency = 'USD';

    public string $locale = 'en';

    public string $timezone = 'UTC';

    public string $inviteEmail = '';

    public bool $invited = false;

    public function mount(): void
    {
        abort_unless($this->isOwner(), 403);
        $t = $this->tenant();
        $this->name = (string) $t->name;
        $this->brandColor = $t->brand_color ?: '#D4AF6A';
        $this->currency = $t->currency ?: 'USD';
        $this->locale = $t->locale ?: app()->getLocale();
        $this->timezone = $t->timezone ?: ($this->user()->timezone ?: 'UTC');
        $this->step = $t->onboarded_at ? 4 : 1;
    }

    /** @return array<int, array<string, mixed>> */
    protected function stepRules(int $step): array
    {
        return match ($step) {
            1 => ['name' => ['required', 'string', 'min:2', 'max:120'], 'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048']],
            2 => ['brandColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/']],
            3 => [
                'currency' => ['required', Rule::in(self::CURRENCIES)],
                'locale' => ['required', Rule::in(SetLocale::SUPPORTED)],
                'timezone' => ['required', 'timezone:all'],
            ],
            default => [],
        };
    }

    public function next(): void
    {
        $this->validate($this->stepRules($this->step));
        if ($this->step < 3) {
            $this->step++;

            return;
        }
        $this->finish();
    }

    public function back(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function finish(): void
    {
        $this->validate(array_merge($this->stepRules(1), $this->stepRules(2), $this->stepRules(3)));
        $tenant = $this->tenant();
        $attributes = [
            'name' => trim($this->name),
            'brand_color' => strtoupper($this->brandColor),
            'currency' => $this->currency,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'onboarded_at' => $tenant->onboarded_at ?? now(),
        ];
        if ($this->logo) {
            $path = $this->logo->store('tenants/'.$tenant->id, 'public');
            if ($tenant->logo_path && $tenant->logo_path !== $path) {
                Storage::disk('public')->delete($tenant->logo_path);
            }
            $attributes['logo_path'] = $path;
            $this->logo = null;
        }
        $tenant->forceFill($attributes)->save();
        $this->step = 4;
    }

    public function invite(InvitationService $invitations): void
    {
        $this->validate(['inviteEmail' => ['required', 'email:rfc', 'max:190']]);
        try {
            $invitations->create($this->tenant(), $this->user(), ['email' => $this->inviteEmail, 'role' => 'client']);
        } catch (ApiException $e) {
            $this->addError('inviteEmail', $e->getMessage());

            return;
        }
        $this->invited = true;
        $this->toast(__('web.dashboard.invite_sent'));
    }

    public function render()
    {
        return view('livewire.app.onboarding', [
            'currencies' => self::CURRENCIES,
            'colors' => self::COLORS,
            'timezones' => \DateTimeZone::listIdentifiers(),
            'logoUrl' => $this->tenant()->logo_path ? Storage::disk('public')->url($this->tenant()->logo_path) : null,
        ])->title(__('web.onboarding.title'));
    }
}
