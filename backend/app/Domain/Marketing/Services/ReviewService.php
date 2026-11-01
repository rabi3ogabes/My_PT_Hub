<?php

namespace App\Domain\Marketing\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Marketing\Models\Review;
use App\Domain\Marketing\Models\ReviewRequest;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Models\CoachProfile;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use App\Notifications\Marketplace\ReviewRequestNotification;
use Illuminate\Support\Facades\URL;

/**
 * Reviews lifecycle: request after 30 days → submission (verified when the
 * author is a client of the business) → moderation (pending/approved/rejected)
 * → coach reply. The aggregate rating counts approved verified reviews only.
 */
final class ReviewService
{
    public function __construct(private readonly TenantContext $context) {}

    /** The profile a client reviews: their assigned coach's, falling back to the owner's. */
    public function profileForClient(Tenant $tenant, User $client): ?CoachProfile
    {
        return $this->context->run($tenant, function () use ($client) {
            $coachId = ClientProfile::query()->where('user_id', $client->id)->value('coach_id');
            $profile = $coachId ? CoachProfile::query()->where('user_id', $coachId)->first() : null;
            if ($profile) {
                return $profile;
            }
            $ownerId = Membership::query()->where('role', 'owner')->where('status', 'active')->value('user_id');

            return $ownerId ? CoachProfile::query()->where('user_id', $ownerId)->first() : null;
        });
    }

    public function submit(Tenant $tenant, User $client, int $rating, ?string $body): Review
    {
        if ($rating < 1 || $rating > 5) {
            throw ApiException::invalid('validation_failed', __('marketplace.errors.invalid_rating'), ['rating' => [__('marketplace.errors.invalid_rating')]]);
        }
        $profile = $this->profileForClient($tenant, $client) ?? throw new ApiException('not_found', __('marketplace.errors.coach_not_found'), 404);
        $verified = Membership::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('user_id', $client->id)->where('role', 'client')->exists();

        $review = $this->context->run($tenant, fn () => Review::query()->updateOrCreate(
            ['coach_profile_id' => $profile->id, 'client_id' => $client->id],
            ['rating' => $rating, 'body' => $body !== null ? trim($body) : null, 'status' => 'pending', 'is_verified' => $verified, 'moderated_at' => null],
        ));

        return $review;
    }

    public function moderate(Review $review, string $status): Review
    {
        if (! in_array($status, Review::STATUSES, true)) {
            throw ApiException::invalid('validation_failed', __('marketplace.errors.invalid_status'), ['status' => [__('marketplace.errors.invalid_status')]]);
        }
        $review->forceFill(['status' => $status, 'moderated_at' => now()])->save();

        return $review;
    }

    public function reply(Review $review, string $reply): Review
    {
        $review->forceFill(['coach_reply' => trim($reply), 'replied_at' => now()])->save();

        return $review;
    }

    /** Recomputes rating_avg/rating_count from approved, verified reviews. */
    public function refreshAggregate(string $coachProfileId): void
    {
        $stats = Review::withoutGlobalScopes()->published()->where('coach_profile_id', $coachProfileId)
            ->selectRaw('COUNT(*) as c, AVG(rating) as a')->first();

        CoachProfile::withoutGlobalScopes()->whereKey($coachProfileId)->update([
            'rating_count' => (int) ($stats->c ?? 0),
            'rating_avg' => round((float) ($stats->a ?? 0), 2),
        ]);
    }

    /** Signed link (30 days) the client uses to submit the review. */
    public function submitUrl(Tenant $tenant, User $client): string
    {
        return URL::temporarySignedRoute('marketplace.reviews.submit', now()->addDays(30), ['tenant' => $tenant->id, 'client' => $client->id]);
    }

    /**
     * Sends one review request to each client who joined 30+ days ago
     * (within the request window) and has not been asked yet.
     */
    public function sendDueRequests(): int
    {
        $after = (int) config('integrations.marketplace.review_request_after_days', 30);
        $window = (int) config('integrations.marketplace.review_request_window_days', 30);
        $sent = 0;

        Membership::withoutGlobalScopes()->where('role', 'client')->where('status', 'active')
            ->whereBetween('joined_at', [now()->subDays($after + $window), now()->subDays($after)])
            ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('review_requests')
                ->whereColumn('review_requests.tenant_id', 'tenant_user.tenant_id')
                ->whereColumn('review_requests.client_id', 'tenant_user.user_id'))
            ->with('user')
            ->chunkById(200, function ($memberships) use (&$sent) {
                foreach ($memberships as $membership) {
                    $tenant = Tenant::query()->find($membership->tenant_id);
                    $client = $membership->user;
                    if (! $tenant || ! $client || $tenant->isPersonal()) {
                        continue;
                    }
                    $profile = $this->profileForClient($tenant, $client);
                    if (! $profile || ! $profile->is_published) {
                        continue;
                    }

                    $this->context->run($tenant, fn () => ReviewRequest::create([
                        'coach_profile_id' => $profile->id, 'client_id' => $client->id, 'sent_at' => now(),
                    ]));
                    $coachName = $profile->user()->value('name') ?? $tenant->name;
                    $client->notify(new ReviewRequestNotification($coachName, $tenant->id, $this->submitUrl($tenant, $client)));
                    $sent++;
                }
            });

        return $sent;
    }
}
