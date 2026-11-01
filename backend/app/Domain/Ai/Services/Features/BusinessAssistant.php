<?php

namespace App\Domain\Ai\Services\Features;

use App\Domain\Ai\Data\AiTask;
use App\Domain\Ai\Models\AiOutput;
use App\Domain\Ai\Services\AiGateway;
use App\Domain\Ai\Services\AiOutputService;
use App\Domain\Ai\Services\AiSettingsService;
use App\Domain\Ai\Services\BusinessTools;
use App\Domain\Ai\Services\ToneEnforcer;
use App\Domain\Ai\Support\Schemas;
use App\Domain\Identity\Models\User;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Models\Program;

/**
 * I. Coach business assistant. `ask`: the model picks one predefined,
 * tenant-scoped tool (never raw SQL) and the server runs it. `content`:
 * captions, program descriptions and sales copy in the requested language.
 */
final class BusinessAssistant
{
    public function __construct(
        private readonly AiGateway $gateway,
        private readonly AiOutputService $outputs,
        private readonly AiSettingsService $settings,
        private readonly ToneEnforcer $tone,
        private readonly BusinessTools $tools,
    ) {}

    /** @return array<string, mixed> */
    public function ask(Tenant $tenant, User $coach, string $question): array
    {
        $result = $this->gateway->run(new AiTask(
            feature: 'business_ask',
            tenant: $tenant,
            user: $coach,
            variables: ['question' => $question, 'tools' => $this->toolCatalog(), 'today' => now()->toDateString()],
            context: ['question' => $question],
            schema: Schemas::businessAsk(),
        ));
        $choice = $result->data;
        $output = $this->tools->run($choice['tool'], $tenant, $choice['period'], $choice['limit']);

        return [
            'question' => $question,
            'tool' => $choice['tool'],
            'period' => $choice['period'],
            'answer' => $output['answer'],
            'rows' => $output['rows'],
            'ai_request_id' => $result->requestId,
        ];
    }

    /** @param array<string, mixed> $input */
    public function content(Tenant $tenant, User $coach, array $input): AiOutput
    {
        $voice = $this->settings->forUser($tenant, $coach);
        $program = ! empty($input['program_id']) ? Program::query()->find($input['program_id']) : null;
        $language = $input['language'] ?? $voice->language ?? $tenant->locale ?? 'en';
        $programInfo = $program ? [
            'name' => $program->translate('name', $language),
            'goal' => $program->goal,
            'level' => $program->level,
            'days_per_week' => $program->days_per_week,
            'weeks' => $program->weeks()->count(),
        ] : '-';

        $result = $this->gateway->run(new AiTask(
            feature: 'content',
            tenant: $tenant,
            user: $coach,
            variables: [
                'type' => $input['type'],
                'topic' => $input['topic'] ?? '-',
                'language' => $language,
                'program' => $programInfo,
                'brand' => $tenant->name,
                ...$this->tone->variables($voice),
            ],
            context: ['type' => $input['type'], 'topic' => $input['topic'] ?? ($programInfo['name'] ?? null), 'language' => $language, 'brand' => $tenant->name],
            schema: Schemas::content(),
            postProcess: function (array $data) use ($voice) {
                $banned = $this->tone->bannedWordsIn($data['body'].' '.($data['title'] ?? ''), $voice);

                return [$data, $banned === [] ? [] : ['body contains banned words: '.implode(', ', $banned)]];
            },
        ));

        return $this->outputs->draft(
            'content',
            $coach,
            null,
            array_filter(['type' => $input['type'], 'topic' => $input['topic'] ?? null, 'program_id' => $program?->id, 'language' => $language]),
            $result->data,
            ['label' => __('ai.grounding_content')],
            [],
            $result->requestId,
        );
    }

    /** @return list<array{name: string, description: string}> */
    private function toolCatalog(): array
    {
        return [
            ['name' => 'unpaid_clients', 'description' => 'Clients with past-due subscriptions, unpaid orders or open invoices in the period.'],
            ['name' => 'best_retention_program', 'description' => 'Programs ranked by share of assignments still active or completed.'],
            ['name' => 'at_risk_clients', 'description' => 'Clients with amber/red churn risk and the reasons.'],
            ['name' => 'revenue', 'description' => 'Payments received minus refunds in the period, per currency.'],
            ['name' => 'pending_checkins', 'description' => 'Submitted check-ins waiting for coach review.'],
            ['name' => 'unsupported', 'description' => 'The question cannot be answered with the tools above.'],
        ];
    }
}
