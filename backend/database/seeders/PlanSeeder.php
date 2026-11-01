<?php

namespace Database\Seeders;

use App\Domain\Billing\Models\SaasPlan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['code' => 'free', 'sort' => 0, 'm' => 0, 'y' => 0, 'trial' => 0,
                'name' => ['en' => 'Free', 'ar' => 'مجاني', 'fr' => 'Gratuit', 'es' => 'Gratis'],
                'limits' => ['clients' => 3, 'coaches' => 1, 'ai_requests_month' => 20, 'video_mb' => 200],
                'features' => ['workouts', 'nutrition', 'habits', 'checkins', 'messaging', 'calendar', 'payments']],
            ['code' => 'pro', 'sort' => 1, 'm' => 3900, 'y' => 39000, 'trial' => 14,
                'name' => ['en' => 'Pro', 'ar' => 'احترافي', 'fr' => 'Pro', 'es' => 'Pro'],
                'limits' => ['clients' => null, 'coaches' => 1, 'ai_requests_month' => 2000, 'video_mb' => 20000],
                'features' => ['workouts', 'nutrition', 'habits', 'checkins', 'messaging', 'calendar', 'payments', 'ai_copilot', 'automations', 'community', 'integrations', 'api', 'zapier', 'custom_domain_profile']],
            ['code' => 'studio', 'sort' => 2, 'm' => 9900, 'y' => 99000, 'trial' => 14,
                'name' => ['en' => 'Studio', 'ar' => 'استوديو', 'fr' => 'Studio', 'es' => 'Studio'],
                'limits' => ['clients' => null, 'coaches' => 5, 'ai_requests_month' => 8000, 'video_mb' => 100000],
                'features' => ['workouts', 'nutrition', 'habits', 'checkins', 'messaging', 'calendar', 'payments', 'ai_copilot', 'automations', 'community', 'integrations', 'api', 'zapier', 'custom_domain_profile', 'branded_app', 'team', 'commissions', 'locations']],
            ['code' => 'enterprise', 'sort' => 3, 'm' => 0, 'y' => 0, 'trial' => 0, 'public' => false,
                'name' => ['en' => 'Enterprise', 'ar' => 'المؤسسات', 'fr' => 'Entreprise', 'es' => 'Empresa'],
                'limits' => ['clients' => null, 'coaches' => null, 'ai_requests_month' => null, 'video_mb' => null],
                'features' => ['workouts', 'nutrition', 'habits', 'checkins', 'messaging', 'calendar', 'payments', 'ai_copilot', 'automations', 'community', 'integrations', 'api', 'zapier', 'custom_domain_profile', 'branded_app', 'team', 'commissions', 'locations', 'sso', 'white_label']],
        ];
        foreach ($plans as $p) {
            SaasPlan::query()->updateOrCreate(['code' => $p['code']], [
                'name' => $p['name'],
                'monthly_price_minor' => $p['m'],
                'annual_price_minor' => $p['y'],
                'currency' => 'USD',
                'limits' => $p['limits'],
                'features' => $p['features'],
                'trial_days' => $p['trial'],
                'sort' => $p['sort'],
                'is_public' => $p['public'] ?? true,
                'stripe_monthly_price_id' => env('STRIPE_PRICE_'.strtoupper($p['code']).'_MONTHLY'),
                'stripe_annual_price_id' => env('STRIPE_PRICE_'.strtoupper($p['code']).'_ANNUAL'),
            ]);
        }
    }
}
