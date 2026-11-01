<?php

namespace App\Providers;

use App\Domain\Billing\Gateways\LocalSaasGateway;
use App\Domain\Billing\Gateways\SaasGateway;
use App\Domain\Billing\Gateways\StripeSaasGateway;
use App\Domain\Shared\Tenancy\TenantContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);

        // Module providers (app/Providers/Modules/*ServiceProvider.php) are auto-registered.
        foreach (glob(app_path('Providers/Modules/*ServiceProvider.php')) ?: [] as $file) {
            $this->app->register('App\\Providers\\Modules\\'.basename($file, '.php'));
        }

        $this->app->singleton(SaasGateway::class, function () {
            $secret = config('services.stripe.secret');

            return $secret
                ? new StripeSaasGateway($secret, config('services.stripe.webhook_secret'))
                : new LocalSaasGateway;
        });
    }

    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction() && ! $this->app->runningUnitTests());
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        Relation::enforceMorphMap([
            'user' => \App\Domain\Identity\Models\User::class,
            'tenant' => \App\Domain\Tenancy\Models\Tenant::class,
            'post' => \App\Domain\Community\Models\Post::class,
            'comment' => \App\Domain\Community\Models\Comment::class,
            'message' => \App\Domain\Messaging\Models\Message::class,
            'invitation' => \App\Domain\Tenancy\Models\Invitation::class,
            'membership' => \App\Domain\Tenancy\Models\Membership::class,
            'saas_subscription' => \App\Domain\Billing\Models\SaasSubscription::class,
            'order' => \App\Domain\Payments\Models\Order::class,
            'payment' => \App\Domain\Payments\Models\Payment::class,
            'client_subscription' => \App\Domain\Payments\Models\ClientSubscription::class,
            'booking' => \App\Domain\Calendar\Models\Booking::class,
            'contract' => \App\Domain\Crm\Models\Contract::class,
            'ai_output' => \App\Domain\Ai\Models\AiOutput::class,
            'checkin_response' => \App\Domain\Checkins\Models\CheckinResponse::class,
            'program' => \App\Domain\Workouts\Models\Program::class,
            'workout' => \App\Domain\Workouts\Models\Workout::class,
        ]);

        RateLimiter::for('api', fn (Request $r) => Limit::perMinute(120)->by($r->user()?->id ?: $r->ip()));
        RateLimiter::for('auth', fn (Request $r) => [
            Limit::perMinute(10)->by('ip:'.$r->ip()),
            Limit::perMinute(10)->by('acct:'.strtolower((string) $r->input('email'))),
        ]);
        RateLimiter::for('ai', fn (Request $r) => Limit::perMinute(20)->by($r->user()?->id ?: $r->ip()));
        RateLimiter::for('public', fn (Request $r) => Limit::perMinute(30)->by($r->ip()));
    }
}
