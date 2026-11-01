<?php

namespace App\Domain\Community\Services;

use App\Domain\Community\Models\Comment;
use App\Domain\Community\Models\CommunitySpace;
use App\Domain\Community\Models\PollVote;
use App\Domain\Community\Models\Post;
use App\Domain\Community\Models\Reaction;
use App\Domain\Identity\Models\User;
use App\Domain\Messaging\Services\Notifier;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Workouts\Models\ProgramAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/** Spaces visibility, reactions, poll votes and post presentation for the community feed. */
final class CommunityService
{
    public function __construct(private readonly Notifier $notifier) {}

    /** @param  Builder<CommunitySpace>  $query */
    public function visibleSpaces(Builder $query, User $user): Builder
    {
        if ($user->isStaffIn()) {
            return $query;
        }
        $groupIds = DB::table('client_group_members')->where('user_id', $user->id)->pluck('client_group_id')->all();
        $programIds = ProgramAssignment::query()->where('client_id', $user->id)->whereIn('status', ['active', 'scheduled', 'paused'])->pluck('program_id')->all();

        return $query->where(function (Builder $q) use ($groupIds, $programIds) {
            $q->where(fn (Builder $open) => $open->whereNull('client_group_id')->whereNull('program_id'))
                ->orWhereIn('client_group_id', $groupIds)
                ->orWhereIn('program_id', $programIds);
        });
    }

    public function canView(CommunitySpace $space, User $user): bool
    {
        return $this->visibleSpaces(CommunitySpace::query()->whereKey($space->id), $user)->exists();
    }

    public function canPost(CommunitySpace $space, User $user): bool
    {
        return ! $space->announcement_only || $user->isStaffIn();
    }

    public function isModerator(User $user): bool
    {
        return $user->isOwnerIn() || ($user->isStaffIn() && $user->can('community.moderate'));
    }

    /** Toggles the viewer's reaction (one per user per item; a different emoji replaces it). */
    public function toggleReaction(Post|Comment $item, User $user, string $emoji): void
    {
        DB::transaction(function () use ($item, $user, $emoji) {
            $existing = Reaction::query()->where('reactable_type', $item->getMorphClass())->where('reactable_id', $item->getKey())->where('user_id', $user->id)->lockForUpdate()->first();
            if ($existing && $existing->emoji === $emoji) {
                $existing->delete();
            } elseif ($existing) {
                $existing->update(['emoji' => $emoji]);
            } else {
                try {
                    DB::transaction(fn () => Reaction::create(['reactable_type' => $item->getMorphClass(), 'reactable_id' => $item->getKey(), 'user_id' => $user->id, 'emoji' => $emoji]));
                } catch (UniqueConstraintViolationException) {
                    // Concurrent duplicate: already reacted.
                }
            }
            if ($item instanceof Post) {
                $item->forceFill(['reactions_count' => Reaction::query()->where('reactable_type', 'post')->where('reactable_id', $item->id)->count()])->save();
            }
        });
    }

    public function vote(Post $post, User $user, int $option): void
    {
        $options = $post->meta['poll']['options'] ?? [];
        if ($post->type !== 'poll' || ! array_key_exists($option, $options)) {
            throw ApiException::invalid('validation_failed', __('community_module.errors.invalid_option'), ['option' => [__('community_module.errors.invalid_option')]]);
        }
        if (! empty($post->meta['poll']['closes_at']) && now()->greaterThan($post->meta['poll']['closes_at'])) {
            throw ApiException::conflict('poll_closed', __('community_module.errors.poll_closed'));
        }
        try {
            PollVote::query()->updateOrCreate(['post_id' => $post->id, 'user_id' => $user->id], ['option' => $option]);
        } catch (UniqueConstraintViolationException) {
            PollVote::query()->where('post_id', $post->id)->where('user_id', $user->id)->update(['option' => $option]);
        }
    }

    public function addComment(Post $post, User $author, string $body): Comment
    {
        $comment = DB::transaction(function () use ($post, $author, $body) {
            $comment = Comment::create(['post_id' => $post->id, 'author_id' => $author->id, 'body' => $body]);
            $post->forceFill(['comments_count' => Comment::query()->where('post_id', $post->id)->count()])->save();

            return $comment;
        });

        $post->loadMissing('author');
        if ($post->author && $post->author_id !== $author->id) {
            $this->notifier->send($post->author, 'community', 'community_module.notifications.comment.title', 'community_module.notifications.comment.body', [
                'name' => $author->name,
            ], '/community/posts/'.$post->id, ['post_id' => $post->id, 'comment_id' => $comment->id]);
        }

        return $comment->load('author');
    }

    /**
     * Attaches viewer-specific data (my_reaction, poll votes) to posts.
     *
     * @param  Collection<int, Post>  $posts
     */
    public function present(Collection $posts, User $viewer): void
    {
        if ($posts->isEmpty()) {
            return;
        }
        $posts->loadMissing('author');
        $ids = $posts->modelKeys();
        $mine = Reaction::query()->where('reactable_type', 'post')->whereIn('reactable_id', $ids)->where('user_id', $viewer->id)->pluck('emoji', 'reactable_id');
        $pollIds = $posts->where('type', 'poll')->modelKeys();
        $votes = $pollIds === [] ? collect() : PollVote::query()->whereIn('post_id', $pollIds)->get(['post_id', 'user_id', 'option'])->groupBy('post_id');
        $moderator = $this->isModerator($viewer);

        foreach ($posts as $post) {
            $postVotes = $votes->get($post->id, collect());
            $post->presentation = [
                'my_reaction' => $mine[$post->id] ?? null,
                'vote_counts' => $postVotes->countBy('option')->all(),
                'my_vote' => $postVotes->firstWhere('user_id', $viewer->id)?->option,
                'total_votes' => $postVotes->count(),
                'can_delete' => $moderator || $post->author_id === $viewer->id,
            ];
        }
    }
}
