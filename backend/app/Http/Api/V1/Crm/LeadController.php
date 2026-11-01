<?php

namespace App\Http\Api\V1\Crm;

use App\Domain\Crm\Models\Lead;
use App\Domain\Crm\Models\LeadStage;
use App\Domain\Crm\Services\LeadService;
use App\Domain\Identity\Models\User;
use App\Domain\Payments\Models\Product;
use App\Domain\Payments\Support\Permission;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Crm\LeadActivityResource;
use App\Http\Api\V1\Resources\Crm\LeadResource;
use App\Http\Api\V1\Resources\Crm\LeadStageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends ApiController
{
    public const SOURCES = ['profile', 'form', 'referral', 'instagram', 'whatsapp', 'manual', 'marketplace'];

    public function __construct(private readonly LeadService $leads) {}

    public function stages(): JsonResponse
    {
        Permission::assert($this->user(), 'crm.manage');
        $stages = LeadStage::query()->withCount('leads')->orderBy('position')->get();

        return $this->ok(LeadStageResource::collection($stages)->resolve(request()));
    }

    public function saveStages(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'crm.manage');
        $data = $request->validate([
            'stages' => ['required', 'array', 'min:1', 'max:20'],
            'stages.*.id' => ['nullable', 'string', 'size:26'],
            'stages.*.name' => ['required', 'string', 'max:60'],
            'stages.*.kind' => ['nullable', Rule::in(['open', 'won', 'lost'])],
        ]);

        return $this->ok(LeadStageResource::collection($this->leads->saveStages($data['stages']))->resolve($request));
    }

    public function index(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'crm.manage');
        $query = Lead::query()->with(['stage', 'owner'])
            ->when($request->input('filter.stage'), fn ($q, $s) => $q->where('lead_stage_id', $s))
            ->when($request->input('filter.source'), fn ($q, $s) => $q->whereIn('source', explode(',', (string) $s)))
            ->when($request->input('filter.owner_id'), fn ($q, $id) => $q->where('owner_id', $id))
            ->when($request->has('filter.converted'), fn ($q) => $request->boolean('filter.converted') ? $q->whereNotNull('converted_at') : $q->whereNull('converted_at'))
            ->when($request->input('filter.tag'), fn ($q, $tag) => $q->whereJsonContains('tags', $tag))
            ->when($request->input('q'), fn ($q, $term) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")));
        $sort = (string) $request->input('sort', '-created_at');
        $column = ltrim($sort, '-');
        $query->orderBy(in_array($column, ['created_at', 'updated_at', 'name', 'value_minor'], true) ? $column : 'created_at', str_starts_with($sort, '-') ? 'desc' : 'asc');

        return $this->paginated($query->paginate($this->perPage(50, 200)), LeadResource::class);
    }

    public function store(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'crm.manage');
        $data = $request->validate($this->rules(true));
        $this->assertRefs($data);
        $lead = Lead::create($data + ['lead_stage_id' => $data['lead_stage_id'] ?? $this->leads->defaultStage()?->id, 'owner_id' => $data['owner_id'] ?? $this->user()->id]);

        return $this->created(new LeadResource($lead->load(['stage', 'owner'])));
    }

    public function show(Lead $lead): JsonResponse
    {
        Permission::assert($this->user(), 'crm.manage');

        return $this->ok(new LeadResource($lead->load(['stage', 'owner', 'activities.user'])));
    }

    public function update(Request $request, Lead $lead): JsonResponse
    {
        Permission::assert($this->user(), 'crm.manage');
        $data = $request->validate($this->rules(false));
        $this->assertRefs($data);
        if (! empty($data['lead_stage_id']) && $data['lead_stage_id'] !== $lead->lead_stage_id) {
            $this->leads->moveToStage($lead, LeadStage::query()->findOrFail($data['lead_stage_id']), $this->user());
        }
        unset($data['lead_stage_id']);
        $lead->update($data);

        return $this->ok(new LeadResource($lead->refresh()->load(['stage', 'owner', 'activities.user'])));
    }

    public function destroy(Lead $lead): JsonResponse
    {
        Permission::assert($this->user(), 'crm.manage');
        $lead->delete();

        return $this->noContent();
    }

    public function activity(Request $request, Lead $lead): JsonResponse
    {
        Permission::assert($this->user(), 'crm.manage');
        $data = $request->validate([
            'type' => ['required', Rule::in(['note', 'task', 'call', 'email', 'stage'])],
            'body' => ['required_unless:type,stage', 'nullable', 'string', 'max:5000'],
            'due_at' => ['nullable', 'date'],
            'completed' => ['sometimes', 'boolean'],
            'lead_stage_id' => ['required_if:type,stage', 'nullable', 'string', 'size:26'],
        ]);
        if ($data['type'] === 'stage' && ! LeadStage::query()->whereKey($data['lead_stage_id'])->exists()) {
            throw ApiException::invalid('validation_failed', __('crm_module.errors.unknown_stage'), ['lead_stage_id' => [__('crm_module.errors.unknown_stage')]]);
        }
        $activity = $this->leads->addActivity($lead, $data, $this->user());

        return $this->created(new LeadActivityResource($activity->load('user')));
    }

    public function convert(Request $request, Lead $lead): JsonResponse
    {
        Permission::assert($this->user(), 'crm.manage');
        $data = $request->validate([
            'product_id' => ['nullable', 'string', 'size:26'],
            'program_id' => ['nullable', 'string', 'size:26'],
        ]);
        $product = ! empty($data['product_id']) ? Product::query()->where('is_active', true)->findOrFail($data['product_id']) : null;
        $result = $this->leads->convert($this->tenant(), $lead, $this->user(), $product, $data['program_id'] ?? null);

        return $this->ok([
            'lead' => (new LeadResource($result['lead']->load(['stage', 'owner'])))->resolve($request),
            'invitation_code' => $result['invitation_code'],
            'invite_url' => url('invite/'.$result['invitation_code']),
            'checkout_url' => $result['checkout_url'],
        ]);
    }

    /** @return array<string, mixed> */
    private function rules(bool $creating): array
    {
        return [
            'name' => [$creating ? 'required' : 'sometimes', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:32'],
            'source' => ['sometimes', Rule::in(self::SOURCES)],
            'message' => ['nullable', 'string', 'max:5000'],
            'tags' => ['nullable', 'array', 'max:20'],
            'tags.*' => ['string', 'max:40'],
            'value_minor' => ['nullable', 'integer', 'min:0'],
            'consent' => ['sometimes', 'boolean'],
            'lead_stage_id' => ['nullable', 'string', 'size:26'],
            'owner_id' => ['nullable', 'string', 'size:26'],
        ];
    }

    /** @param array<string, mixed> $data */
    private function assertRefs(array $data): void
    {
        if (! empty($data['lead_stage_id']) && ! LeadStage::query()->whereKey($data['lead_stage_id'])->exists()) {
            throw ApiException::invalid('validation_failed', __('crm_module.errors.unknown_stage'), ['lead_stage_id' => [__('crm_module.errors.unknown_stage')]]);
        }
        if (! empty($data['owner_id']) && ! User::find($data['owner_id'])?->isStaffIn($this->tenant()->id)) {
            throw ApiException::invalid('validation_failed', __('crm_module.errors.unknown_owner'), ['owner_id' => [__('crm_module.errors.unknown_owner')]]);
        }
    }
}
