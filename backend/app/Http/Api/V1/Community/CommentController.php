<?php

namespace App\Http\Api\V1\Community;

use App\Domain\Community\Models\Comment;
use App\Domain\Community\Models\CommunitySpace;
use App\Domain\Community\Models\Post;
use App\Domain\Community\Services\CommunityService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Community\CommentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends ApiController
{
    public function __construct(private readonly CommunityService $community) {}

    /** Comments, oldest first (cursor). */
    public function index(Post $post): JsonResponse
    {
        $this->assertVisible($post);
        $moderator = $this->community->isModerator($this->user());
        $paginator = Comment::query()->where('post_id', $post->id)
            ->when(! $moderator, fn ($q) => $q->where('is_hidden', false))
            ->with('author')
            ->orderBy('created_at')->orderBy('id')
            ->cursorPaginate($this->perPage(30));

        return $this->paginated($paginator, CommentResource::class);
    }

    public function store(Request $request, Post $post): JsonResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $this->assertVisible($post);

        return $this->created(new CommentResource($this->community->addComment($post, $this->user(), $data['body'])));
    }

    private function assertVisible(Post $post): void
    {
        $space = CommunitySpace::query()->find($post->community_space_id);
        if (! $space || ! $this->community->canView($space, $this->user()) || ($post->is_hidden && ! $this->community->isModerator($this->user()))) {
            throw new ApiException('not_found', __('community_module.errors.post_not_found'), 404);
        }
    }
}
