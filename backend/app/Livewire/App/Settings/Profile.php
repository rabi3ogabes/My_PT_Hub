<?php

namespace App\Livewire\App\Settings;

use App\Domain\Identity\Services\AuthService;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\CoachProfile;
use App\Http\Middleware\SetLocale;
use App\Livewire\App\Concerns\WithTenant;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/** Personal settings: identity, preferences, password, 2FA and the public coach profile. */
#[Layout('layouts.app')]
class Profile extends Component
{
    use WithFileUploads, WithTenant;

    public string $name = '';

    public string $locale = 'en';

    public string $timezone = 'UTC';

    public string $units = 'metric';

    /** @var TemporaryUploadedFile|null */
    public $avatar = null;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $twoFactorSecret = null;

    public ?string $twoFactorQr = null;

    public string $twoFactorCode = '';

    /** @var list<string> */
    public array $recoveryCodes = [];

    public bool $confirmingDisable = false;

    public string $disablePassword = '';

    // Public coach profile
    public string $headline = '';

    public string $city = '';

    public string $bio = '';

    public bool $offersOnline = true;

    public bool $offersInPerson = false;

    public bool $published = false;

    public function mount(): void
    {
        $u = $this->user();
        $this->name = $u->name;
        $this->locale = $u->locale ?: 'en';
        $this->timezone = $u->timezone ?: 'UTC';
        $this->units = $u->units ?: 'metric';
        if ($profile = $this->coachProfile()) {
            $this->headline = (string) $profile->headline;
            $this->city = (string) $profile->city;
            $this->bio = (string) ($profile->translateRaw('bio') ?? '');
            $this->offersOnline = (bool) $profile->offers_online;
            $this->offersInPerson = (bool) $profile->offers_in_person;
            $this->published = (bool) $profile->is_published;
        }
    }

    private function coachProfile(): ?CoachProfile
    {
        return CoachProfile::query()->where('user_id', $this->user()->id)->first();
    }

    public function saveProfile(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'locale' => ['required', Rule::in(SetLocale::SUPPORTED)],
            'timezone' => ['required', 'timezone:all'],
            'units' => ['required', Rule::in(['metric', 'imperial'])],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:3072'],
        ]);
        $user = $this->user();
        $attributes = ['name' => trim($data['name']), 'locale' => $data['locale'], 'timezone' => $data['timezone'], 'units' => $data['units']];
        if ($this->avatar) {
            $path = $this->avatar->store('avatars/'.$user->id, 'public');
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $attributes['avatar_path'] = $path;
            $this->avatar = null;
        }
        $user->forceFill($attributes)->save();
        session()->put('locale', $data['locale']);
        $this->toast(__('web.settings.saved'));
    }

    public function savePassword(): void
    {
        $user = $this->user();
        $rules = ['password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()]];
        if ($user->password) {
            $rules['current_password'] = ['required', 'string'];
        }
        $this->validate($rules);
        if ($user->password && ! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', __('web.settings.wrong_password'));

            return;
        }
        $user->forceFill(['password' => $this->password])->save();
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->toast(__('web.settings.password_updated'));
    }

    public function beginTwoFactor(AuthService $auth): void
    {
        $result = $auth->beginTwoFactor($this->user());
        $this->twoFactorSecret = $result['secret'];
        $this->twoFactorQr = (new QRCode(new QROptions(['outputBase64' => true, 'addQuietzone' => true])))->render($result['otpauth_url']);
        $this->recoveryCodes = [];
    }

    public function confirmTwoFactor(AuthService $auth): void
    {
        $this->validate(['twoFactorCode' => ['required', 'digits:6']]);
        try {
            $this->recoveryCodes = $auth->confirmTwoFactor($this->user(), $this->twoFactorCode);
        } catch (ApiException $e) {
            $this->addError('twoFactorCode', $e->getMessage());

            return;
        }
        $this->reset('twoFactorSecret', 'twoFactorQr', 'twoFactorCode');
        $this->toast(__('web.settings.two_factor_enabled'));
    }

    public function cancelTwoFactor(AuthService $auth): void
    {
        if (! $this->user()->hasTwoFactorEnabled()) {
            $auth->disableTwoFactor($this->user());
        }
        $this->reset('twoFactorSecret', 'twoFactorQr', 'twoFactorCode');
    }

    public function disableTwoFactor(AuthService $auth): void
    {
        $user = $this->user();
        if ($user->password && ! Hash::check($this->disablePassword, $user->password)) {
            $this->addError('disablePassword', __('web.settings.wrong_password'));

            return;
        }
        $auth->disableTwoFactor($user);
        $this->reset('confirmingDisable', 'disablePassword', 'recoveryCodes');
        $this->toast(__('web.settings.two_factor_disabled'));
    }

    public function savePublicProfile(): void
    {
        $this->validate([
            'headline' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:80'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'offersOnline' => ['boolean'],
            'offersInPerson' => ['boolean'],
            'published' => ['boolean'],
        ]);
        $profile = $this->coachProfile() ?? new CoachProfile([
            'user_id' => $this->user()->id,
            'slug' => $this->uniqueSlug($this->user()->name),
            'languages' => [$this->tenant()->locale ?: 'en'],
        ]);
        $profile->headline = $this->headline ?: null;
        $profile->city = $this->city ?: null;
        $profile->setTranslation('bio', app()->getLocale(), $this->bio);
        $profile->offers_online = $this->offersOnline;
        $profile->offers_in_person = $this->offersInPerson;
        $profile->is_published = $this->published;
        $profile->save();
        $this->toast($this->published ? __('web.settings.profile_published') : __('web.settings.saved'));
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'coach';
        $slug = $base;
        $i = 1;
        while (CoachProfile::withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    public function render()
    {
        $user = $this->user();

        return view('livewire.app.settings.profile', [
            'timezones' => \DateTimeZone::listIdentifiers(),
            'twoFactorOn' => $user->hasTwoFactorEnabled(),
            'hasPassword' => (bool) $user->password,
            'avatarUrl' => $user->avatar_path ? Storage::disk('public')->url($user->avatar_path) : null,
            'profileSlug' => $this->coachProfile()?->slug,
        ])->title(__('web.settings.profile'));
    }
}
