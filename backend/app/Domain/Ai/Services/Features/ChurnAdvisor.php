<?php

namespace App\Domain\Ai\Services\Features;

use App\Domain\Ai\Data\AiTask;
use App\Domain\Ai\Services\AiGateway;
use App\Domain\Ai\Services\AiOutputService;
use App\Domain\Ai\Services\AiSettingsService;
use App\Domain\Ai\Services\ChurnScorer;
use App\Domain\Ai\Services\SafetyGuard;
use App\Domain\Ai\Services\ToneEnforcer;
use App\Domain\Ai\Support\Schemas;
use App\Domain\Ai\Support\TextNormalizer;
use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * G. Churn prediction & re-engagement: scores every active client with the
 * transparent ChurnScorer (recency, frequency, compliance, check-in
 * sentiment, payment status, response time), then drafts a re-engagement
 * message per at-risk client (AI when available, translated template
 * otherwise). Messages are drafts — never sent automatically.
 */
final class ChurnAdvisor
{
    private const NEGATIVE_WORDS = [
        'tired', 'exhausted', 'stressed', 'unmotivated', 'demotivated', 'struggling', 'hard week', 'bad week', 'gave up', 'no time', 'frustrated', 'overwhelmed', 'bored',
        'fatigue', 'epuise', 'stresse', 'demotive', 'difficile', 'mauvaise semaine', 'pas le temps', 'decourage', 'abandonne',
        'cansado', 'cansada', 'agotado', 'estresado', 'desmotivado', 'mala semana', 'sin tiempo', 'frustrado', 'abrumado',
        'تعبان', 'متعب', 'مرهق', 'مضغوط', 'محبط', 'اسبوع صعب', 'ما عندي وقت', 'زهقت', 'ملل',
    ];

    public function __construct(
        private readonly AiGateway $gateway,
        private readonly AiOutputService $outputs,
        private readonly AiSettingsService $settings,
        private readonly ToneEnforcer $tone,
        private readonly ChurnScorer $scorer,
        private readonly SafetyGuard $guard,
    ) {}

    /** @return array{clients: list<array<string, mixed>>, weights: array<string, float>, ai_output_id: string|null} */
    public function list(Tenant $tenant, User $coach, bool $suggest = true, ?string $level = null): array
    {
        $rows = collect($this->scores($tenant))
            ->sortByDesc('score')
            ->when($level, fn ($c) => $c->where('level', $level))
            ->values();

        $atRisk = $rows->whereIn('level', ['red', 'amber'])->take((int) config('ai.churn.suggest_limit', 10))->values();
        $messages = [];
        $outputId = null;
        $source = 'template';

        if ($suggest && $atRisk->isNotEmpty()) {
            try {
                $voice = $this->settings->forUser($tenant, $coach);
                $clients = $atRisk->map(fn ($r) => [
                    'client_key' => $r['client']['id'],
                    'first_name' => $r['first_name'],
                    'language' => $r['language'],
                    'level' => $r['level'],
                    'reasons' => $r['reasons'],
                ])->all();
                $keys = array_column($clients, 'client_key');

                $result = $this->gateway->run(new AiTask(
                    feature: 'churn',
                    tenant: $tenant,
                    user: $coach,
                    variables: ['clients' => $clients, ...$this->tone->variables($voice)],
                    context: ['clients' => $clients],
                    schema: Schemas::churnMessages(),
                    postProcess: function (array $data) use ($keys, $voice) {
                        $errors = [];
                        foreach ($data['messages'] as $i => $m) {
                            if (! in_array($m['client_key'], $keys, true)) {
                                $errors[] = "messages[{$i}].client_key '{$m['client_key']}' is unknown";
                            }
                            if ($banned = $this->tone->bannedWordsIn($m['message'], $voice)) {
                                $errors[] = "messages[{$i}] contains banned words: ".implode(', ', $banned);
                            }
                        }

                        return [$data, $errors];
                    },
                ));
                foreach ($result->data['messages'] as $m) {
                    $messages[$m['client_key']] = $this->tone->polish($m['message'], $voice);
                }
                $source = 'ai';
                $outputId = $this->outputs->draft(
                    'churn',
                    $coach,
                    null,
                    ['clients' => $keys],
                    ['messages' => $result->data['messages']],
                    ['clients_scored' => $rows->count(), 'label' => __('ai.grounding_churn', ['count' => $rows->count()])],
                    [],
                    $result->requestId,
                )->id;
            } catch (ApiException $e) {
                // Graceful degradation: AI disabled, capped or unavailable -> translated templates.
                $messages = [];
            }
        }

        $clients = $rows->map(function (array $r) use ($messages, $source) {
            $suggested = null;
            if (in_array($r['level'], ['red', 'amber'], true)) {
                $suggested = $messages[$r['client']['id']] ?? __('ai.churn.fallback_message', ['name' => $r['first_name']], $r['language']);
            }

            return [
                'client' => $r['client'],
                'score' => $r['score'],
                'level' => $r['level'],
                'reasons' => array_map(fn (string $f) => ['factor' => $f, 'text' => __("ai.churn.reason.{$f}")], $r['reasons']),
                'factors' => $r['factors'],
                'last_active_at' => $r['last_active_at'],
                'suggested_message' => $suggested,
                'suggestion_source' => $suggested === null ? null : (isset($messages[$r['client']['id']]) ? $source : 'template'),
            ];
        })->all();

        return ['clients' => $clients, 'weights' => ChurnScorer::WEIGHTS, 'ai_output_id' => $outputId];
    }

