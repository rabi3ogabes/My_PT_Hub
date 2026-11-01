<?php

namespace Database\Seeders\Modules;

use App\Domain\Nutrition\Models\Food;
use App\Domain\Shared\Tenancy\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Global food library (tenant_id = null): Gulf, Levant, Maghreb, French and
 * international staples with per-100 g macros and names in en/ar/fr/es.
 * Idempotent: rows are matched on their English name.
 */
class FoodSeeder extends Seeder
{
    public function run(): void
    {
        app(TenantContext::class)->run(null, function () {
            /** @var list<array<string, mixed>> $foods */
            $foods = require database_path('seeders/data/foods.php');

            DB::transaction(function () use ($foods) {
                foreach ($foods as $row) {
                    $food = Food::withoutGlobalScopes()->withTrashed()
                        ->whereNull('tenant_id')
                        ->where('name->en', $row['name']['en'])
                        ->first() ?? new Food(['tenant_id' => null]);

                    $food->fill($row + ['is_verified' => true, 'moderation' => 'approved', 'deleted_at' => null]);
                    $food->tenant_id = null;
                    $food->save();
                }
            });
        });
    }
}
