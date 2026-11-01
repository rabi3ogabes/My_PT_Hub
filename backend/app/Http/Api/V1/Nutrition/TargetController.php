<?php

namespace App\Http\Api\V1\Nutrition;

use App\Domain\Identity\Models\User;
use App\Domain\Nutrition\Models\NutritionTarget;
use App\Domain\Nutrition\Services\NutritionCalculator;
use App\Domain\Nutrition\Services\TargetService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Nutrition\NutritionTargetResource;
use App\Http\Api\V1\Support\TenantClients;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TargetController extends ApiController
{
    public function __construct(
        private readonly NutritionCalculator $calculator,
        private readonly TargetService $targets,
    ) {}

    /** GET /clients/{client}/nutrition-targets → history (newest first) + meta.current. */
    public function index(User $client): JsonResponse
    {
        TenantClients::assert($client);
        $rows = NutritionTarget::query()->where('client_id', $client->id)
            ->orderByDesc('effective_from')->orderByDesc('created_at')->get();
        $today = now($client->timezone ?: 'UTC')->toDateString();
        $current = $this->targets->forDate($client->id, $today);

        return $this->ok(NutritionTargetResource::collection($rows)->resolve(), [
            'current' => $current ? (new NutritionTargetResource($current))->resolve() : null,
            'profile_inputs' => (object) $this->targets->profileInputs($client),
        ]);
    }

    /**
     * POST /clients/{client}/nutrition-targets
     * Either explicit {kcal, protein_g, carbs_g, fat_g, fiber_g?, water_ml?, day_type?} or
     * {calculate: {...calculator inputs}, split_day_types?: bool}; plus effective_from?, override_safety?.
     */
    public function store(Request $request, User $client): JsonResponse
    {
        $this->authorizeManage();
        TenantClients::assert($client);
        $data = $request->validate([
            'calculate' => ['nullable', 'array'],
            ...$this->calculatorRules('calculate.', false),
            'split_day_types' => ['sometimes', 'boolean'],
            'kcal' => ['required_without:calculate', 'nullable', 'integer', 'min:500', 'max:10000'],
            'protein_g' => ['required_without:calculate', 'nullable', 'integer', 'min:0', 'max:600'],
            'carbs_g' => ['required_without:calculate', 'nullable', 'integer', 'min:0', 'max:1500'],
            'fat_g' => ['required_without:calculate', 'nullable', 'integer', 'min:0', 'max:500'],
            'fiber_g' => ['nullable', 'integer', 'min:0', 'max:200'],
            'water_ml' => ['nullable', 'integer', 'min:500', 'max:10000'],
            'day_type' => ['nullable', Rule::in(['all', 'training', 'rest'])],
            'effective_from' => ['nullable', 'date_format:Y-m-d'],
            'override_safety' => ['sometimes', 'boolean'],
        ]);
        $override = (bool) ($data['override_safety'] ?? false);
        if ($override && ! in_array($this->user()->roleIn(), ['owner', 'coach'], true)) {
            throw ApiException::forbidden(__('nutrition.errors.override_requires_coach'));
        }
        $base = ['effective_from' => $data['effective_from'] ?? null, 'override_safety' => $override];

        $created = DB::transaction(function () use ($data, $client, $base, $override) {
            if (empty($data['calculate'])) {
                return [$this->targets->store($client, $data + $base)];
            }
            $inputs = $this->calculatorInputs($data['calculate'] + $this->targets->profileInputs($client) + ['override_safety' => $override]);
            $result = $this->calculator->calculate($inputs);
            $calculation = ['inputs' => $inputs, 'result' => $result];
            $rows = [$this->targets->store($client, [...$base, ...$result, 'day_type' => 'all'], $calculation)];
            if ($data['split_day_types'] ?? false) {
                foreach (['training', 'rest'] as $type) {
                    $rows[] = $this->targets->store($client, [...$base, ...$result['day_types'][$type], 'water_ml' => $result['water_ml'], 'day_type' => $type, 'override_safety' => $override || $result['override_safety']], $calculation);
                }
            }

            return $rows;
        });

        return $this->created(NutritionTargetResource::collection($created)->resolve());
    }

    /** POST /nutrition/calculate — preview without saving (optionally pre-filled from client_id). */
    public function calculate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id' => ['nullable', 'string', 'size:26'],
            ...$this->calculatorRules('', false),
        ]);
        $inputs = $data;
        if (! empty($data['client_id'])) {
            $client = TenantClients::find([$data['client_id']], 'client_id')[0];
            $inputs += $this->targets->profileInputs($client);
        }
        unset($inputs['client_id']);

        return $this->ok($this->calculator->calculate($this->calculatorInputs($inputs)));
    }

    /**
     * @param  array<string, mixed>  $inputs
     * @return array<string, mixed>
     */
    private function calculatorInputs(array $inputs): array
    {
        $missing = array_values(array_filter(['weight_kg', 'height_cm', 'age', 'sex'], fn ($k) => empty($inputs[$k])));
        if (($inputs['formula'] ?? 'auto') === 'katch' || ! empty($inputs['body_fat_pct'])) {
            $missing = array_values(array_diff($missing, ['height_cm', 'age', 'sex']));
            if (empty($inputs['sex'])) {
                $missing[] = 'sex'; // still needed for the safety floor
            }
        }
        if (($inputs['preset'] ?? null) === 'custom' && abs(array_sum(array_map('floatval', (array) ($inputs['custom_split'] ?? []))) - 100) > 0.5) {
            throw ApiException::invalid('validation_failed', __('api.validation_failed'), ['custom_split' => [__('nutrition.errors.split_must_total_100')]]);
        }
        if ($missing) {
            $errors = [];
            foreach ($missing as $field) {
                $errors[$field] = [__('validation.required', ['attribute' => $field])];
            }
            throw ApiException::invalid('validation_failed', __('api.validation_failed'), $errors);
        }

        return $inputs + ['activity' => 'moderate', 'goal' => 'maintenance', 'age' => 30, 'height_cm' => 170];
    }

    /** @return array<string, mixed> */
    private function calculatorRules(string $prefix, bool $required): array
    {
        $r = $required ? 'required' : 'nullable';

        return [
            $prefix.'sex' => [$r, Rule::in(['male', 'female'])],
            $prefix.'age' => [$r, 'integer', 'min:14', 'max:100'],
            $prefix.'height_cm' => [$r, 'numeric', 'min:100', 'max:250'],
            $prefix.'weight_kg' => [$r, 'numeric', 'min:30', 'max:350'],
            $prefix.'body_fat_pct' => ['nullable', 'numeric', 'min:3', 'max:60'],
            $prefix.'activity' => ['nullable', Rule::in(array_keys(NutritionCalculator::ACTIVITY_FACTORS))],
            $prefix.'goal' => ['nullable', Rule::in(array_keys(NutritionCalculator::GOAL_ADJUSTMENTS))],
            $prefix.'formula' => ['nullable', Rule::in(['auto', 'mifflin', 'katch'])],
            $prefix.'adjustment_pct' => ['nullable', 'numeric', 'min:-35', 'max:35'],
            $prefix.'preset' => ['nullable', Rule::in([...array_keys(NutritionCalculator::PRESETS), 'custom'])],
            $prefix.'custom_split' => ['nullable', 'required_if:'.$prefix.'preset,custom', 'array', 'size:3'],
            $prefix.'custom_split.*' => ['numeric', 'min:0', 'max:100'],
            $prefix.'protein_per_kg' => ['nullable', 'numeric', 'min:0.8', 'max:3.5'],
            $prefix.'cycle_pct' => ['nullable', 'numeric', 'min:0', 'max:30'],
            $prefix.'override_safety' => ['sometimes', 'boolean'],
        ];
    }

    private function authorizeManage(): void
    {
        if (! $this->user()->can('nutrition.manage')) {
            throw ApiException::forbidden();
        }
    }
}
