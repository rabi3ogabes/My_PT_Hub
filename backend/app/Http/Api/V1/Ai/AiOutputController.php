<?php

namespace App\Http\Api\V1\Ai;

use App\Domain\Ai\Models\AiOutput;
use App\Domain\Ai\Services\AiOutputService;
use App\Domain\Ai\Support\AiFeatures;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Ai\AiOutputResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Review queue for AI drafts (staff only). */
class AiOutputController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'filter.feature' => ['nullable', Rule::in(AiFeatures::all())],
            'filter.status' => ['nullable', Rule::in(['draft', 'approved', 'edited', 'rejected', 'applied'])],
            'filter.client_id' => ['nullable', 'string', 'max:26'],
            'filter.flagged' => ['nullable', 'boolean'],
        ]);
        $filter = (array) $request->input('filter', []);

        $query = AiOutput::query()
            ->with(['client:id,name', 'author:id,name', 'reviewer:id,name'])
            ->when($filter['feature'] ?? null, fn ($q, $v) => $q->where('feature', $v))
            ->when($filter['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filter['client_id'] ?? null, fn ($q, $v) => $q->where('client_id', $v))
            ->when(filter_var($filter['flagged'] ?? false, FILTER_VALIDATE_BOOLEAN), fn ($q) => $q->whereNotNull('flags')->where('flags', '!=', '[]'))
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        return $this->paginated($query->cursorPaginate($this->perPage()), AiOutputResource::class);
    }

    public function show(AiOutput $output): JsonResponse
    {
        return $this->ok(new AiOutputResource($output->load(['client:id,name', 'author:id,name', 'reviewer:id,name'])));
    }

    public function approve(Request $request, AiOutput $output, AiOutputService $service): JsonResponse
    {
        $data = $request->validate([
            'edited_payload' => ['nullable', 'array'],
            'apply' => ['nullable', 'boolean'],
        ]);

        return $this->ok(new AiOutputResource($service->approve($output, $this->user(), $data['edited_payload'] ?? null, (bool) ($data['apply'] ?? false))));
    }

    public function reject(Request $request, AiOutput $output, AiOutputService $service): JsonResponse
    {
        $data = $request->validate(['note' => ['nullable', 'string', 'max:1000']]);

        return $this->ok(new AiOutputResource($service->reject($output, $this->user(), $data['note'] ?? null)));
    }
}
