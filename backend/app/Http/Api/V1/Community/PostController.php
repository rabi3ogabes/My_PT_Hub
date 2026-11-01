<?php

namespace App\Http\Api\V1\Community;

use App\Domain\Community\Models\CommunitySpace;
use App\Domain\Community\Models\Post;
use App\Domain\Community\Services\CommunityService;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Workouts\Models\WorkoutSession;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Community\PostResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostController extends ApiController
{
    public function __construct(private readonly CommunityService $community) {}

    /** Feed: pinned posts first, then newest. Hidden posts are only visible to moderators. */
    public function index(CommunitySpace $space): JsonResponse
    {
        $this->assertCanView($space);
        $moderator = $this->community->isModerator($this->user());

        $paginator = Post::query()
            ->where('community_space_id', $space->id)
            ->when(! $moderator, fn ($q) => $q->where('is_hidden', false))
            ->with('author')
            ->orderByDesc('is_pinned')->orderByDesc('created_at')->orderByDesc('id')
            ->cursorPaginate($this->perPage(20));

        $this->community->present(new Collection($paginator->items()), $this->user());

        return $this->paginated($paginator, PostResource::class);
    }

    public function store(Request $request, CommunitySpace $space): JsonResponse
    {
        $this->assertCanView($space);
        $user = $this->user();
        if (! $this->community->canPost($space, $user)) {
            throw ApiException::forbidden(__('community_module.errors.announcement_only'));
        }

        $data = $request->validate([
            'type' => ['nullable', Rule::in(['text', 'media', 'poll', 'workout'])],
            'body' => ['nullable', 'string', 'max:5000'],
            'media' => ['nullable', 'array', 'max:4'],
            'media.*' => ['file', 'max:51200', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif,video/mp4,video/quicktime,video/webm'],
            'poll' => ['nullable', 'array'],
            'poll.options' => ['required_if:type,poll', 'nullable', 'array', 'min:2', 'max:10'],
            'poll.options.*' => ['string', 'max:100'],
            'poll.closes_at' => ['nullable', 'date', 'after:now'],
            'workout_session_id' => ['required_if:type,workout', 'nullable', 'string', 'size:26'],
        ]);
        $type = $data['type'] ?? ($request->hasFile('media') ? 'media' : 'text');
        $meta = [];

        switch ($type) {
            case 'text':
                if (trim((string) ($data['body'] ?? '')) === '') {
                    throw ApiException::invalid('validation_failed', __('community_module.errors.body_required'), ['body' => [__('community_module.errors.body_required')]]);
                }
                break;
            case 'media':
                $files = $request->file('media', []);
                if ($files === []) {
                    throw ApiException::invalid('validation_failed', __('community_module.errors.media_required'), ['media' => [__('community_module.errors.media_required')]]);
                }
                $meta['media'] = array_map(fn (UploadedFile $f) => [
                    'path' => $f->storeAs('tenants/'.$space->tenant_id.'/community', Str::ulid().'.'.strtolower($f->guessExtension() ?: $f->getClientOriginalExtension() ?: 'bin'), 'public'),
                    'mime' => (string) $f->getMimeType(),
                    'size' => (int) $f->getSize(),
                ], $files);
                break;
            case 'poll':
                $meta['poll'] = ['options' => array_values($data['poll']['options']), 'closes_at' => $data['poll']['closes_at'] ?? null];
                break;
            case 'workout':
                /** @var WorkoutSession|null $session */
                $session = WorkoutSession::query()->where('id', $data['workout_session_id'])->where('client_id', $user->id)->where('status', 'completed')->first();
                if (! $session) {
                    throw ApiException::invalid('validation_failed', __('community_module.errors.workout_not_found'), ['workout_session_id' => [__('community_module.errors.workout_not_found')]]);
                }
                $meta['workout'] = [
                    'session_id' => $session->id,
                    'name' => $session->name,
                    'duration_seconds' => $session->duration_seconds,
                    'total_volume_kg' => (float) $session->total_volume_kg,
                    'completed_at' => ($session->ended_at ?? $session->started_at)?->toIso8601String(),
                ];
                break;
        }

        $post = Post::create([
            'community_space_id' => $space->id,
            'author_id' => $user->id,
            'type' => $type,
            'body' => $data['body'] ?? null,
            'meta' => $meta === [] ? null : $meta,
        ]);

        return $this->created($this->present($post));
    }

    public function destroy(Post $post): JsonResponse
    {
        $this->spaceFor($post);
        if ($post->author_id !== $this->user()->id && ! $this->community->isModerator($this->user())) {
            throw ApiException::forbidden(__('community_module.errors.not_owner'));
        }
        $post->delete();

        return $this->noContent();
    }

    public function react(Request $request, Post $post): JsonResponse
    {
        $data = $request->validate(['emoji' => ['nullable', 'string', 'max:16']]);
        $this->spaceFor($post);
        $this->community->toggleReaction($post, $this->user(), $data['emoji'] ?? '🔥');

        return $this->ok($this->present($post->refresh()));
    }

    public function vote(Request $request, Post $post): JsonResponse
    {
        $data = $request->validate(['option' => ['required', 'integer', 'min:0', 'max:9']]);
        $this->spaceFor($post);
        $this->community->vote($post, $this->user(), (int) $data['option']);

        return $this->ok($this->present($post));
    }

    public function pin(Request $request, Post $post): JsonResponse
    {
        $data = $request->validate(['pinned' => ['nullable', 'boolean']]);
        $this->spaceFor($post);
        $this->assertModerator();
        $post->forceFill(['is_pinned' => array_key_exists('pinned', $data) && $data['pinned'] !== null ? (bool) $data['pinned'] : ! $post->is_pinned])->save();

        return $this->ok($this->present($post));
    }

    public function hide(Request $request, Post $post): JsonResponse
    {
        $data = $request->validate(['hidden' => ['nullable', 'boolean']]);
        $this->spaceFor($post);
        $this->assertModerator();
        $post->forceFill(['is_hidden' => array_key_exists('hidden', $data) && $data['hidden'] !== null ? (bool) $data['hidden'] : ! $post->is_hidden])->save();

        return $this->ok($this->present($post));
    }

    /** @return array<string, mixed> */
    private function present(Post $post): array
    {
        $this->community->present(new Collection([$post]), $this->user());

        return (new PostResource($post))->resolve(request());
    }

    /** Posts are reached through their tenant-scoped space; hidden posts only for moderators. */
    private function spaceFor(Post $post): CommunitySpace
    {
        $space = CommunitySpace::query()->find($post->community_space_id);
        if (! $space || ! $this->community->canView($space, $this->user()) || ($post->is_hidden && ! $this->community->isModerator($this->user()))) {
            throw new ApiException('not_found', __('community_module.errors.post_not_found'), 404);
        }

        return $space;
    }

    private function assertCanView(CommunitySpace $space): void
    {
        if (! $this->community->canView($space, $this->user())) {
            throw new ApiException('not_found', __('community_module.errors.space_not_found'), 404);
        }
    }

    private function assertModerator(): void
    {
        if (! $this->community->isModerator($this->user())) {
            throw ApiException::forbidden(__('community_module.errors.moderators_only'));
        }
    }
}
