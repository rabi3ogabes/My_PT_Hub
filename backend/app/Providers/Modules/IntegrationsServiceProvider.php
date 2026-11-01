<?php

namespace App\Providers\Modules;

use App\Domain\Crm\Models\Lead;
use App\Domain\Integrations\Jobs\PushCalendarEventJob;
use App\Domain\Integrations\Jobs\RemoveCalendarEventsJob;
use App\Domain\Integrations\Listeners\ForwardDomainEventToWebhooks;
use App\Domain\Integrations\Models\CalendarEventLink;
use App\Domain\Integrations\Models\Integration;
use App\Domain\Integrations\Models\WebhookEndpoint;
use App\Domain\Integrations\Services\CalendarSyncService;
use App\Domain\Integrations\Services\MeetingService;
use App\Domain\Integrations\Services\ProviderRegistry;
use App\Domain\Marketing\Models\Review;
use App\Domain\Marketing\Services\ReviewService;
use App\Http\Middleware\EnforceApiTokenScopes;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Integrations & marketplace module: provider registry, outgoing webhook
 * subscriptions, calendar mirroring of platform events, review aggregates
 * and personal-access-token scope enforcement.
 */
class IntegrationsServiceProvider extends ServiceProvider
{
    private const CALENDAR_EVENT = 'App\\Domain\\Calendar\\Models\\Event';

    /** Event attributes whose change must be mirrored to external calendars. */
    private const MIRRORED = ['title', 'description', 'starts_at', 'ends_at', 'timezone', 'status', 'meeting_url', 'deleted_at'];

    public function register(): void
    {
        $this->app->singleton(ProviderRegistry::class);
    }

    public function boot(): void
    {
        Relation::morphMap([
            'integration' => Integration::class,
            'webhook_endpoint' => WebhookEndpoint::class,
            'review' => Review::class,
            'lead' => Lead::class,
        ]);

        foreach (array_keys(ForwardDomainEventToWebhooks::MAP) as $eventClass) {
            if (class_exists($eventClass)) {
                Event::listen($eventClass, [ForwardDomainEventToWebhooks::class, 'handle']);
            }
        }

        Review::saved(function (Review $review) {
            if ($review->wasRecentlyCreated || $review->wasChanged(['status', 'rating', 'is_verified'])) {
                app(ReviewService::class)->refreshAggregate($review->coach_profile_id);
            }
        });
        Review::deleted(fn (Review $review) => app(ReviewService::class)->refreshAggregate($review->coach_profile_id));

        $this->mirrorCalendarEvents();

        $this->app->make(Router::class)->pushMiddlewareToGroup('api', EnforceApiTokenScopes::class);
    }

    private function mirrorCalendarEvents(): void
    {
        if (! class_exists(self::CALENDAR_EVENT)) {
            return;
        }
        /** @var class-string<\App\Domain\Calendar\Models\Event> $model */
        $model = self::CALENDAR_EVENT;

        $queue = function ($event): void {
            if (app(CalendarSyncService::class)->coachHasCalendar($event->coach_id) || app(MeetingService::class)->wantsMeeting($event)) {
                PushCalendarEventJob::dispatch($event->id)->afterCommit();
            }
        };

        $model::created($queue);
        $model::updated(function ($event) use ($queue) {
            if ($event->wasChanged(self::MIRRORED)) {
                $queue($event);
            }
        });
        $model::deleting(function ($event) {
            $links = CalendarEventLink::query()->where('event_id', $event->id)->get(['integration_id', 'external_id']);
            if ($links->isNotEmpty()) {
                RemoveCalendarEventsJob::dispatch($links->map(fn ($l) => ['integration_id' => $l->integration_id, 'external_id' => $l->external_id])->all())->afterCommit();
            }
        });
    }
}
