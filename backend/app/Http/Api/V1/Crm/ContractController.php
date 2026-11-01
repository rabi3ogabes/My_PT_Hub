<?php

namespace App\Http\Api\V1\Crm;

use App\Domain\Crm\Models\Contract;
use App\Domain\Crm\Models\ContractTemplate;
use App\Domain\Crm\Services\ContractService;
use App\Domain\Identity\Models\User;
use App\Domain\Payments\Support\Permission;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Payments\InvoiceController;
use App\Http\Api\V1\Resources\Crm\ContractResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ContractController extends ApiController
{
    public function __construct(private readonly ContractService $contracts) {}

    public function index(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'crm.manage', 'clients.view');
        $query = Contract::query()->with(['client', 'template'])
            ->when($request->input('filter.client_id'), fn ($q, $id) => $q->where('client_id', $id))
            ->when($request->input('filter.status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at');

        return $this->paginated($query->paginate($this->perPage()), ContractResource::class);
    }

    public function store(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'crm.manage');
        $data = $request->validate([
            'client_id' => ['required', 'string', 'size:26'],
            'contract_template_id' => ['nullable', 'string', 'size:26', 'required_without:body'],
            'title' => ['nullable', 'string', 'max:160'],
            'body' => ['nullable', 'string', 'max:100000'],
        ]);
        $client = User::find($data['client_id']) ?? throw ApiException::invalid('validation_failed', __('crm_module.errors.not_a_client'), ['client_id' => [__('crm_module.errors.not_a_client')]]);
        $template = ! empty($data['contract_template_id']) ? ContractTemplate::query()->findOrFail($data['contract_template_id']) : null;

        $contract = $this->contracts->send($this->tenant(), $client, $this->user(), $template, $data['title'] ?? null, $data['body'] ?? null);

        return $this->created(new ContractResource($contract->load(['client', 'template'])));
    }

    public function show(Contract $contract): JsonResponse
    {
        Permission::assert($this->user(), 'crm.manage', 'clients.view');

        return $this->ok(new ContractResource($contract->load(['client', 'template'])));
    }

    public function pdf(Request $request, Contract $contract): Response
    {
        Permission::assert($this->user(), 'crm.manage', 'clients.view');
        $locale = $request->validate(['locale' => ['nullable', Rule::in(['en', 'ar', 'fr', 'es'])]])['locale'] ?? null;
        $pdf = $contract->pdf_path && ! $locale && Storage::disk('local')->exists($contract->pdf_path)
            ? (string) Storage::disk('local')->get($contract->pdf_path)
            : $this->contracts->pdf($contract, $locale);

        return InvoiceController::pdfResponse($pdf, $contract->title);
    }
}