    /**
     * Scores every active client of the tenant (no model call).
     *
     * @return list<array<string, mixed>>
     */
    public function scores(Tenant $tenant): array
    {
        $profiles = ClientProfile::query()->with('user:id,name,locale')->where('tenant_id', $tenant->id)->where('status', 'active')->get();
        $ids = $profiles->pluck('user_id')->all();
        if ($ids === []) {
            return [];
        }
        $now = now();

        $lastSession = DB::table('workout_sessions')->where('tenant_id', $tenant->id)->whereIn('client_id', $ids)->whereNull('deleted_at')
            ->selectRaw('client_id, MAX(started_at) as agg')->groupBy('client_id')->pluck('agg', 'client_id');
        $lastFood = DB::table('food_logs')->where('tenant_id', $tenant->id)->whereIn('client_id', $ids)->whereNull('deleted_at')
            ->selectRaw('client_id, MAX(logged_at) as agg')->groupBy('client_id')->pluck('agg', 'client_id');
        $lastCheckin = DB::table('checkin_responses')->where('tenant_id', $tenant->id)->whereIn('client_id', $ids)->whereNotNull('submitted_at')
            ->selectRaw('client_id, MAX(submitted_at) as agg')->groupBy('client_id')->pluck('agg', 'client_id');
        $sessions14 = DB::table('workout_sessions')->where('tenant_id', $tenant->id)->whereIn('client_id', $ids)->whereNull('deleted_at')->where('status', 'completed')
            ->where('started_at', '>=', $now->copy()->subDays(14))->selectRaw('client_id, COUNT(*) as agg')->groupBy('client_id')->pluck('agg', 'client_id');
        $sessionsPrev = DB::table('workout_sessions')->where('tenant_id', $tenant->id)->whereIn('client_id', $ids)->whereNull('deleted_at')->where('status', 'completed')
            ->whereBetween('started_at', [$now->copy()->subDays(28), $now->copy()->subDays(14)])->selectRaw('client_id, COUNT(*) as agg')->groupBy('client_id')->pluck('agg', 'client_id');
        $overdue = DB::table('checkin_responses')->where('tenant_id', $tenant->id)->whereIn('client_id', $ids)
            ->where(fn ($q) => $q->where('status', 'overdue')->orWhere(fn ($q2) => $q2->where('status', 'pending')->where('due_on', '<', $now->toDateString())))
            ->selectRaw('client_id, COUNT(*) as agg')->groupBy('client_id')->pluck('agg', 'client_id');
        $subscriptions = DB::table('client_subscriptions')->where('tenant_id', $tenant->id)->whereIn('client_id', $ids)
            ->orderBy('created_at')->get(['client_id', 'status', 'cancel_at_period_end'])->keyBy('client_id');
        $failedOrders = DB::table('orders')->where('tenant_id', $tenant->id)->whereIn('client_id', $ids)->where('status', 'failed')
            ->where('created_at', '>=', $now->copy()->subDays(30))->pluck('client_id')->unique()->all();

        $out = [];
        foreach ($profiles as $profile) {
            $user = $profile->user;
            if (! $user) {
                continue;
            }
            $id = $user->id;
            $last = collect([$profile->last_active_at, $lastSession[$id] ?? null, $lastFood[$id] ?? null, $lastCheckin[$id] ?? null])
                ->filter()->map(fn ($v) => Carbon::parse($v))->max();
            $sub = $subscriptions[$id] ?? null;
            $payment = match (true) {
                $sub && in_array($sub->status, ['past_due'], true) => 'past_due',
                in_array($id, $failedOrders, true) => 'failed',
                $sub && (bool) $sub->cancel_at_period_end => 'cancelling',
                $sub && in_array($sub->status, ['active', 'trialing'], true) => 'ok',
                default => 'none',
            };
            [$unanswered, $avgReply] = $this->responseSignals($tenant, $id);

            $scored = $this->scorer->score([
                'days_since_active' => $last ? (int) $last->diffInDays($now) : null,
                'sessions_last_14' => (int) ($sessions14[$id] ?? 0),
                'sessions_prev_14' => (int) ($sessionsPrev[$id] ?? 0),
                'compliance_score' => $profile->compliance_score !== null ? (int) $profile->compliance_score : null,
                'compliance_trend' => null,
                'negative_sentiment' => $this->sentiment($tenant, $id),
                'overdue_checkins' => (int) ($overdue[$id] ?? 0),
                'payment_status' => $payment,
                'unanswered_coach_messages' => $unanswered,
                'avg_reply_hours' => $avgReply,
            ]);

            $out[] = $scored + [
                'client' => ['id' => $id, 'name' => $user->name],
                'first_name' => $user->firstName(),
                'language' => in_array($user->locale, ['en', 'ar', 'fr', 'es'], true) ? $user->locale : ($tenant->locale ?: 'en'),
                'last_active_at' => $last?->toIso8601String(),
            ];
        }

        return $out;
    }

