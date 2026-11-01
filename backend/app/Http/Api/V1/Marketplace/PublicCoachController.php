<?php

namespace App\Http\Api\V1\Marketplace;

use App\Domain\Marketing\Services\CoachDirectoryService;
use App\Domain\Marketing\Services\MarketplaceLeadService;
use App\Domain\Marketing\Services\PublicAvailabilityService;
use App\Domain\Tenancy\Models\CoachProfile;
use App\Http\Api\ApiController;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Public coach directory, profiles, bookable slots and contact (lead) form. */
class PublicCoachController extends ApiController
{
    public function __construct(
        private readonly CoachDirectoryService $directory,
        private readonly PublicAvailabilityService $availability,
        private readonly MarketplaceLeadService $leads,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'city' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'size:2'],
            'specialty' => ['nullable', 'string', 'max:40'],
            'language' => ['nullable', 'string', 'max:5'],
            'mode' => ['nullable', Rule::in(['online', 'in_person', 'any'])],
            'price_min' => ['nullable', 'integer', 'min:0'],
            'price_max' => ['nullable', 'integer', 'min:0'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'verified' => ['nullable', 'boolean'],
            'q' => ['nullable', 'string', 'max:80'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $page = $this->directory->search($filters)->paginate($this->perPage((int) config('integrations.marketplace.directory_per_page', 20), 50));

        return response()->json([
            'data' => collect($page->items())->map(fn (CoachProfile $p) => $this->directory->card($p))->all(),
            'meta' => ['per_page' => $page->perPage(), 'current_page' => $page->currentPage(), 'total' => $page->total(), 'last_page' => $page->lastPage()],
            'links' => ['next' => $page->nextPageUrl(), 'prev' => $page->previousPageUrl()],
            'errors' => [],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        return $this->ok($this->directory->profile($this->directory->findPublished($slug)));
    }

    public function slots(Request $request, string $slug): JsonResponse
    {
        $data = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'days' => ['nullable', 'integer', 'min:1', 'max:31'],
        ]);
        $profile = $this->directory->findPublished($slug);
        $from = isset($data['from']) ? CarbonImmutable::parse($data['from']) : CarbonImmutable::now();
        $from = $from->lt(CarbonImmutable::today()) ? CarbonImmutable::today() : $from;

        return $this->ok($this->availability->slots($profile, $from, (int) ($data['days'] ?? 7)), [
            'timezone' => $this->availability->summary($profile)['timezone'],
            'from' => $from->toDateString(),
        ]);
    }

    public function lead(Request $request, string $slug): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required_without:phone', 'nullable', 'email:rfc', 'max:190'],
            'phone' => ['required_without:email', 'nullable', 'string', 'max:32'],
            'message' => ['nullable', 'string', 'max:2000'],
            'goal' => ['nullable', Rule::in(['fat_loss', 'muscle_gain', 'strength', 'performance', 'health'])],
            'consent' => ['accepted'],
            'website' => ['nullable', 'max:0'], // honeypot
        ]);
        $profile = $this->directory->findPublished($slug);
        $this->leads->create($profile, $data + ['consent' => true], 'profile', $request->user('sanctum'));

        return $this->created(['received' => true]);
    }
}
