<?php

namespace App\Http\Api\V1\Ai;

use App\Domain\Ai\Services\AiGateway;
use App\Domain\Ai\Services\Features\SelfGuidedCoach;
use App\Domain\Ai\Support\Schemas;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Self-guided AI coach for clients (chat, starter plan, weekly recap). */
class SelfGuidedController extends ApiController
{
    public function chat(Request $request, SelfGuidedCoach $coach, AiGateway $gateway): JsonResponse|StreamedResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required', Rule::in(['user', 'assistant'])],
            'history.*.content' => ['required', 'string', 'max:4000'],
            'stream' => ['nullable', 'boolean'],
        ]);
        $tenant = $this->tenant();
        $user = $this->user();

        $wantsStream = ($data['stream'] ?? false) || str_contains((string) $request->header('Accept'), 'text/event-stream');
        if (! $wantsStream) {
            return $this->ok($coach->chat($tenant, $user, $data['message'], $data['history'] ?? []));
        }

        // Gate before headers are sent so plan/opt-out errors use the normal envelope.
        $gateway->authorize('coach_chat', $tenant, $user);

        return response()->stream(function () use ($coach, $tenant, $user, $data) {
            $send = function (string $event, array $payload): void {
                echo "event: {$event}\ndata: ".json_encode($payload, JSON_UNESCAPED_UNICODE)."\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            };
            try {
                $result = $coach->chat($tenant, $user, $data['message'], $data['history'] ?? [], fn (string $delta) => $send('delta', ['text' => $delta]));
                $send('done', $result);
            } catch (ApiException $e) {
                $send('error', ['code' => $e->errorCode, 'message' => $e->getMessage()]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function starterPlan(Request $request, SelfGuidedCoach $coach): JsonResponse
    {
        $data = $request->validate([
            'goal' => ['nullable', Rule::in(Schemas::GOALS)],
            'level' => ['nullable', Rule::in(Schemas::LEVELS)],
            'days_per_week' => ['nullable', 'integer', 'between:1,7'],
            'equipment' => ['nullable', 'array', 'max:20'],
            'equipment.*' => ['string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'assign' => ['nullable', 'boolean'],
        ]);

        return $this->ok($coach->starterPlan($this->tenant(), $this->user(), $data));
    }

    public function weeklyRecap(SelfGuidedCoach $coach): JsonResponse
    {
        return $this->ok($coach->weeklyRecap($this->tenant(), $this->user()));
    }
}
