<?php

namespace App\Http\Api\V1\Calendar;

use App\Domain\Calendar\Models\CreditLedgerEntry;
use App\Domain\Calendar\Services\BookingService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Calendar\Concerns\ResolvesMembers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Append-only session credits: balance == sum(entries), manual adjustments always carry a reason. */
class CreditController extends ApiController
{
    use ResolvesMembers;

    public function __construct(private readonly BookingService $bookings) {}

    public function show(Request $request, string $client): JsonResponse
    {
        $user = $this->clientMember($client);

        return $this->ok(self::summary($user->id, $this->bookings));
    }

    public function adjust(Request $request, string $client): JsonResponse
    {
        $actor = $this->user();
        if (! $actor->isOwnerIn() && ! $actor->can('bookings.manage')) {
            throw ApiException::forbidden(__('calendar.errors.bookings_forbidden'));
        }
        $user = $this->clientMember($client);
        $data = $request->validate([
            'amount' => ['required', 'integer', 'between:-1000,1000', 'not_in:0'],
            'note' => ['required', 'string', 'min:3', 'max:255'],
            'reason' => ['nullable', Rule::in(['adjustment', 'purchase', 'refund', 'expiry'])],
            'expires_on' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $this->bookings->adjustCredits($user, (int) $data['amount'], $data['note'], $actor, $data['expires_on'] ?? null, $data['reason'] ?? 'adjustment');

        return $this->created(self::summary($user->id, $this->bookings));
    }

    /** @return array{balance: int, entries: list<array<string, mixed>>} */
    public static function summary(string $clientId, BookingService $bookings): array
    {
        return [
            'balance' => $bookings->balance($clientId),
            'entries' => CreditLedgerEntry::query()->where('client_id', $clientId)->orderByDesc('created_at')->orderByDesc('id')->limit(200)->get()
                ->map(fn (CreditLedgerEntry $e) => [
                    'id' => $e->id,
                    'amount' => $e->amount,
                    'reason' => $e->reason,
                    'reason_label' => __('calendar.credit_reasons.'.$e->reason),
                    'note' => $e->note,
                    'booking_id' => $e->booking_id,
                    'expires_on' => $e->expires_on?->toDateString(),
                    'created_at' => $e->created_at?->toIso8601String(),
                ])->values()->all(),
        ];
    }
}
