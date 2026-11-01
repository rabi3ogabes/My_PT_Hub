<?php

namespace App\Livewire\App\Settings;

use App\Http\Middleware\SetLocale;
use App\Livewire\App\Concerns\WithTenant;
use App\Livewire\App\Onboarding;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/** Workspace identity, regional defaults and the legal/VAT details printed on invoices. */
#[Layout('layouts.app')]
class Business extends Component
{
    use WithFileUploads, WithTenant;

    public const LEGAL_FIELDS = ['legal_name', 'vat_number', 'registration_number', 'address_line1', 'address_line2', 'city', 'postal_code', 'country', 'invoice_email', 'invoice_footer'];

    public string $name = '';

    public string $brandColor = '#D4AF6A';

    public string $currency = 'USD';

    public string $locale = 'en';

    public string $timezone = 'UTC';

    public string $country = '';

    /** @var TemporaryUploadedFile|null */
    public $logo = null;

    /** @var array<string, string> */
    public array $legal = [];

    public function mount(): void
    {
        $t = $this->tenant();
        $this->name = (string) $t->name;
        $this->brandColor = $t->brand_color ?: '#D4AF6A';
        $this->currency = $t->currency ?: 'USD';
        $this->locale = $t->locale ?: 'en';
        $this->timezone = $t->timezone ?: 'UTC';
        $this->country = (string) $t->country;
        $stored = (array) ($t->legal ?? []);
        foreach (self::LEGAL_FIELDS as $f) {
            $this->legal[$f] = (string) ($stored[$f] ?? '');
        }
    }

    public function save(): void
    {
        abort_unless($this->isOwner(), 403);
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'brandColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'currency' => ['required', Rule::in(Onboarding::CURRENCIES)],
            'locale' => ['required', Rule::in(SetLocale::SUPPORTED)],
            'timezone' => ['required', 'timezone:all'],
            'country' => ['nullable', 'string', 'size:2', 'alpha'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'legal.legal_name' => ['nullable', 'string', 'max:160'],
            'legal.vat_number' => ['nullable', 'string', 'max:40'],
            'legal.registration_number' => ['nullable', 'string', 'max:60'],
            'legal.address_line1' => ['nullable', 'string', 'max:160'],
            'legal.address_line2' => ['nullable', 'string', 'max:160'],
            'legal.city' => ['nullable', 'string', 'max:80'],
            'legal.postal_code' => ['nullable', 'string', 'max:20'],
            'legal.country' => ['nullable', 'string', 'max:80'],
            'legal.invoice_email' => ['nullable', 'email', 'max:190'],
            'legal.invoice_footer' => ['nullable', 'string', 'max:500'],
        ]);
        $tenant = $this->tenant();
        $attributes = [
            'name' => trim($this->name),
            'brand_color' => strtoupper($this->brandColor),
            'currency' => $this->currency,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'country' => $this->country ? strtoupper($this->country) : null,
            'legal' => array_filter(array_map(fn ($v) => trim((string) $v), $this->legal), fn ($v) => $v !== ''),
        ];
        if ($this->logo) {
            $path = $this->logo->store('tenants/'.$tenant->id, 'public');
            if ($tenant->logo_path) {
                Storage::disk('public')->delete($tenant->logo_path);
            }
            $attributes['logo_path'] = $path;
            $this->logo = null;
        }
        $tenant->forceFill($attributes)->save();
        $this->toast(__('web.settings.saved'));
    }

    public function removeLogo(): void
    {
        abort_unless($this->isOwner(), 403);
        $tenant = $this->tenant();
        if ($tenant->logo_path) {
            Storage::disk('public')->delete($tenant->logo_path);
            $tenant->forceFill(['logo_path' => null])->save();
        }
    }

    public function render()
    {
        return view('livewire.app.settings.business', [
            'canEdit' => $this->isOwner(),
            'currencies' => Onboarding::CURRENCIES,
            'colors' => Onboarding::COLORS,
            'timezones' => \DateTimeZone::listIdentifiers(),
            'logoUrl' => $this->tenant()->logo_path ? Storage::disk('public')->url($this->tenant()->logo_path) : null,
            'slug' => $this->tenant()->slug,
        ])->title(__('web.settings.business'));
    }
}
