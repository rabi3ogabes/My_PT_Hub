<?php

namespace App\Http\Api\V1\Crm;

use App\Domain\Crm\Models\ContractTemplate;
use App\Domain\Payments\Support\Permission;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Crm\ContractTemplateResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContractTemplateController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'crm.manage');
        $templates = ContractTemplate::query()->when($request->input('filter.kind'), fn ($q, $k) => $q->where('kind', $k))->orderBy('name')->get();

        return $this->ok(ContractTemplateResource::collection($templates)->resolve($request));
    }

    public function store(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'crm.manage');

        return $this->created(new ContractTemplateResource(ContractTemplate::create($this->validated($request, true))));
    }

    public function update(Request $request, ContractTemplate $contractTemplate): JsonResponse
    {
        Permission::assert($this->user(), 'crm.manage');
        $contractTemplate->update($this->validated($request, false));

        return $this->ok(new ContractTemplateResource($contractTemplate->refresh()));
    }

    public function destroy(ContractTemplate $contractTemplate): JsonResponse
    {
        Permission::assert($this->user(), 'crm.manage');
        $contractTemplate->delete();

        return $this->noContent();
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, bool $creating): array
    {
        $req = $creating ? 'required' : 'sometimes';

        return $request->validate([
            'name' => [$req, 'string', 'max:120'],
            'kind' => ['sometimes', Rule::in(['contract', 'waiver', 'parq'])],
            'body' => [$req, 'string', 'max:100000'],
        ]);
    }
}
