<?php

namespace App\Http\Web\Marketplace;

use App\Domain\Identity\Models\User;
use App\Domain\Marketing\Services\ReviewService;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/** Signed-link review submission (link delivered by the 30-day review request). */
class ReviewSubmissionController extends Controller
{
    public function __invoke(Request $request, string $tenant, string $client, ReviewService $reviews): JsonResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:3000'],
        ]);
        $tenantModel = Tenant::query()->findOrFail($tenant);
        $user = User::query()->findOrFail($client);
        $review = $reviews->submit($tenantModel, $user, (int) $data['rating'], $data['body'] ?? null);

        return response()->json([
            'data' => ['id' => $review->id, 'status' => $review->status, 'rating' => $review->rating],
            'meta' => (object) [], 'links' => (object) [], 'errors' => [],
        ], 201);
    }
}
