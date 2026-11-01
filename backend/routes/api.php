<?php

/*
|--------------------------------------------------------------------------
| Aurum REST API v1 — the contract shared by the web dashboard, both
| Flutter apps and the public API. Documented in /docs/API_CONTRACT.md.
|--------------------------------------------------------------------------
| Envelope: {data, meta, links, errors}. Auth: Sanctum bearer tokens with the
| "access" ability. Tenant: X-Tenant header (id or slug) or user's current tenant.
*/

use App\Http\Api\V1 as V1;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ------------------------------------------------------------------ Public
    Route::middleware('throttle:auth')->prefix('auth')->group(function () {
        Route::post('register', [V1\AuthController::class, 'register']);
        Route::post('login', [V1\AuthController::class, 'login']);
        Route::post('two-factor', [V1\AuthController::class, 'twoFactorChallenge']);
        Route::post('magic-link', [V1\AuthController::class, 'magicLink']);
        Route::post('magic-link/verify', [V1\AuthController::class, 'magicLinkVerify']);
        Route::post('social/{provider}', [V1\AuthController::class, 'social']);
        Route::post('forgot-password', [V1\AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [V1\AuthController::class, 'resetPassword']);
    });
    Route::post('auth/refresh', [V1\AuthController::class, 'refresh'])->middleware('throttle:30,1');
    Route::get('invitations/{code}', [V1\AuthController::class, 'invitationPreview'])->middleware('throttle:public');

    Route::middleware('throttle:public')->prefix('public')->group(function () {
        Route::get('coaches', [V1\Marketplace\PublicCoachController::class, 'index']);
        Route::get('coaches/{slug}', [V1\Marketplace\PublicCoachController::class, 'show']);
        Route::get('coaches/{slug}/slots', [V1\Marketplace\PublicCoachController::class, 'slots']);
        Route::post('coaches/{slug}/leads', [V1\Marketplace\PublicCoachController::class, 'lead']);
        Route::get('checkout/{tenantSlug}/{productSlug}', [V1\Payments\CheckoutController::class, 'show']);
        Route::post('checkout/{tenantSlug}/{productSlug}', [V1\Payments\CheckoutController::class, 'store'])->middleware('idempotent');
        Route::get('orders/{order}/status', [V1\Payments\CheckoutController::class, 'status']);
        Route::get('program-templates', [V1\Workouts\ProgramController::class, 'publicTemplates']);
    });

    // ------------------------------------------------------- Authenticated
    Route::middleware(['auth:sanctum', 'ability:access', 'active', 'throttle:api'])->group(function () {
        Route::post('auth/logout', [V1\AuthController::class, 'logout']);
        Route::get('me', [V1\AuthController::class, 'me']);
        Route::patch('me', [V1\AuthController::class, 'updateMe']);
        Route::post('me/avatar', [V1\Identity\AccountController::class, 'avatar']);
        Route::post('me/devices', [V1\AuthController::class, 'registerDevice']);
        Route::post('me/two-factor', [V1\AuthController::class, 'twoFactorEnable']);
        Route::post('me/two-factor/confirm', [V1\AuthController::class, 'twoFactorConfirm']);
        Route::delete('me/two-factor', [V1\AuthController::class, 'twoFactorDisable']);
        Route::post('me/export', [V1\Identity\AccountController::class, 'export']);
        Route::delete('me', [V1\Identity\AccountController::class, 'destroy']);
        Route::post('invitations/accept', [V1\AuthController::class, 'acceptInvitation']);
        Route::post('tenants', [V1\Identity\AccountController::class, 'createTenant']);
        Route::post('tenants/switch', [V1\Identity\AccountController::class, 'switchTenant']);
        Route::get('notifications', [V1\Identity\NotificationController::class, 'index']);
        Route::post('notifications/read', [V1\Identity\NotificationController::class, 'markRead']);
        Route::get('marketplace/coaches', [V1\Marketplace\PublicCoachController::class, 'index']);
        Route::post('marketplace/match', [V1\Marketplace\MatchController::class, 'store']);

        // -------------------------------------------- Tenant scoped (any role)
        Route::middleware('tenant')->group(function () {
            Route::get('tenant', [V1\Tenancy\TenantController::class, 'show']);

            // Messaging (staff + clients, participant-checked)
            Route::get('conversations', [V1\Messaging\ConversationController::class, 'index']);
            Route::post('conversations', [V1\Messaging\ConversationController::class, 'store']);
            Route::get('conversations/{conversation}', [V1\Messaging\ConversationController::class, 'show']);
            Route::get('conversations/{conversation}/messages', [V1\Messaging\MessageController::class, 'index']);
            Route::post('conversations/{conversation}/messages', [V1\Messaging\MessageController::class, 'store']);
            Route::post('conversations/{conversation}/read', [V1\Messaging\ConversationController::class, 'read']);
            Route::post('conversations/{conversation}/typing', [V1\Messaging\ConversationController::class, 'typing']);
            Route::patch('messages/{message}', [V1\Messaging\MessageController::class, 'update']);
            Route::delete('messages/{message}', [V1\Messaging\MessageController::class, 'destroy']);
            Route::post('messages/{message}/reactions', [V1\Messaging\MessageController::class, 'react']);
            Route::get('messages/{message}/attachments/{attachment}', [V1\Messaging\MessageController::class, 'attachment'])->name('messages.attachment');

            // Community
            Route::get('community/spaces', [V1\Community\SpaceController::class, 'index']);
            Route::get('community/spaces/{space}/posts', [V1\Community\PostController::class, 'index']);
            Route::post('community/spaces/{space}/posts', [V1\Community\PostController::class, 'store']);
            Route::delete('community/posts/{post}', [V1\Community\PostController::class, 'destroy']);
            Route::get('community/posts/{post}/comments', [V1\Community\CommentController::class, 'index']);
            Route::post('community/posts/{post}/comments', [V1\Community\CommentController::class, 'store']);
            Route::post('community/posts/{post}/react', [V1\Community\PostController::class, 'react']);
            Route::post('community/posts/{post}/vote', [V1\Community\PostController::class, 'vote']);
            Route::post('community/reports', [V1\Community\ReportController::class, 'store']);
            Route::get('challenges', [V1\Community\ChallengeController::class, 'index']);
            Route::get('challenges/{challenge}', [V1\Community\ChallengeController::class, 'show']);
            Route::post('challenges/{challenge}/join', [V1\Community\ChallengeController::class, 'join']);
            Route::get('badges', [V1\Community\BadgeController::class, 'index']);

            // Library reads used by clients too
            Route::get('exercises', [V1\Workouts\ExerciseController::class, 'index']);
            Route::get('exercises/{exercise}', [V1\Workouts\ExerciseController::class, 'show']);
            Route::get('foods', [V1\Nutrition\FoodController::class, 'index']);
            Route::get('foods/barcode/{code}', [V1\Nutrition\FoodController::class, 'barcode']);
            Route::get('foods/{food}', [V1\Nutrition\FoodController::class, 'show']);
            Route::get('recipes', [V1\Nutrition\RecipeController::class, 'index']);
            Route::get('recipes/{recipe}', [V1\Nutrition\RecipeController::class, 'show']);

            // Offline sync (client apps)
            Route::get('sync/pull', [V1\Sync\SyncController::class, 'pull']);
            Route::post('sync/push', [V1\Sync\SyncController::class, 'push']);

            // ---------------------------------------------------- Client self
            Route::prefix('me')->group(function () {
                Route::get('today', [V1\Client\TodayController::class, 'show']);
                Route::get('program', [V1\Client\ClientWorkoutController::class, 'program']);
                Route::get('workouts/{workout}', [V1\Client\ClientWorkoutController::class, 'workout']);
                Route::get('sessions', [V1\Client\SessionController::class, 'index']);
                Route::post('sessions', [V1\Client\SessionController::class, 'store']);
                Route::get('sessions/{session}', [V1\Client\SessionController::class, 'show']);
                Route::post('sessions/{session}/sets', [V1\Client\SessionController::class, 'sets']);
                Route::post('sessions/{session}/complete', [V1\Client\SessionController::class, 'complete']);
                Route::get('exercises/{exercise}/history', [V1\Client\SessionController::class, 'exerciseHistory']);
                Route::get('exercises/{exercise}/last', [V1\Client\SessionController::class, 'lastPerformance']);
                Route::get('records', [V1\Client\SessionController::class, 'records']);

                Route::get('nutrition', [V1\Client\ClientNutritionController::class, 'day']);
                Route::get('nutrition/week', [V1\Client\ClientNutritionController::class, 'week']);
                Route::get('nutrition/plan', [V1\Client\ClientNutritionController::class, 'plan']);
                Route::post('food-logs', [V1\Client\ClientNutritionController::class, 'log']);
                Route::delete('food-logs/{foodLog}', [V1\Client\ClientNutritionController::class, 'deleteLog']);
                Route::post('food-logs/from-plan', [V1\Client\ClientNutritionController::class, 'fromPlan']);
                Route::post('food-logs/copy-day', [V1\Client\ClientNutritionController::class, 'copyDay']);
                Route::post('food-logs/photo', [V1\Ai\PhotoFoodController::class, 'store'])->middleware('throttle:ai');
                Route::post('water', [V1\Client\ClientNutritionController::class, 'water']);
                Route::get('foods/recent', [V1\Client\ClientNutritionController::class, 'recent']);
                Route::post('foods/{food}/favorite', [V1\Client\ClientNutritionController::class, 'favorite']);

                Route::get('habits', [V1\Client\ClientHabitController::class, 'index']);
                Route::post('habits/{assignment}/log', [V1\Client\ClientHabitController::class, 'log']);

                Route::get('checkins', [V1\Client\ClientCheckinController::class, 'index']);
                Route::get('checkins/{response}', [V1\Client\ClientCheckinController::class, 'show']);
                Route::post('checkins/{response}/submit', [V1\Client\ClientCheckinController::class, 'submit']);
                Route::post('progress-photos', [V1\Client\ClientProgressController::class, 'photo']);
                Route::get('progress-photos/{photo}', [V1\Client\ClientProgressController::class, 'showPhoto'])->name('progress-photos.show');
                Route::post('body-metrics', [V1\Client\ClientProgressController::class, 'metric']);
                Route::get('progress', [V1\Client\ClientProgressController::class, 'summary']);

                Route::get('bookings', [V1\Client\ClientBookingController::class, 'index']);
                Route::get('slots', [V1\Client\ClientBookingController::class, 'slots']);
                Route::get('classes', [V1\Client\ClientBookingController::class, 'classes']);
                Route::post('bookings', [V1\Client\ClientBookingController::class, 'store']);
                Route::post('bookings/{booking}/cancel', [V1\Client\ClientBookingController::class, 'cancel']);
                Route::post('bookings/{booking}/claim', [V1\Client\ClientBookingController::class, 'claim']);
                Route::get('bookings/{booking}/ics', [V1\Client\ClientBookingController::class, 'ics']);
                Route::get('credits', [V1\Client\ClientBookingController::class, 'credits']);

                Route::get('shop', [V1\Client\ShopController::class, 'index']);
                Route::post('shop/{product}/checkout', [V1\Client\ShopController::class, 'checkout'])->middleware('idempotent');
                Route::get('purchases', [V1\Client\ShopController::class, 'purchases']);
                Route::get('invoices', [V1\Client\ShopController::class, 'invoices']);
                Route::get('invoices/{invoice}/pdf', [V1\Client\ShopController::class, 'invoicePdf']);
                Route::post('subscriptions/{subscription}/cancel', [V1\Client\ShopController::class, 'cancelSubscription']);
                Route::post('subscriptions/{subscription}/pause', [V1\Client\ShopController::class, 'pauseSubscription']);
                Route::get('contracts', [V1\Client\ClientContractController::class, 'index']);
                Route::post('contracts/{contract}/sign', [V1\Client\ClientContractController::class, 'sign']);

                Route::post('health/samples', [V1\Integrations\HealthController::class, 'ingest']);
                Route::get('health/summary', [V1\Integrations\HealthController::class, 'mySummary']);
                Route::get('integrations', [V1\Integrations\IntegrationController::class, 'mine']);
                Route::post('integrations/{provider}/connect', [V1\Integrations\IntegrationController::class, 'connect']);
                Route::delete('integrations/{provider}', [V1\Integrations\IntegrationController::class, 'disconnect']);
                Route::post('integrations/{provider}/sync', [V1\Integrations\IntegrationController::class, 'syncNow']);

                Route::post('ai/coach-chat', [V1\Ai\SelfGuidedController::class, 'chat'])->middleware('throttle:ai');
                Route::post('ai/starter-plan', [V1\Ai\SelfGuidedController::class, 'starterPlan'])->middleware('throttle:ai');
                Route::post('ai/form-check', [V1\Ai\FormCheckController::class, 'store'])->middleware('throttle:ai');
                Route::get('ai/weekly-recap', [V1\Ai\SelfGuidedController::class, 'weeklyRecap']);
            });

            // ---------------------------------------------------- Staff only
            Route::middleware('role:owner,coach,assistant')->group(function () {
                Route::get('dashboard', [V1\Coach\DashboardController::class, 'show']);
                Route::get('search', [V1\Coach\SearchController::class, 'index']);

                // Clients
                Route::get('clients', [V1\Coach\ClientController::class, 'index']);
                Route::post('clients', [V1\Coach\ClientController::class, 'store']);
                Route::post('clients/import', [V1\Coach\ClientController::class, 'import']);
                Route::get('clients/{client}', [V1\Coach\ClientController::class, 'show']);
                Route::patch('clients/{client}', [V1\Coach\ClientController::class, 'update']);
                Route::delete('clients/{client}', [V1\Coach\ClientController::class, 'destroy']);
                Route::get('clients/{client}/schedule', [V1\Coach\ClientController::class, 'schedule']);
                Route::get('clients/{client}/sessions', [V1\Coach\ClientController::class, 'sessions']);
                Route::get('clients/{client}/sessions/{session}', [V1\Coach\ClientController::class, 'session']);
                Route::get('clients/{client}/nutrition', [V1\Coach\ClientController::class, 'nutrition']);
                Route::get('clients/{client}/progress', [V1\Coach\ClientController::class, 'progress']);
                Route::get('clients/{client}/compliance', [V1\Coach\ClientController::class, 'compliance']);
                Route::get('clients/{client}/health', [V1\Integrations\HealthController::class, 'clientTimeline']);
                Route::get('clients/{client}/credits', [V1\Calendar\CreditController::class, 'show']);
                Route::post('clients/{client}/credits', [V1\Calendar\CreditController::class, 'adjust']);
                Route::get('clients/{client}/nutrition-targets', [V1\Nutrition\TargetController::class, 'index']);
                Route::post('clients/{client}/nutrition-targets', [V1\Nutrition\TargetController::class, 'store']);
                Route::post('nutrition/calculate', [V1\Nutrition\TargetController::class, 'calculate']);
                Route::get('compliance', [V1\Coach\ComplianceController::class, 'index']);
                Route::post('compliance/message', [V1\Coach\ComplianceController::class, 'bulkMessage']);
                Route::get('invitations', [V1\Coach\InvitationController::class, 'index']);
                Route::post('invitations/{invitation}/resend', [V1\Coach\InvitationController::class, 'resend']);
                Route::delete('invitations/{invitation}', [V1\Coach\InvitationController::class, 'destroy']);
                Route::get('invitations/{invitation}/qr', [V1\Coach\InvitationController::class, 'qr']);
                Route::apiResource('groups', V1\Coach\GroupController::class);

                // Workouts
                Route::post('exercises', [V1\Workouts\ExerciseController::class, 'store']);
                Route::patch('exercises/{exercise}', [V1\Workouts\ExerciseController::class, 'update']);
                Route::delete('exercises/{exercise}', [V1\Workouts\ExerciseController::class, 'destroy']);
                Route::post('exercises/{exercise}/favorite', [V1\Workouts\ExerciseController::class, 'favorite']);
                Route::post('exercises/{exercise}/video', [V1\Workouts\ExerciseController::class, 'uploadVideo']);
                Route::apiResource('workouts', V1\Workouts\WorkoutController::class);
                Route::post('workouts/{workout}/duplicate', [V1\Workouts\WorkoutController::class, 'duplicate']);
                Route::get('workouts/{workout}/export', [V1\Workouts\WorkoutController::class, 'export']);
                Route::post('workouts/import', [V1\Workouts\WorkoutController::class, 'import']);
                Route::apiResource('programs', V1\Workouts\ProgramController::class);
                Route::post('programs/{program}/duplicate', [V1\Workouts\ProgramController::class, 'duplicate']);
                Route::post('programs/{program}/assign', [V1\Workouts\ProgramController::class, 'assign']);
                Route::post('programs/{program}/weeks/{week}/copy', [V1\Workouts\ProgramController::class, 'copyWeek']);
                Route::get('assignments', [V1\Workouts\AssignmentController::class, 'index']);
                Route::patch('assignments/{assignment}', [V1\Workouts\AssignmentController::class, 'update']);
                Route::delete('assignments/{assignment}', [V1\Workouts\AssignmentController::class, 'destroy']);

                // Nutrition
                Route::post('foods', [V1\Nutrition\FoodController::class, 'store']);
                Route::patch('foods/{food}', [V1\Nutrition\FoodController::class, 'update']);
                Route::post('recipes', [V1\Nutrition\RecipeController::class, 'store']);
                Route::patch('recipes/{recipe}', [V1\Nutrition\RecipeController::class, 'update']);
                Route::delete('recipes/{recipe}', [V1\Nutrition\RecipeController::class, 'destroy']);
                Route::apiResource('meal-plans', V1\Nutrition\MealPlanController::class);
                Route::post('meal-plans/{mealPlan}/assign', [V1\Nutrition\MealPlanController::class, 'assign']);
                Route::get('meal-plans/{mealPlan}/grocery-list', [V1\Nutrition\MealPlanController::class, 'groceryList']);
                Route::get('meal-plans/{mealPlan}/pdf', [V1\Nutrition\MealPlanController::class, 'pdf']);
                Route::post('meal-plans/{mealPlan}/items/{mealItem}/swaps', [V1\Nutrition\MealPlanController::class, 'swaps']);

                // Habits & check-ins
                Route::get('habits', [V1\Habits\HabitController::class, 'index']);
                Route::post('habits', [V1\Habits\HabitController::class, 'store']);
                Route::post('habit-assignments', [V1\Habits\HabitController::class, 'assign']);
                Route::patch('habit-assignments/{assignment}', [V1\Habits\HabitController::class, 'updateAssignment']);
                Route::delete('habit-assignments/{assignment}', [V1\Habits\HabitController::class, 'unassign']);
                Route::apiResource('checkin-forms', V1\Checkins\CheckinFormController::class);
                Route::post('checkin-schedules', [V1\Checkins\CheckinController::class, 'schedule']);
                Route::delete('checkin-schedules/{schedule}', [V1\Checkins\CheckinController::class, 'unschedule']);
                Route::get('checkins', [V1\Checkins\CheckinController::class, 'index']);
                Route::get('checkins/{response}', [V1\Checkins\CheckinController::class, 'show']);
                Route::post('checkins/{response}/review', [V1\Checkins\CheckinController::class, 'review']);
                Route::post('checkins/{response}/comments', [V1\Checkins\CheckinController::class, 'comment']);
                Route::get('clients/{client}/progress-photos/{photo}', [V1\Checkins\CheckinController::class, 'photo'])->name('clients.progress-photos.show');

                // Messaging admin
                Route::get('broadcasts', [V1\Messaging\BroadcastController::class, 'index']);
                Route::post('broadcasts', [V1\Messaging\BroadcastController::class, 'store']);
                Route::post('broadcasts/preview', [V1\Messaging\BroadcastController::class, 'preview']);
                Route::delete('broadcasts/{scheduledMessage}', [V1\Messaging\BroadcastController::class, 'destroy']);
                Route::apiResource('message-templates', V1\Messaging\MessageTemplateController::class)->except('show');
                Route::apiResource('automations', V1\Messaging\AutomationController::class);

                // Community moderation
                Route::post('community/spaces', [V1\Community\SpaceController::class, 'store']);
                Route::patch('community/spaces/{space}', [V1\Community\SpaceController::class, 'update']);
                Route::post('community/posts/{post}/pin', [V1\Community\PostController::class, 'pin']);
                Route::post('community/posts/{post}/hide', [V1\Community\PostController::class, 'hide']);
                Route::get('community/reports', [V1\Community\ReportController::class, 'index']);
                Route::post('community/reports/{report}/resolve', [V1\Community\ReportController::class, 'resolve']);
                Route::post('challenges', [V1\Community\ChallengeController::class, 'store']);
                Route::patch('challenges/{challenge}', [V1\Community\ChallengeController::class, 'update']);
                Route::delete('challenges/{challenge}', [V1\Community\ChallengeController::class, 'destroy']);

                // Calendar
                Route::get('events', [V1\Calendar\EventController::class, 'index']);
                Route::post('events', [V1\Calendar\EventController::class, 'store']);
                Route::get('events/{event}', [V1\Calendar\EventController::class, 'show']);
                Route::patch('events/{event}', [V1\Calendar\EventController::class, 'update']);
                Route::delete('events/{event}', [V1\Calendar\EventController::class, 'destroy']);
                Route::post('events/{event}/bookings', [V1\Calendar\BookingController::class, 'store']);
                Route::patch('bookings/{booking}', [V1\Calendar\BookingController::class, 'update']);
                Route::post('bookings/bulk-status', [V1\Calendar\BookingController::class, 'bulkStatus']);
                Route::post('bookings/check-in', [V1\Calendar\BookingController::class, 'checkIn']);
                Route::get('availability', [V1\Calendar\AvailabilityController::class, 'show']);
                Route::put('availability', [V1\Calendar\AvailabilityController::class, 'update']);
                Route::get('locations', [V1\Calendar\AvailabilityController::class, 'locations']);
                Route::post('locations', [V1\Calendar\AvailabilityController::class, 'storeLocation']);

                // AI copilot
                Route::middleware('throttle:ai')->prefix('ai')->group(function () {
                    Route::post('programs', [V1\Ai\CopilotController::class, 'program']);
                    Route::post('meal-plans', [V1\Ai\CopilotController::class, 'mealPlan']);
                    Route::post('checkins/{response}/summary', [V1\Ai\CopilotController::class, 'checkinSummary']);
                    Route::post('reply-draft', [V1\Ai\CopilotController::class, 'replyDraft']);
                    Route::post('progression/{client}', [V1\Ai\CopilotController::class, 'progression']);
                    Route::post('ask', [V1\Ai\CopilotController::class, 'ask']);
                    Route::post('content', [V1\Ai\CopilotController::class, 'content']);
                    Route::post('summarize/{conversation}', [V1\Ai\CopilotController::class, 'summarizeThread']);
                });
                Route::get('ai/outputs', [V1\Ai\AiOutputController::class, 'index']);
                Route::get('ai/outputs/{output}', [V1\Ai\AiOutputController::class, 'show']);
                Route::post('ai/outputs/{output}/approve', [V1\Ai\AiOutputController::class, 'approve']);
                Route::post('ai/outputs/{output}/reject', [V1\Ai\AiOutputController::class, 'reject']);
                Route::get('ai/settings', [V1\Ai\AiSettingsController::class, 'show']);
                Route::put('ai/settings', [V1\Ai\AiSettingsController::class, 'update']);
                Route::get('ai/usage', [V1\Ai\AiSettingsController::class, 'usage']);
                Route::get('ai/churn', [V1\Ai\CopilotController::class, 'churn']);

                // CRM
                Route::get('lead-stages', [V1\Crm\LeadController::class, 'stages']);
                Route::put('lead-stages', [V1\Crm\LeadController::class, 'saveStages']);
                Route::apiResource('leads', V1\Crm\LeadController::class);
                Route::post('leads/{lead}/activities', [V1\Crm\LeadController::class, 'activity']);
                Route::post('leads/{lead}/convert', [V1\Crm\LeadController::class, 'convert']);
                Route::apiResource('contract-templates', V1\Crm\ContractTemplateController::class)->except('show');
                Route::get('contracts', [V1\Crm\ContractController::class, 'index']);
                Route::post('contracts', [V1\Crm\ContractController::class, 'store']);
                Route::get('contracts/{contract}', [V1\Crm\ContractController::class, 'show']);
                Route::get('contracts/{contract}/pdf', [V1\Crm\ContractController::class, 'pdf']);

                // Payments (view for staff; manage enforced per permission)
                Route::get('payment-gateways', [V1\Payments\GatewayController::class, 'index']);
                Route::post('payment-gateways', [V1\Payments\GatewayController::class, 'store']);
                Route::patch('payment-gateways/{gateway}', [V1\Payments\GatewayController::class, 'update']);
                Route::delete('payment-gateways/{gateway}', [V1\Payments\GatewayController::class, 'destroy']);
                Route::apiResource('products', V1\Payments\ProductController::class);
                Route::get('products/{product}/link', [V1\Payments\ProductController::class, 'link']);
                Route::apiResource('coupons', V1\Payments\CouponController::class)->except('show');
                Route::get('orders', [V1\Payments\OrderController::class, 'index']);
                Route::get('orders/{order}', [V1\Payments\OrderController::class, 'show']);
                Route::post('orders/{order}/refund', [V1\Payments\OrderController::class, 'refund'])->middleware('idempotent');
                Route::post('manual-payments', [V1\Payments\OrderController::class, 'manual'])->middleware('idempotent');
                Route::get('subscriptions', [V1\Payments\SubscriptionController::class, 'index']);
                Route::post('subscriptions/{subscription}/{action}', [V1\Payments\SubscriptionController::class, 'action'])->whereIn('action', ['cancel', 'pause', 'resume']);
                Route::get('invoices', [V1\Payments\InvoiceController::class, 'index']);
                Route::get('invoices/{invoice}/pdf', [V1\Payments\InvoiceController::class, 'pdf']);
                Route::get('analytics/overview', [V1\Payments\AnalyticsController::class, 'overview']);
                Route::get('analytics/retention', [V1\Payments\AnalyticsController::class, 'retention']);
                Route::get('analytics/coaches', [V1\Payments\AnalyticsController::class, 'coaches']);
                Route::get('analytics/export', [V1\Payments\AnalyticsController::class, 'export']);

                // Integrations / developer
                Route::get('integrations', [V1\Integrations\IntegrationController::class, 'index']);
                Route::apiResource('webhook-endpoints', V1\Integrations\WebhookEndpointController::class)->except('show');
                Route::get('webhook-endpoints/{endpoint}/deliveries', [V1\Integrations\WebhookEndpointController::class, 'deliveries']);
                Route::get('api-tokens', [V1\Integrations\ApiTokenController::class, 'index']);
                Route::post('api-tokens', [V1\Integrations\ApiTokenController::class, 'store']);
                Route::delete('api-tokens/{token}', [V1\Integrations\ApiTokenController::class, 'destroy']);

                // Owner-level settings
                Route::middleware('role:owner')->group(function () {
                    Route::patch('tenant', [V1\Tenancy\TenantController::class, 'update']);
                    Route::post('tenant/logo', [V1\Tenancy\TenantController::class, 'logo']);
                    Route::get('team', [V1\Tenancy\TeamController::class, 'index']);
                    Route::post('team', [V1\Tenancy\TeamController::class, 'invite']);
                    Route::patch('team/{user}', [V1\Tenancy\TeamController::class, 'update']);
                    Route::delete('team/{user}', [V1\Tenancy\TeamController::class, 'destroy']);
                    Route::get('billing', [V1\Tenancy\BillingController::class, 'show']);
                    Route::post('billing/subscribe', [V1\Tenancy\BillingController::class, 'subscribe']);
                    Route::post('billing/cancel', [V1\Tenancy\BillingController::class, 'cancel']);
                    Route::post('billing/resume', [V1\Tenancy\BillingController::class, 'resume']);
                    Route::post('billing/remove-card', [V1\Tenancy\BillingController::class, 'removeCard']);
                    Route::get('billing/portal', [V1\Tenancy\BillingController::class, 'portal']);
                    Route::put('coach-profile', [V1\Marketplace\CoachProfileController::class, 'update']);
                    Route::get('coach-profile', [V1\Marketplace\CoachProfileController::class, 'show']);
                    Route::post('reviews/{review}/reply', [V1\Marketplace\CoachProfileController::class, 'reply']);
                    Route::post('commission-rules', [V1\Payments\AnalyticsController::class, 'saveCommission']);
                });
            });
        });
    });
});
