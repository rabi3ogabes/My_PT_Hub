<?php

namespace App\Http\Api\V1\Ai;

use App\Domain\Ai\Services\Features\PhotoFoodLogger;
use App\Http\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** POST /me/food-logs/photo — candidates only; the client confirms via POST /me/food-logs. */
class PhotoFoodController extends ApiController
{
    public function store(Request $request, PhotoFoodLogger $logger): JsonResponse
    {
        $data = $request->validate([
            'photo' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'slot' => ['nullable', Rule::in(['breakfast', 'lunch', 'dinner', 'snack', 'suhoor', 'iftar'])],
            'note' => ['nullable', 'string', 'max:300'],
        ]);

        return $this->ok($logger->analyse($this->tenant(), $this->user(), $request->file('photo'), $data['slot'] ?? null, $data['note'] ?? null));
    }
}
