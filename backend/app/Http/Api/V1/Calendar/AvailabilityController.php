<?php

namespace App\Http\Api\V1\Calendar;

use App\Domain\Calendar\Models\AvailabilityRule;
use App\Domain\Calendar\Models\BookingSetting;
use App\Domain\Calendar\Models\Location;
use App\Domain\Calendar\Services\AvailabilityService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Calendar\Concerns\ResolvesMembers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AvailabilityController extends ApiController
{
    use ResolvesMembers;

    public function __construct(private readonly AvailabilityService $availability) {}

    public function show(Request $request): JsonResponse
    {
        $data = $request->validate(['coach_id' => ['nullable', 'string', 'size:26']]);
        $coach = $this->staffMember($data['coach_id'] ?? $this->user()->id);

        return $this->ok($this->present($coach->id, $this->availability->timezoneFor($coach, $this->tenant())));
    }

    /** Replaces the coach's weekly hours, date overrides/holidays and booking settings. */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'coach_id' => ['nullable', 'string', 'size:26'],
            'timezone' => ['required', 'timezone:all'],
            'weekly' => ['nullable', 'array', 'max:70'],
            'weekly.*.weekday' => ['required', 'integer', 'between:1,7'],
            'weekly.*.starts_at' => ['required', 'date_format:H:i'],
            'weekly.*.ends_at' => ['required', 'regex:/^(([01]\d|2[0-3]):[0-5]\d|24:00)$/'],
            'overrides' => ['nullable', 'array', 'max:400'],
            'overrides.*.date' => ['required', 'date_format:Y-m-d'],
            'overrides.*.starts_at' => ['nullable', 'date_format:H:i'],
            'overrides.*.ends_at' => ['nullable', 'regex:/^(([01]\d|2[0-3]):[0-5]\d|24:00)$/'],
            'overrides.*.is_unavailable' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
            'settings.slot_minutes' => ['nullable', 'integer', 'min:10', 'max:480'],
            'settings.buffer_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
            'settings.min_notice_hours' => ['nullable', 'integer', 'min:0', 'max:720'],
            'settings.max_advance_days' => ['nullable', 'integer', 'min:1', 'max:730'],
            'settings.cancel_window_hours' => ['nullable', 'integer', 'min:0', 'max:720'],
            'settings.no_show_policy' => ['nullable', Rule::in(['forfeit', 'fee', 'none'])],
            'settings.waitlist_claim_minutes' => ['nullable', 'integer', 'min:0', 'max:2880'],
            'settings.public_booking' => ['nullable', 'boolean'],
        ]);
        $coach = $this->staffMember($data['coach_id'] ?? $this->user()->id);
        $user = $this->user();
        if ($coach->id !== $user->id && ! $user->isOwnerIn() && ! $user->can('calendar.manage')) {
            throw ApiException::forbidden(__('calendar.errors.availability_forbidden'));
        }

        $errors = [];
        foreach ($data['weekly'] ?? [] as $i => $w) {
            if ($this->minutes($w['ends_at']) <= $this->minutes($w['starts_at'])) {
                $errors["weekly.$i.ends_at"] = [__('calendar.errors.invalid_times')];
            }
        }
        foreach ($data['overrides'] ?? [] as $i => $o) {
            $hasTimes = ! empty($o['starts_at']) || ! empty($o['ends_at']);
            if ($hasTimes && (empty($o['starts_at']) || empty($o['ends_at']) || $this->minutes($o['ends_at']) <= $this->minutes($o['starts_at']))) {
                $errors["overrides.$i.ends_at"] = [__('calendar.errors.invalid_times')];
            }
            if (! $hasTimes && empty($o['is_unavailable'])) {
                $errors["overrides.$i.starts_at"] = [__('calendar.errors.override_needs_times')];
            }
        }
        if ($errors !== []) {
            throw ApiException::invalid('validation_failed', __('calendar.errors.invalid_availability'), $errors);
        }

        DB::transaction(function () use ($data, $coach) {
            AvailabilityRule::query()->where('coach_id', $coach->id)->delete();
            foreach ($data['weekly'] ?? [] as $w) {
                AvailabilityRule::create(['coach_id' => $coach->id, 'weekday' => $w['weekday'], 'starts_at' => $w['starts_at'], 'ends_at' => $w['ends_at'], 'is_unavailable' => false, 'timezone' => $data['timezone']]);
            }
            foreach ($data['overrides'] ?? [] as $o) {
                AvailabilityRule::create(['coach_id' => $coach->id, 'date' => $o['date'], 'starts_at' => $o['starts_at'] ?? null, 'ends_at' => $o['ends_at'] ?? null, 'is_unavailable' => (bool) ($o['is_unavailable'] ?? false), 'timezone' => $data['timezone']]);
            }
            if (isset($data['settings'])) {
                $settings = BookingSetting::query()->firstOrNew(['coach_id' => $coach->id]);
                $settings->fill(array_filter($data['settings'], fn ($v) => $v !== null))->save();
            }
        });

        return $this->ok($this->present($coach->id, $data['timezone']));
    }

    public function locations(): JsonResponse
    {
        return $this->ok(Location::query()->orderBy('name')->get()->map(fn (Location $l) => $this->location($l))->values()->all());
    }

    public function storeLocation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'timezone' => ['nullable', 'timezone:all'],
        ]);

        return $this->created($this->location(Location::create($data)));
    }

    /** @return array<string, mixed> */
    private function location(Location $l): array
    {
        return ['id' => $l->id, 'name' => $l->name, 'address' => $l->address, 'timezone' => $l->timezone];
    }

    /** @return array<string, mixed> */
    private function present(string $coachId, string $timezone): array
    {
        $rules = AvailabilityRule::query()->where('coach_id', $coachId)->orderBy('weekday')->orderBy('date')->orderBy('starts_at')->get();
        $s = $this->availability->settingsFor($coachId);
        $time = fn (?string $t) => $t === null ? null : substr($t, 0, 5);

        return [
            'coach_id' => $coachId,
            'timezone' => $timezone,
            'weekly' => $rules->whereNull('date')->map(fn (AvailabilityRule $r) => ['id' => $r->id, 'weekday' => (int) $r->weekday, 'starts_at' => $time($r->starts_at), 'ends_at' => $time($r->ends_at)])->values()->all(),
            'overrides' => $rules->whereNotNull('date')->map(fn (AvailabilityRule $r) => ['id' => $r->id, 'date' => $r->date->toDateString(), 'starts_at' => $time($r->starts_at), 'ends_at' => $time($r->ends_at), 'is_unavailable' => (bool) $r->is_unavailable])->values()->all(),
            'settings' => [
                'slot_minutes' => (int) $s->slot_minutes,
                'buffer_minutes' => (int) $s->buffer_minutes,
                'min_notice_hours' => (int) $s->min_notice_hours,
                'max_advance_days' => (int) $s->max_advance_days,
                'cancel_window_hours' => (int) $s->cancel_window_hours,
                'no_show_policy' => $s->no_show_policy,
                'waitlist_claim_minutes' => (int) $s->waitlist_claim_minutes,
                'public_booking' => (bool) $s->public_booking,
            ],
        ];
    }

    private function minutes(string $hhmm): int
    {
        [$h, $m] = array_map('intval', explode(':', $hhmm));

        return $h * 60 + $m;
    }
}
