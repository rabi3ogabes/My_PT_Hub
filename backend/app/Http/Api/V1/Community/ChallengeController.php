<?php

namespace App\Http\Api\V1\Community;

use App\Domain\Community\Models\Challenge;
use App\Domain\Community\Models\ChallengeEntry;
use App\Domain\Community\Models\CommunitySpace;
use App\Domain\Community\Services\ChallengeScorer;
use App\Domain\Community\Services\CommunityService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Community\ChallengeResource;
use App\Http\Api\V1\Resources\Messaging\UserSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChallengeController extends ApiController
{
    public const METRICS = ['steps', 'workouts', 'habit_streak', 'volume', 'custom'];

    public function __construct(private readonly CommunityService $community, private readonly ChallengeScorer $scorer) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate(['filter.status' => ['nullable', Rule::in(['upcoming', 'active', 'ended'])]]);
        $today = now()->toDateString();
        $status = $request->input('filter.status');

        $challenges = Challenge::query()->withCount('entries')
            ->when($status === 'upcoming', fn ($q) => $q->whereDate('starts_on', '>', $today))
            ->when($status === 'active', fn ($q) => $q->whereDate('starts_on', '<=', $today)->whereDate('ends_on', '>=', $today))
            ->when($status === 'ended', fn ($q) => $q->whereDate('ends_on', '<', $today))
            ->orderByDesc('starts_on')->get();

        $mine = ChallengeEntry::query()->whereIn('challenge_id', $challenges->modelKeys())->where('user_id', $this->user()->id)->get()->keyBy('challenge_id');

        return $this->ok($challenges->map(function (Challenge $c) use ($mine, $request) {
            $resource = new ChallengeResource($c);
            $entry = $mine->get($c->id);
            $resource->extra = ['my_entry' => $entry ? ['score' => $entry->score, 'completed_at' => $entry->completed_at?->toIso8601String(), 'team' => $entry->team] : null];

            return $resource->resolve($request);
        })->values()->all());
    }

    /** Challenge with leaderboard (top 100) and the viewer's rank. */
    public function show(Request $request, Challenge $challenge): JsonResponse
    {
        return $this->ok($this->present($challenge, $request));
    }

    public function store(Request $request): JsonResponse
    {
        $this->assertModerator();
        $challenge = Challenge::create($this->validated($request, true));

        return $this->created($this->present($challenge, $request));
    }

    public function update(Request $request, Challenge $challenge): JsonResponse
    {
        $this->assertModerator();
        $challenge->update($this->validated($request, false, $challenge));

        return $this->ok($this->present($challenge, $request));
    }

    public function destroy(Challenge $challenge): JsonResponse
    {
        $this->assertModerator();
        $challenge->delete();

        return $this->noContent();
    }

    public function join(Request $request, Challenge $challenge): JsonResponse
    {
        $data = $request->validate(['team' => ['nullable', 'string', 'max:60']]);
        if ($challenge->ends_on->toDateString() < now()->toDateString()) {
            throw ApiException::conflict('challenge_ended', __('community_module.errors.challenge_ended'));
        }
        if ($challenge->community_space_id) {
            $space = CommunitySpace::query()->find($challenge->community_space_id);
            if (! $space || ! $this->community->canView($space, $this->user())) {
                throw ApiException::forbidden(__('community_module.errors.challenge_not_open'));
            }
        }

        $entry = ChallengeEntry::query()->firstOrCreate(
            ['challenge_id' => $challenge->id, 'user_id' => $this->user()->id],
            ['team' => $data['team'] ?? null, 'score' => 0],
        );
        if ($challenge->starts_on->toDateString() <= now()->toDateString()) {
            $this->scorer->scoreEntry($challenge, $entry, $this->user(), $this->tenant());
        }

        return $this->ok($this->present($challenge, $request));
    }

    /** @return array<string, mixed> */
    private function present(Challenge $challenge, Request $request): array
    {
        $challenge->loadCount('entries');
        $entries = ChallengeEntry::query()->where('challenge_id', $challenge->id)->with('user')
            ->orderByDesc('score')->orderByRaw('completed_at is null')->orderBy('completed_at')->orderBy('created_at')->limit(100)->get();

        $leaderboard = [];
        $rank = 0;
        $previous = null;
        foreach ($entries->values() as $i => $entry) {
            if ($previous === null || $entry->score < $previous) {
                $rank = $i + 1;
            }
            $previous = $entry->score;
            $leaderboard[] = ['rank' => $rank, 'user' => UserSummary::make($entry->user), 'score' => $entry->score, 'team' => $entry->team, 'completed_at' => $entry->completed_at?->toIso8601String()];
        }

        $mine = ChallengeEntry::query()->where('challenge_id', $challenge->id)->where('user_id', $this->user()->id)->first();
        $myEntry = null;
        if ($mine) {
            $myRank = ChallengeEntry::query()->where('challenge_id', $challenge->id)->where('score', '>', $mine->score)->count() + 1;
            $myEntry = ['score' => $mine->score, 'rank' => $myRank, 'team' => $mine->team, 'completed_at' => $mine->completed_at?->toIso8601String()];
        }

        $resource = new ChallengeResource($challenge);
        $resource->extra = ['my_entry' => $myEntry, 'leaderboard' => $leaderboard];

        return $resource->resolve($request);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, bool $creating, ?Challenge $existing = null): array
    {
        $req = $creating ? 'required' : 'sometimes';
        $data = $request->validate([
            'name' => [$req, 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'metric' => [$req, Rule::in(self::METRICS)],
            'goal' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'starts_on' => [$req, 'date_format:Y-m-d'],
            'ends_on' => [$req, 'date_format:Y-m-d'],
            'community_space_id' => ['nullable', 'string', 'size:26'],
            'badge_key' => ['nullable', 'string', 'max:32', 'regex:/^[a-z0-9_]+$/'],
        ]);
        $starts = $data['starts_on'] ?? $existing?->starts_on->toDateString();
        $ends = $data['ends_on'] ?? $existing?->ends_on->toDateString();
        if ($starts !== null && $ends !== null && $ends < $starts) {
            throw ApiException::invalid('validation_failed', __('community_module.errors.invalid_dates'), ['ends_on' => [__('community_module.errors.invalid_dates')]]);
        }
        if (! empty($data['community_space_id']) && ! CommunitySpace::query()->whereKey($data['community_space_id'])->exists()) {
            throw ApiException::invalid('validation_failed', __('community_module.errors.space_not_found'), ['community_space_id' => [__('community_module.errors.space_not_found')]]);
        }

        return $data;
    }

    private function assertModerator(): void
    {
        if (! $this->community->isModerator($this->user())) {
            throw ApiException::forbidden(__('community_module.errors.moderators_only'));
        }
    }
}
