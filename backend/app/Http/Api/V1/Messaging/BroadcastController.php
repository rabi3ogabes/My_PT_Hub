<?php

namespace App\Http\Api\V1\Messaging;

use App\Domain\Identity\Models\User;
use App\Domain\Messaging\Models\MessageTemplate;
use App\Domain\Messaging\Models\ScheduledMessage;
use App\Domain\Messaging\Services\BroadcastService;
use App\Domain\Messaging\Services\SegmentService;
use App\Domain\Messaging\Services\TemplateRenderer;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Messaging\ScheduledMessageResource;
use App\Http\Api\V1\Resources\Messaging\UserSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class BroadcastController extends ApiController
{
    public function __construct(
        private readonly BroadcastService $broadcasts,
        private readonly SegmentService $segments,
        private readonly TemplateRenderer $renderer,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate(['filter.status' => ['nullable', Rule::in(['scheduled', 'sent'])]]);
        $paginator = ScheduledMessage::query()->with('sender')
            ->when($request->input('filter.status') === 'scheduled', fn ($q) => $q->whereNull('sent_at'))
            ->when($request->input('filter.status') === 'sent', fn ($q) => $q->whereNotNull('sent_at'))
            ->orderByDesc('send_at')->orderByDesc('id')
            ->cursorPaginate($this->perPage());

        return $this->paginated($paginator, ScheduledMessageResource::class);
    }

    /** Segment preview: how many clients (and who) a broadcast would reach. */
    public function preview(Request $request): JsonResponse
    {
        $data = $request->validate($this->segmentRules());
        $recipients = $this->segments->resolve($this->tenant(), $data['segment'] ?? []);

        return $this->ok([
            'count' => $recipients->count(),
            'recipients' => $recipients->take(50)->map(fn (User $u) => UserSummary::make($u))->values()->all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->assertCanBroadcast();
        $data = $request->validate($this->segmentRules() + [
            'body' => ['required_without:template_id'],
            'template_id' => ['nullable', 'string', 'size:26'],
            'send_at' => ['nullable', 'date'],
        ]);

        if (! empty($data['template_id'])) {
            $body = MessageTemplate::query()->findOrFail($data['template_id'])->body ?? [];
        } else {
            if (! is_string($data['body']) && ! is_array($data['body'])) {
                throw ApiException::invalid('validation_failed', __('messaging.errors.body_required'), ['body' => [__('messaging.errors.body_required')]]);
            }
            $body = $this->renderer->normalize($data['body'], app()->getLocale());
        }
        if ($body === []) {
            throw ApiException::invalid('validation_failed', __('messaging.errors.body_required'), ['body' => [__('messaging.errors.body_required')]]);
        }

        $sendAt = isset($data['send_at']) ? Carbon::parse($data['send_at'])->utc() : null;
        $scheduled = $this->broadcasts->schedule($this->tenant(), $this->user(), $data['segment'] ?? [], $body, $sendAt);

        return $this->created(new ScheduledMessageResource($scheduled->load('sender')));
    }

    public function destroy(ScheduledMessage $scheduledMessage): JsonResponse
    {
        $this->assertCanBroadcast();
        if ($scheduledMessage->sent_at !== null) {
            throw ApiException::conflict('already_sent', __('messaging.errors.broadcast_already_sent'));
        }
        $scheduledMessage->delete();

        return $this->noContent();
    }

    /** @return array<string, mixed> */
    private function segmentRules(): array
    {
        return [
            'segment' => ['nullable', 'array'],
            'segment.client_ids' => ['nullable', 'array', 'max:5000'],
            'segment.client_ids.*' => ['string', 'size:26'],
            'segment.tags' => ['nullable', 'array', 'max:50'],
            'segment.tags.*' => ['string', 'max:64'],
            'segment.group_ids' => ['nullable', 'array', 'max:100'],
            'segment.group_ids.*' => ['string', 'size:26'],
            'segment.program_id' => ['nullable', 'string', 'size:26'],
            'segment.at_risk' => ['nullable', 'boolean'],
        ];
    }

    private function assertCanBroadcast(): void
    {
        $user = $this->user();
        if (! $user->isOwnerIn() && ! $user->can('messages.broadcast')) {
            throw ApiException::forbidden(__('messaging.errors.broadcast_forbidden'));
        }
    }
}
