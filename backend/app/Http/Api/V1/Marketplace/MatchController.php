<?php

namespace App\Http\Api\V1\Marketplace;

use App\Domain\Marketing\Services\CoachDirectoryService;
use App\Domain\Marketing\Services\CoachMatchService;
use App\Domain\Marketing\Services\MarketplaceLeadService;
use App\Http\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** "Find a coach" quiz for standalone clients: ranked matches + an introduction lead. */
class MatchController extends ApiController
{
    public function __construct(
        private readonly CoachMatchService $matcher,
        private readonly CoachDirectoryService $directory,
        private readonly MarketplaceLeadService $leads,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'goal' => ['required', Rule::in(array_keys(CoachMatchService::GOAL_SPECIALTIES))],
            'specialties' => ['nullable', 'array', 'max:10'],
            'specialties.*' => ['string', 'max:40'],
            'languages' => ['nullable', 'array', 'max:5'],
            'languages.*' => ['string', 'max:5'],
            'mode' => ['nullable', Rule::in(['online', 'in_person', 'any'])],
            'city' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'size:2'],
            'budget_max_minor' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'coach_gender' => ['nullable', Rule::in(['male', 'female', 'any'])],
            'message' => ['nullable', 'string', 'max:2000'],
            'introduce' => ['nullable', 'boolean'],
            'coach_slug' => ['nullable', 'string', 'max:190'],
            'consent' => Rule::when($request->boolean('introduce', true), ['accepted'], ['nullable', 'boolean']),
        ]);

        $matches = $this->matcher->rank($data);
        $user = $this->user();
        $introduction = null;

        if ($request->boolean('introduce', true) && $request->boolean('consent')) {
            $target = isset($data['coach_slug']) ? $this->directory->findPublished($data['coach_slug']) : ($matches[0]['profile'] ?? null);
            if ($target) {
                $this->leads->create($target, [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'message' => $data['message'] ?? __('marketplace.match.default_message', ['goal' => __('marketplace.goals.'.$data['goal'])]),
                    'goal' => $data['goal'],
                    'consent' => true,
                ], 'match', $user);
                $introduction = ['coach_slug' => $target->slug, 'lead_created' => true];
            }
        }

        return $this->ok([
            'matches' => array_map(fn (array $m) => [
                'coach' => $this->directory->card($m['profile']),
                'score' => $m['score'],
                'reasons' => array_map(fn (string $r) => ['key' => $r, 'label' => __('marketplace.match.reasons.'.$r)], $m['reasons']),
            ], $matches),
            'introduction' => $introduction,
        ]);
    }
}
