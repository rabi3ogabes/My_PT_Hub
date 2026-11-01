<?php

namespace App\Http\Api\V1\Coach;

use App\Domain\Tenancy\Models\Invitation;
use App\Http\Api\ApiController;
use App\Notifications\InvitationNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class InvitationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->input('filter.status', 'pending');
        $page = Invitation::query()
            ->when($status === 'pending', fn ($q) => $q->whereNull('accepted_at')->where(fn ($w) => $w->whereNull('expires_at')->orWhere('expires_at', '>', now())))
            ->when($status === 'accepted', fn ($q) => $q->whereNotNull('accepted_at'))
            ->when($status === 'expired', fn ($q) => $q->whereNull('accepted_at')->where('expires_at', '<=', now()))
            ->latest()->paginate($this->perPage());

        return response()->json([
            'data' => collect($page->items())->map(fn (Invitation $i) => ClientController::invitationPayload($i)),
            'meta' => ['total' => $page->total(), 'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'per_page' => $page->perPage()],
            'links' => (object) [],
            'errors' => [],
        ]);
    }

    public function resend(Invitation $invitation): JsonResponse
    {
        abort_if($invitation->accepted_at !== null, 409);
        $invitation->update(['expires_at' => now()->addDays(14)]);
        if ($invitation->email) {
            Notification::route('mail', $invitation->email)->notify(new InvitationNotification($invitation, $this->tenant(), $this->user()));
        }

        return $this->ok(ClientController::invitationPayload($invitation->refresh()));
    }

    public function destroy(Invitation $invitation): JsonResponse
    {
        abort_if($invitation->accepted_at !== null, 409);
        $invitation->delete();

        return $this->noContent();
    }

    /** SVG QR code of the invite link (for in-person onboarding). */
    public function qr(Invitation $invitation): \Illuminate\Http\Response
    {
        return response(InviteQr::svg($invitation->code), 200, ['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'private, max-age=3600']);
    }
}
