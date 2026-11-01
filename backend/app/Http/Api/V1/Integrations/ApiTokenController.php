<?php

namespace App\Http\Api\V1\Integrations;

use App\Domain\Billing\Services\PlanGate;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Personal access tokens for the public REST API (Zapier, Make, custom code).
 * Tokens carry the abilities: access, pat, tenant:<id> and the chosen scopes.
 * The plain-text token is returned only once.
 */
class ApiTokenController extends ApiController
{
    public const MARKER = 'pat';

    public function index(): JsonResponse
    {
        $tenantAbility = 'tenant:'.$this->tenant()->id;
        $tokens = $this->user()->tokens()->latest('id')->get()
            ->filter(fn (PersonalAccessToken $t) => in_array(self::MARKER, $t->abilities ?? [], true) && in_array($tenantAbility, $t->abilities ?? [], true))
            ->map(fn (PersonalAccessToken $t) => $this->present($t))->values()->all();

        return $this->ok($tokens, ['scopes' => config('integrations.api_tokens.scopes')]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeManage();
        app(PlanGate::class)->assertFeature($this->tenant(), 'api');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'scopes' => ['required', 'array', 'min:1'],
            'scopes.*' => ['string', Rule::in(config('integrations.api_tokens.scopes'))],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:730'],
        ]);

        $expiresAt = isset($data['expires_in_days']) ? now()->addDays((int) $data['expires_in_days']) : null;
        $abilities = array_values(array_unique(['access', self::MARKER, 'tenant:'.$this->tenant()->id, ...$data['scopes']]));
        $token = $this->user()->createToken($data['name'], $abilities, $expiresAt);
        ActivityLogger::log('api_token.created', $this->user(), ['name' => $data['name'], 'scopes' => $data['scopes']]);

        return $this->created($this->present($token->accessToken) + ['token' => $token->plainTextToken]);
    }

    public function destroy(string $token): JsonResponse
    {
        $model = $this->user()->tokens()->whereKey($token)->first();
        if (! $model || ! in_array(self::MARKER, $model->abilities ?? [], true)) {
            throw new ApiException('not_found', __('integrations.errors.token_not_found'), 404);
        }
        $model->delete();
        ActivityLogger::log('api_token.revoked', $this->user(), ['name' => $model->name]);

        return $this->noContent();
    }

    /** @return array<string, mixed> */
    private function present(PersonalAccessToken $token): array
    {
        return [
            'id' => (string) $token->getKey(),
            'name' => $token->name,
            'scopes' => array_values(array_filter($token->abilities ?? [], fn (string $a) => ! in_array($a, ['access', self::MARKER], true) && ! str_starts_with($a, 'tenant:'))),
            'last_used_at' => $token->last_used_at?->toIso8601String(),
            'expires_at' => $token->expires_at?->toIso8601String(),
            'created_at' => $token->created_at?->toIso8601String(),
        ];
    }

    private function authorizeManage(): void
    {
        $user = $this->user();
        if (! $user->isOwnerIn($this->tenant()->id) && ! $user->can('integrations.manage')) {
            throw ApiException::forbidden();
        }
    }
}
