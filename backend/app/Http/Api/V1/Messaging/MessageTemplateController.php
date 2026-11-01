<?php

namespace App\Http\Api\V1\Messaging;

use App\Domain\Messaging\Models\MessageTemplate;
use App\Domain\Messaging\Services\TemplateRenderer;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Messaging\MessageTemplateResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Saved replies / templates with {first_name}, {program}, ... variables in up to 4 languages. */
class MessageTemplateController extends ApiController
{
    public function __construct(private readonly TemplateRenderer $renderer) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $templates = MessageTemplate::query()
            ->when($request->input('q'), fn ($q, $term) => $q->where('name', 'like', '%'.$term.'%'))
            ->orderBy('name')->get();

        return $this->ok(MessageTemplateResource::collection($templates)->resolve($request), ['variables' => TemplateRenderer::VARIABLES]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, true);

        return $this->created(new MessageTemplateResource(MessageTemplate::create($data)));
    }

    public function update(Request $request, MessageTemplate $messageTemplate): JsonResponse
    {
        $data = $this->validated($request, false);
        if (isset($data['body'])) {
            $data['body'] = array_merge($messageTemplate->body ?? [], $data['body']);
        }
        $messageTemplate->update($data);

        return $this->ok(new MessageTemplateResource($messageTemplate));
    }

    public function destroy(MessageTemplate $messageTemplate): JsonResponse
    {
        $messageTemplate->delete();

        return $this->noContent();
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, bool $creating): array
    {
        $data = $request->validate([
            'name' => [$creating ? 'required' : 'sometimes', 'string', 'max:120'],
            'body' => [$creating ? 'required' : 'sometimes'],
            'body.*' => ['nullable', 'string', 'max:5000'],
        ]);
        if (array_key_exists('body', $data)) {
            if (! is_string($data['body']) && ! is_array($data['body'])) {
                throw ApiException::invalid('validation_failed', __('messaging.errors.body_required'), ['body' => [__('messaging.errors.body_required')]]);
            }
            if (is_string($data['body']) && mb_strlen($data['body']) > 5000) {
                throw ApiException::invalid('validation_failed', __('messaging.errors.body_too_long'), ['body' => [__('messaging.errors.body_too_long')]]);
            }
            $data['body'] = $this->renderer->normalize($data['body'], app()->getLocale());
            if ($data['body'] === []) {
                throw ApiException::invalid('validation_failed', __('messaging.errors.body_required'), ['body' => [__('messaging.errors.body_required')]]);
            }
        }

        return $data;
    }
}
