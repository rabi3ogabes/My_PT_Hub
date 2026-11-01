<?php

namespace App\Http\Api\V1\Client;

use App\Domain\Crm\Models\Contract;
use App\Domain\Crm\Services\ContractService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Crm\ContractResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Client side of contracts, waivers and PAR-Q: read and e-sign (draw or type). */
class ClientContractController extends ApiController
{
    public function __construct(private readonly ContractService $contracts) {}

    public function index(): JsonResponse
    {
        $contracts = Contract::query()->where('client_id', $this->user()->id)->where('status', '!=', 'void')->with('template')->orderByDesc('created_at')->get();

        return $this->ok(ContractResource::collection($contracts)->resolve(request()));
    }

    public function sign(Request $request, Contract $contract): JsonResponse
    {
        if ($contract->client_id !== $this->user()->id) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }
        $data = $request->validate([
            'signature_type' => ['required', Rule::in(['draw', 'type'])],
            'signature_data' => ['required', 'string', 'max:500000'],
            'agree' => ['accepted'],
        ]);
        if ($data['signature_type'] === 'type' && mb_strlen(trim($data['signature_data'])) > 120) {
            throw ApiException::invalid('validation_failed', __('contracts.errors.invalid_signature'), ['signature_data' => [__('contracts.errors.invalid_signature')]]);
        }

        $signed = $this->contracts->sign($contract, $this->user(), $data['signature_type'],
            $data['signature_type'] === 'type' ? trim($data['signature_data']) : $data['signature_data'],
            $request->ip(), $request->userAgent());

        return $this->ok(new ContractResource($signed->load('template')));
    }
}