    /** Negative sentiment in the latest submitted check-in, in [0, 1]. */
    private function sentiment(Tenant $tenant, string $clientId): float
    {
        $latest = CheckinResponse::query()->where('tenant_id', $tenant->id)->where('client_id', $clientId)->whereNotNull('submitted_at')->latest('submitted_at')->first(['answers']);
        if (! $latest) {
            return 0.0;
        }
        $text = collect($latest->answers ?? [])->filter(fn ($a) => is_string($a))->implode(' ');
        if ($this->guard->screenText($text)->refuse) {
            return 1.0;
        }
        $normalized = TextNormalizer::normalize($text);
        $hits = 0;
        foreach (self::NEGATIVE_WORDS as $word) {
            if (str_contains($normalized, TextNormalizer::normalize($word))) {
                $hits++;
            }
        }
        foreach ($latest->answers ?? [] as $answer) {
            // Low scale answers (1-3 out of 10) also signal a bad week.
            if (is_numeric($answer) && (float) $answer <= 3 && (float) $answer >= 1) {
                $hits++;
            }
        }

        return min(1.0, $hits / 3);
    }

    /**
     * Coach messages the client has not answered, and the client's average
     * reply delay, over the last 30 days of direct conversations.
     *
     * @return array{0: int, 1: float|null}
     */
    private function responseSignals(Tenant $tenant, string $clientId): array
    {
        $conversationIds = DB::table('conversations')
            ->join('conversation_participants', 'conversation_participants.conversation_id', '=', 'conversations.id')
            ->where('conversations.tenant_id', $tenant->id)
            ->where('conversation_participants.user_id', $clientId)
            ->whereIn('conversations.type', ['direct', 'checkin'])
            ->pluck('conversations.id');
        if ($conversationIds->isEmpty()) {
            return [0, null];
        }
        $messages = DB::table('messages')->whereIn('conversation_id', $conversationIds)->whereNull('deleted_at')
            ->where('created_at', '>=', now()->subDays(30))->where('type', '!=', 'system')
            ->orderBy('created_at')->get(['sender_id', 'created_at']);

        $unanswered = 0;
        $pendingSince = null;
        $delays = [];
        foreach ($messages as $m) {
            if ($m->sender_id === $clientId) {
                if ($pendingSince) {
                    $delays[] = Carbon::parse($pendingSince)->diffInMinutes(Carbon::parse($m->created_at)) / 60;
                }
                $unanswered = 0;
                $pendingSince = null;
            } else {
                $unanswered++;
                $pendingSince ??= $m->created_at;
            }
        }

        return [$unanswered, $delays === [] ? null : round(array_sum($delays) / count($delays), 1)];
    }
}
