<?php

namespace App\Http\Api\V1\Messaging;

use App\Domain\Messaging\Models\Conversation;
use App\Domain\Messaging\Models\Message;
use App\Domain\Messaging\Models\MessageAttachment;
use App\Domain\Messaging\Services\MessagingService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Messaging\MessageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MessageController extends ApiController
{
    /** Allowed upload mime types per message type. */
    private const MIMES = [
        'image' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/heic', 'image/heif'],
        'video' => ['video/mp4', 'video/quicktime', 'video/webm', 'video/3gpp', 'video/x-m4v'],
        'voice' => ['audio/aac', 'audio/x-aac', 'audio/mp4', 'audio/x-m4a', 'audio/m4a', 'audio/mpeg', 'audio/ogg', 'audio/opus', 'audio/webm', 'audio/wav', 'audio/x-wav', 'video/mp4', 'video/webm', 'application/octet-stream'],
    ];

    public function __construct(private readonly MessagingService $messaging) {}

    /** History, newest first (cursor). Deleted messages are returned as tombstones. */
    public function index(Conversation $conversation): JsonResponse
    {
        $this->authorizeParticipant($conversation);
        $this->messaging->touchPresence($conversation->id, $this->user()->id);

        $paginator = Message::withTrashed()
            ->where('conversation_id', $conversation->id)
            ->with(['sender', 'attachments', 'reactions', 'replyTo' => fn ($q) => $q->withTrashed()->with('sender')])
            ->orderByDesc('created_at')->orderByDesc('id')
            ->cursorPaginate($this->perPage(50));

        return $this->paginated($paginator, MessageResource::class);
    }

    /** JSON text/share message or multipart upload (`file`). Idempotent on `id`. */
    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeParticipant($conversation);

        if (is_string($request->input('meta'))) {
            $decoded = json_decode((string) $request->input('meta'), true);
            $request->merge(['meta' => is_array($decoded) ? $decoded : []]);
        }
        if (is_string($request->input('meta.waveform'))) {
            $decoded = json_decode((string) $request->input('meta.waveform'), true);
            $request->merge(['meta' => array_merge((array) $request->input('meta'), ['waveform' => is_array($decoded) ? $decoded : []])]);
        }

        $hasFile = $request->hasFile('file');
        $data = $request->validate([
            'id' => ['nullable', 'string', 'size:26', 'regex:/^[0-9A-HJKMNP-TV-Za-hjkmnp-tv-z]{26}$/'],
            'type' => ['nullable', Rule::in($hasFile ? MessagingService::FILE_TYPES : ['text', 'share'])],
            'body' => [$hasFile ? 'nullable' : 'required_unless:type,share', 'nullable', 'string', 'max:5000'],
            'reply_to_id' => ['nullable', 'string', 'size:26'],
            'meta' => ['nullable', 'array'],
            'meta.duration_ms' => ['nullable', 'integer', 'min:0', 'max:3600000'],
            'meta.waveform' => ['nullable', 'array', 'max:1024'],
            'meta.waveform.*' => ['numeric', 'min:0', 'max:1000'],
            'meta.kind' => ['nullable', Rule::in(['workout', 'meal', 'habit', 'program', 'recipe', 'exercise'])],
            'meta.ref_id' => ['nullable', 'string', 'max:64'],
            'file' => ['nullable', 'file', 'max:102400'],
        ]);

        $type = $data['type'] ?? ($hasFile ? $this->inferType((string) $request->file('file')->getMimeType()) : 'text');
        if ($hasFile && isset(self::MIMES[$type]) && ! in_array($request->file('file')->getMimeType(), self::MIMES[$type], true)) {
            throw ApiException::invalid('validation_failed', __('messaging.errors.invalid_file'), ['file' => [__('messaging.errors.invalid_file')]]);
        }
        if ($type === 'share' && empty($data['meta']['kind'])) {
            throw ApiException::invalid('validation_failed', __('messaging.errors.share_requires_kind'), ['meta.kind' => [__('messaging.errors.share_requires_kind')]]);
        }

        $message = $this->messaging->send($conversation, $this->user(), [
            'id' => $data['id'] ?? null,
            'type' => $type,
            'body' => $data['body'] ?? null,
            'reply_to_id' => $data['reply_to_id'] ?? null,
            'meta' => $data['meta'] ?? [],
        ], $hasFile ? $request->file('file') : null);

        $payload = (new MessageResource($message))->resolve($request);

        return $message->wasRecentlyCreated ? $this->created($payload) : $this->ok($payload);
    }

    public function update(Request $request, Message $message): JsonResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        $this->authorizeMessage($message);

        return $this->ok(new MessageResource($this->messaging->edit($message, $this->user(), $data['body'])));
    }

    public function destroy(Message $message): JsonResponse
    {
        $this->authorizeMessage($message);

        return $this->ok(new MessageResource($this->messaging->delete($message, $this->user())));
    }

    public function react(Request $request, Message $message): JsonResponse
    {
        $data = $request->validate(['emoji' => ['required', 'string', 'max:16']]);
        $this->authorizeMessage($message);

        return $this->ok(new MessageResource($this->messaging->toggleReaction($message, $this->user(), $data['emoji'])));
    }

    /** Streams an attachment to participants of the conversation only. */
    public function attachment(Message $message, string $attachment): StreamedResponse
    {
        $this->authorizeMessage($message);
        /** @var MessageAttachment|null $file */
        $file = MessageAttachment::query()->where('id', $attachment)->where('message_id', $message->id)->first();
        if (! $file || ! Storage::disk($file->disk)->exists($file->path)) {
            throw new ApiException('not_found', __('messaging.errors.attachment_not_found'), 404);
        }
        $inline = str_starts_with($file->mime, 'image/') || str_starts_with($file->mime, 'audio/') || str_starts_with($file->mime, 'video/');

        return Storage::disk($file->disk)->response($file->path, basename($file->path), [
            'Content-Type' => $file->mime,
            'Cache-Control' => 'private, max-age=600',
            'X-Content-Type-Options' => 'nosniff',
        ], $inline ? 'inline' : 'attachment');
    }

    private function inferType(string $mime): string
    {
        return match (true) {
            str_starts_with($mime, 'image/') => 'image',
            str_starts_with($mime, 'video/') => 'video',
            str_starts_with($mime, 'audio/') => 'voice',
            default => 'file',
        };
    }

    private function authorizeParticipant(Conversation $conversation): void
    {
        if (! $this->messaging->isParticipant($conversation, $this->user())) {
            throw new ApiException('not_found', __('messaging.errors.conversation_not_found'), 404);
        }
    }

    /** Messages are reached through their tenant-scoped conversation; anything else is a 404. */
    private function authorizeMessage(Message $message): void
    {
        $conversation = Conversation::query()->find($message->conversation_id);
        if (! $conversation || ! $this->messaging->isParticipant($conversation, $this->user())) {
            throw new ApiException('not_found', __('messaging.errors.message_not_found'), 404);
        }
        $message->setRelation('conversation', $conversation);
    }
}
