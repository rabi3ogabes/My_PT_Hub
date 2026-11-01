<?php

namespace App\Http\Api\V1\Ai;

use App\Domain\Ai\Services\Features\BusinessAssistant;
use App\Domain\Ai\Services\Features\CheckinCopilot;
use App\Domain\Ai\Services\Features\ChurnAdvisor;
use App\Domain\Ai\Services\Features\MealPlanGenerator;
use App\Domain\Ai\Services\Features\ProgramGenerator;
use App\Domain\Ai\Services\Features\ProgressionAssistant;
use App\Domain\Ai\Services\Features\SmartInbox;
use App\Domain\Ai\Services\ClientDataService;
use App\Domain\Ai\Support\Schemas;
use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Messaging\Models\Conversation;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Ai\AiOutputResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Coach copilot endpoints. Every generator returns an AiOutput draft awaiting approval. */
class CopilotController extends ApiController
{
    public function program(Request $request, ProgramGenerator $generator): JsonResponse
    {
        $data = $request->validate([
            'client_id' => ['nullable', 'string', 'max:26'],
            'prompt' => ['required_without_all:client_id,goal', 'nullable', 'string', 'max:2000'],
            'goal' => ['nullable', Rule::in(Schemas::GOALS)],
            'level' => ['nullable', Rule::in(Schemas::LEVELS)],
            'days_per_week' => ['nullable', 'integer', 'between:1,7'],
            'session_minutes' => ['nullable', 'integer', 'between:15,180'],
            'weeks' => ['nullable', 'integer', 'between:1,16'],
            'equipment' => ['nullable', 'array', 'max:20'],
            'equipment.*' => ['string', 'max:40'],
            'focus' => ['nullable', 'array', 'max:10'],
            'focus.*' => ['string', 'max:40'],
            'injuries' => ['nullable', 'string', 'max:1000'],
            'language' => ['nullable', Rule::in(Schemas::LANGUAGES)],
        ]);

        return $this->created(new AiOutputResource($generator->generate($this->tenant(), $this->user(), $data)));
    }

    public function mealPlan(Request $request, MealPlanGenerator $generator): JsonResponse
    {
        $data = $request->validate([
            'client_id' => ['nullable', 'string', 'max:26'],
            'name' => ['nullable', 'string', 'max:120'],
            'kcal' => ['nullable', 'integer', 'between:800,6000'],
            'protein_g' => ['nullable', 'integer', 'between:0,500'],
            'carbs_g' => ['nullable', 'integer', 'between:0,900'],
            'fat_g' => ['nullable', 'integer', 'between:0,400'],
            'diet_tags' => ['nullable', 'array', 'max:10'],
            'diet_tags.*' => ['string', 'max:32'],
            'allergies' => ['nullable', 'array', 'max:15'],
            'allergies.*' => ['string', 'max:40'],
            'cuisine' => ['nullable', Rule::in(array_keys(MealPlanGenerator::CUISINE_REGIONS))],
            'meals_per_day' => ['nullable', 'integer', 'between:3,6'],
            'days' => ['nullable', 'integer', 'between:1,7'],
            'ramadan' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'language' => ['nullable', Rule::in(Schemas::LANGUAGES)],
        ]);

        return $this->created(new AiOutputResource($generator->generate($this->tenant(), $this->user(), $data)));
    }

    public function checkinSummary(Request $request, CheckinResponse $response, CheckinCopilot $copilot): JsonResponse
    {
        $data = $request->validate(['language' => ['nullable', Rule::in(Schemas::LANGUAGES)]]);

        return $this->created(new AiOutputResource($copilot->summarize($this->tenant(), $this->user(), $response, $data)));
    }

    public function replyDraft(Request $request, SmartInbox $inbox): JsonResponse
    {
        $data = $request->validate([
            'conversation_id' => ['required_without:message_id', 'nullable', 'string', 'max:26'],
            'message_id' => ['nullable', 'string', 'max:26'],
            'instruction' => ['nullable', 'string', 'max:500'],
            'language' => ['nullable', Rule::in(Schemas::LANGUAGES)],
        ]);

        return $this->created(new AiOutputResource($inbox->replyDraft($this->tenant(), $this->user(), $data)));
    }

    public function summarizeThread(Conversation $conversation, SmartInbox $inbox): JsonResponse
    {
        return $this->created(new AiOutputResource($inbox->summarize($this->tenant(), $this->user(), $conversation)));
    }

    public function progression(Request $request, string $client, ProgressionAssistant $assistant, ClientDataService $clients): JsonResponse
    {
        $data = $request->validate(['language' => ['nullable', Rule::in(Schemas::LANGUAGES)]]);
        $clientUser = $clients->resolveClient($this->tenant(), $client);

        return $this->created(new AiOutputResource($assistant->generate($this->tenant(), $this->user(), $clientUser, $data)));
    }

    public function ask(Request $request, BusinessAssistant $assistant): JsonResponse
    {
        $data = $request->validate(['question' => ['required', 'string', 'max:500']]);

        return $this->ok($assistant->ask($this->tenant(), $this->user(), $data['question']));
    }

    public function content(Request $request, BusinessAssistant $assistant): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(Schemas::CONTENT_TYPES)],
            'topic' => ['required_without:program_id', 'nullable', 'string', 'max:500'],
            'program_id' => ['nullable', 'string', 'max:26'],
            'language' => ['nullable', Rule::in(Schemas::LANGUAGES)],
        ]);

        return $this->created(new AiOutputResource($assistant->content($this->tenant(), $this->user(), $data)));
    }

    public function churn(Request $request, ChurnAdvisor $advisor): JsonResponse
    {
        $data = $request->validate([
            'suggest' => ['nullable', 'boolean'],
            'level' => ['nullable', Rule::in(['green', 'amber', 'red'])],
        ]);
        $result = $advisor->list($this->tenant(), $this->user(), (bool) ($data['suggest'] ?? true), $data['level'] ?? null);

        return $this->ok($result['clients'], ['weights' => $result['weights'], 'ai_output_id' => $result['ai_output_id']]);
    }
}
