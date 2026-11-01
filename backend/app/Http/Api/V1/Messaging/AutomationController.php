<?php

namespace App\Http\Api\V1\Messaging;

use App\Domain\Habits\Models\Habit;
use App\Domain\Messaging\Models\Automation;
use App\Domain\Messaging\Models\MessageTemplate;
use App\Domain\Messaging\Services\AutomationEngine;
use App\Domain\Messaging\Services\TemplateRenderer;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Workouts\Models\Program;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Messaging\AutomationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AutomationController extends ApiController
{
    public function __construct(private readonly TemplateRenderer $renderer) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate(['filter.trigger' => ['nullable', Rule::in(AutomationEngine::TRIGGERS)]]);
        $automations = Automation::query()->withCount('runs')
            ->when($request->input('filter.trigger'), fn ($q, $t) => $q->where('trigger', $t))
            ->orderBy('name')->get();

        return $this->ok(AutomationResource::collection($automations)->resolve($request), [
            'triggers' => AutomationEngine::TRIGGERS,
            'actions' => AutomationEngine::ACTIONS,
            'condition_fields' => AutomationEngine::CONDITION_FIELDS,
            'condition_ops' => AutomationEngine::CONDITION_OPS,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->assertCanManage();
        $automation = Automation::create($this->validated($request, true));

        return $this->created(new AutomationResource($automation->loadCount('runs')));
    }

    public function show(Automation $automation): JsonResponse
    {
        $automation->loadCount('runs')->load(['runs' => fn ($q) => $q->latest()->limit(20)]);

        return $this->ok(new AutomationResource($automation));
    }

    public function update(Request $request, Automation $automation): JsonResponse
    {
        $this->assertCanManage();
        $automation->update($this->validated($request, false));

        return $this->ok(new AutomationResource($automation->loadCount('runs')));
    }

    public function destroy(Automation $automation): JsonResponse
    {
        $this->assertCanManage();
        $automation->delete();

        return $this->noContent();
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, bool $creating): array
    {
        $req = $creating ? 'required' : 'sometimes';
        $data = $request->validate([
            'name' => [$req, 'string', 'max:120'],
            'trigger' => [$req, Rule::in(AutomationEngine::TRIGGERS)],
            'trigger_config' => ['nullable', 'array'],
            'trigger_config.days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'conditions' => ['nullable', 'array', 'max:20'],
            'conditions.*.field' => ['required', Rule::in(AutomationEngine::CONDITION_FIELDS)],
            'conditions.*.op' => ['required', Rule::in(AutomationEngine::CONDITION_OPS)],
            'conditions.*.value' => ['present'],
            'actions' => [$req, 'array', 'min:1', 'max:10'],
            'actions.*.type' => ['required', Rule::in(AutomationEngine::ACTIONS)],
            'actions.*.template_id' => ['nullable', 'string', 'size:26'],
            'actions.*.body' => ['nullable'],
            'actions.*.habit_id' => ['nullable', 'string', 'size:26'],
            'actions.*.program_id' => ['nullable', 'string', 'size:26'],
            'actions.*.tag' => ['nullable', 'string', 'max:64'],
            'actions.*.message' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $errors = [];
        foreach ($data['actions'] ?? [] as $i => $action) {
            $key = "actions.$i";
            switch ($action['type']) {
                case 'send_message':
                    if (! empty($action['template_id'])) {
                        if (! MessageTemplate::query()->whereKey($action['template_id'])->exists()) {
                            $errors["$key.template_id"] = [__('automations.errors.template_not_found')];
                        }
                    } else {
                        $body = $action['body'] ?? null;
                        $normalized = is_string($body) || is_array($body) ? $this->renderer->normalize($body, app()->getLocale()) : [];
                        if ($normalized === []) {
                            $errors["$key.body"] = [__('automations.errors.message_required')];
                        } else {
                            $data['actions'][$i]['body'] = $normalized;
                        }
                    }
                    break;
                case 'assign_habit':
                    if (empty($action['habit_id']) || ! Habit::query()->whereKey($action['habit_id'])->exists()) {
                        $errors["$key.habit_id"] = [__('automations.errors.habit_not_found')];
                    }
                    break;
                case 'assign_program':
                    if (empty($action['program_id']) || ! Program::query()->whereKey($action['program_id'])->exists()) {
                        $errors["$key.program_id"] = [__('automations.errors.program_not_found')];
                    }
                    break;
                case 'add_tag':
                    if (empty($action['tag'])) {
                        $errors["$key.tag"] = [__('automations.errors.tag_required')];
                    }
                    break;
            }
            $data['actions'][$i] = array_filter($data['actions'][$i], fn ($v) => $v !== null);
        }
        if (in_array($data['trigger'] ?? null, ['workout_missed', 'package_expiring'], true) && isset($data['trigger_config']['days']) && (int) $data['trigger_config']['days'] < 1) {
            $errors['trigger_config.days'] = [__('automations.errors.days_min')];
        }
        if ($errors !== []) {
            throw ApiException::invalid('validation_failed', __('automations.errors.invalid'), $errors);
        }
        if (array_key_exists('conditions', $data)) {
            $data['conditions'] = array_values($data['conditions'] ?? []);
        }

        return $data;
    }

    private function assertCanManage(): void
    {
        $user = $this->user();
        if (! $user->isOwnerIn() && ! $user->can('automations.manage')) {
            throw ApiException::forbidden(__('automations.errors.forbidden'));
        }
    }
}
