<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Platform data first (roles, plans), then every module seeder in
     * database/seeders/Modules (alphabetical; prefix with NN_ ordering in class doc if needed),
     * then clearly-flagged demo data when SEED_DEMO=true (never in production).
     */
    public function run(): void
    {
        $this->call([RoleSeeder::class, PlanSeeder::class]);

        $modules = collect(glob(database_path('seeders/Modules/*Seeder.php')) ?: [])
            ->map(fn (string $f) => 'Database\\Seeders\\Modules\\'.basename($f, '.php'))
            ->sort()
            ->values()
            ->all();
        $this->call($modules);

        if (class_exists(DemoSeeder::class) && env('SEED_DEMO', ! app()->isProduction())) {
            $this->call(DemoSeeder::class);
        }
    }
}
