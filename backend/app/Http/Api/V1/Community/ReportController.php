<?php

namespace App\Http\Api\V1\Community;

use App\Domain\Community\Models\Comment;
use App\Domain\Community\Models\CommunitySpace;
use App\Domain\Community\Models\ContentReport;
use App\Domain\Community\Models\Post;
use App\Domain\Community\Services\CommunityService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Community\ReportResource;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends ApiController
{
    public const REASONS = ['spam', 'harassment', 'hate', 'nudity', 'violence', 'misinformation', 'self_harm', 'other'];

    public function __construct(private readonly CommunityService $community) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['post', 'comment'])],
            'id' => ['required', 'string', 'size:26'],
            'reason' => ['required', Rule::in(self::REASONS)],
        ]);
        $item = $data['type'] === 'post' ? Post::query()->find($data['id']) : Comment::query()->find($data['id']);
        $postId = $item instanceof Comment ? $item->post_id : $item?->id;
        $post = $postId ? Post::query()->find($postId) : null;
        $space = $post ? CommunitySpace::query()->find($post->community_space_id) : null;
        if (! $item || ! $space || ! $this->community->canView($space, $this->user())) {
            throw new ApiException('not_found', __('community_module.errors.post_not_found'), 404);
        }

        $report = ContentReport::query()->firstOrCreate(
            ['reportable_type' => $item->getMorphClass(), 'reportable_id' => $item->getKey(), 'reporter_id' => $this->user()->id, 'status' => 'open'],
            ['reason' => $data['reason']],
        );

        return $this->created(['id' => $report->id, 'status' => $report->status]);
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate(['filter.status' => ['nullable', Rule::in(['open', 'resolved', 'dismissed'])]]);
        $this->assertModerator();
        $paginator = ContentReport::query()
            ->with(['reporter', 'reportable' => fn (MorphTo $m) => $m->constrain([
                Post::class => fn ($q) => $q->withTrashed()->with('author'),
                Comment::class => fn ($q) => $q->withTrashed()->with('author'),
            ])])
            ->where('status', $request->input('filter.status', 'open'))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->cursorPaginate($this->perPage());

        return $this->paginated($paginator, ReportResource::class);
    }

    /** Resolve a report: dismiss it, hide the content, or delete the content. */
    public function resolve(Request $request, ContentReport $report): JsonResponse
    {
        $data = $request->validate(['action' => ['required', Rule::in(['dismiss', 'hide', 'delete'])]]);
        $this->assertModerator();

        $item = $report->reportable_type === 'post'
            ? Post::withTrashed()->find($report->reportable_id)
            : Comment::withTrashed()->find($report->reportable_id);
        if ($item && $data['action'] === 'hide') {
            $item->forceFill(['is_hidden' => true])->save();
        } elseif ($item && $data['action'] === 'delete') {
            $item->delete();
        }

        // Every open report about the same content is closed together.
        ContentReport::query()->where('reportable_type', $report->reportable_type)->where('reportable_id', $report->reportable_id)->where('status', 'open')
            ->update(['status' => $data['action'] === 'dismiss' ? 'dismissed' : 'resolved']);

        $report->refresh()->load(['reporter', 'reportable' => fn (MorphTo $m) => $m->constrain([
            Post::class => fn ($q) => $q->withTrashed()->with('author'),
            Comment::class => fn ($q) => $q->withTrashed()->with('author'),
        ])]);

        return $this->ok(new ReportResource($report));
    }

    private function assertModerator(): void
    {
        if (! $this->community->isModerator($this->user())) {
            throw ApiException::forbidden(__('community_module.errors.moderators_only'));
        }
    }
}
