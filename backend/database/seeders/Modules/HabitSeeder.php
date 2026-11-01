<?php

namespace Database\Seeders\Modules;

use App\Domain\Habits\Models\Habit;
use App\Domain\Shared\Tenancy\TenantContext;
use Illuminate\Database\Seeder;

/** Global habit templates (tenant_id = null) in en/ar/fr/es. Idempotent on key. */
class HabitSeeder extends Seeder
{
    /** @return list<array<string, mixed>> */
    public static function templates(): array
    {
        return [
            ['key' => 'water', 'icon' => 'droplet', 'type' => 'numeric', 'target' => 2500, 'unit' => 'ml', 'health_metric' => 'water_ml',
                'name' => ['en' => 'Drink water', 'ar' => 'اشرب الماء', 'fr' => 'Boire de l’eau', 'es' => 'Beber agua']],
            ['key' => 'steps', 'icon' => 'footprints', 'type' => 'count', 'target' => 8000, 'unit' => 'steps', 'health_metric' => 'steps',
                'name' => ['en' => 'Daily steps', 'ar' => 'الخطوات اليومية', 'fr' => 'Pas quotidiens', 'es' => 'Pasos diarios']],
            ['key' => 'sleep', 'icon' => 'moon', 'type' => 'duration', 'target' => 480, 'unit' => 'min', 'health_metric' => 'sleep_minutes',
                'name' => ['en' => 'Sleep 8 hours', 'ar' => 'نم 8 ساعات', 'fr' => 'Dormir 8 heures', 'es' => 'Dormir 8 horas']],
            ['key' => 'protein', 'icon' => 'drumstick', 'type' => 'boolean', 'target' => null, 'unit' => null, 'health_metric' => null,
                'name' => ['en' => 'Hit your protein target', 'ar' => 'حقّق هدف البروتين', 'fr' => 'Atteindre son objectif de protéines', 'es' => 'Alcanzar tu objetivo de proteínas']],
            ['key' => 'veggies', 'icon' => 'salad', 'type' => 'count', 'target' => 5, 'unit' => 'servings', 'health_metric' => null,
                'name' => ['en' => 'Eat 5 servings of vegetables', 'ar' => 'تناول 5 حصص من الخضار', 'fr' => 'Manger 5 portions de légumes', 'es' => 'Comer 5 raciones de verdura']],
            ['key' => 'meditation', 'icon' => 'lotus', 'type' => 'duration', 'target' => 10, 'unit' => 'min', 'health_metric' => null,
                'name' => ['en' => 'Meditate', 'ar' => 'التأمل', 'fr' => 'Méditer', 'es' => 'Meditar']],
            ['key' => 'no_sugar', 'icon' => 'candy-off', 'type' => 'boolean', 'target' => null, 'unit' => null, 'health_metric' => null,
                'name' => ['en' => 'No added sugar', 'ar' => 'بدون سكر مضاف', 'fr' => 'Zéro sucre ajouté', 'es' => 'Sin azúcar añadido']],
            ['key' => 'stretching', 'icon' => 'stretch', 'type' => 'duration', 'target' => 10, 'unit' => 'min', 'health_metric' => null,
                'name' => ['en' => 'Stretch', 'ar' => 'تمارين الإطالة', 'fr' => 'S’étirer', 'es' => 'Estirar']],
            ['key' => 'reading', 'icon' => 'book', 'type' => 'duration', 'target' => 20, 'unit' => 'min', 'health_metric' => null,
                'name' => ['en' => 'Read', 'ar' => 'القراءة', 'fr' => 'Lire', 'es' => 'Leer']],
            ['key' => 'sunlight', 'icon' => 'sun', 'type' => 'duration', 'target' => 15, 'unit' => 'min', 'health_metric' => null,
                'name' => ['en' => 'Morning sunlight', 'ar' => 'التعرض لشمس الصباح', 'fr' => 'Lumière du matin', 'es' => 'Luz solar por la mañana']],
        ];
    }

    public function run(): void
    {
        app(TenantContext::class)->run(null, function () {
            foreach (self::templates() as $template) {
                $habit = Habit::withoutGlobalScopes()->whereNull('tenant_id')->where('key', $template['key'])->first()
                    ?? new Habit(['tenant_id' => null]);
                $habit->fill($template);
                $habit->tenant_id = null;
                $habit->save();
            }
        });
    }
}
