<?php

namespace Tests\Feature\Ai\Support;

use App\Domain\Ai\Providers\FakeProvider;
use App\Domain\Identity\Models\User;
use App\Domain\Nutrition\Models\Food;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Models\Exercise;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\ProgramWeek;

/** Library data and helpers shared by the AI feature tests and the eval suite. */
trait AiFixtures
{
    protected function fake(): FakeProvider
    {
        return app(FakeProvider::class);
    }

    /** @return array{0: Tenant, 1: User} */
    protected function proTenant(array $tenantAttributes = []): array
    {
        [$tenant, $owner] = $this->createCoachTenant([], $tenantAttributes);
        $tenant->forceFill(['plan' => 'pro'])->save();

        return [$tenant->refresh(), $owner];
    }

    protected function clientWithProfile(Tenant $tenant, User $coach, array $profile = [], array $user = []): User
    {
        $client = $this->addClient($tenant, $coach, $user);
        if ($profile !== []) {
            $this->inTenant($tenant, fn () => ClientProfile::query()->where('user_id', $client->id)->update($this->encryptProfile($profile)));
        }

        return $client;
    }

    /** @param array<string, mixed> $profile */
    private function encryptProfile(array $profile): array
    {
        $model = new ClientProfile;
        $model->forceFill($profile);

        return collect($model->getAttributes())->only(array_keys($profile))->all();
    }

    /** @return array<string, Exercise> keyed by slug */
    protected function seedExercises(): array
    {
        $rows = [
            ['goblet-squat', 'Dumbbell Goblet Squat', 'squat', 'quads', ['dumbbell'], 'compound', ['ar' => 'قرفصاء الكأس بالدمبل', 'fr' => 'Squat gobelet haltère', 'es' => 'Sentadilla copa con mancuerna']],
            ['db-rdl', 'Dumbbell Romanian Deadlift', 'hinge', 'hamstrings', ['dumbbell'], 'compound', ['fr' => 'Soulevé de terre roumain haltères']],
            ['db-bench-press', 'Dumbbell Bench Press', 'push_h', 'chest', ['dumbbell', 'bench'], 'compound', []],
            ['db-floor-press', 'Dumbbell Floor Press', 'push_h', 'chest', ['dumbbell'], 'compound', []],
            ['db-row', 'One-Arm Dumbbell Row', 'pull_h', 'back', ['dumbbell'], 'compound', []],
            ['db-shoulder-press', 'Dumbbell Shoulder Press', 'push_v', 'shoulders', ['dumbbell'], 'compound', []],
            ['db-reverse-lunge', 'Dumbbell Reverse Lunge', 'lunge', 'quads', ['dumbbell'], 'compound', []],
            ['db-pullover', 'Dumbbell Pullover', 'pull_v', 'lats', ['dumbbell'], 'compound', []],
            ['db-curl', 'Dumbbell Biceps Curl', 'pull_h', 'biceps', ['dumbbell'], 'isolation', []],
            ['push-up', 'Push-up', 'push_h', 'chest', ['bodyweight'], 'compound', ['ar' => 'تمرين الضغط']],
            ['plank', 'Plank', 'core', 'abs', ['bodyweight'], 'isolation', []],
            ['bodyweight-squat', 'Bodyweight Squat', 'squat', 'quads', [], 'compound', []],
            ['glute-bridge', 'Glute Bridge', 'hinge', 'glutes', ['bodyweight'], 'isolation', []],
            ['back-squat', 'Barbell Back Squat', 'squat', 'quads', ['barbell', 'rack'], 'compound', []],
            ['deadlift', 'Barbell Deadlift', 'hinge', 'hamstrings', ['barbell'], 'compound', []],
            ['bench-press', 'Barbell Bench Press', 'push_h', 'chest', ['barbell', 'bench'], 'compound', []],
            ['lat-pulldown', 'Lat Pulldown', 'pull_v', 'lats', ['cable', 'machine'], 'compound', []],
            ['cable-row', 'Seated Cable Row', 'pull_h', 'back', ['cable'], 'compound', []],
            ['pull-up', 'Pull-up', 'pull_v', 'lats', ['pull_up_bar'], 'compound', []],
            ['ohp', 'Barbell Overhead Press', 'push_v', 'shoulders', ['barbell'], 'compound', []],
        ];

        $out = [];
        app(TenantContext::class)->run(null, function () use ($rows, &$out) {
            foreach ($rows as [$slug, $name, $pattern, $muscle, $equipment, $mechanics, $translations]) {
                $out[$slug] = Exercise::query()->create([
                    'tenant_id' => null,
                    'slug' => $slug,
                    'name' => ['en' => $name] + $translations,
                    'primary_muscle' => $muscle,
                    'pattern' => $pattern,
                    'equipment' => $equipment,
                    'mechanics' => $mechanics,
                    'level' => $slug === 'pull-up' ? 'advanced' : 'beginner',
                    'is_bodyweight' => $equipment === [] || $equipment === ['bodyweight'],
                    'metrics' => ['weight', 'reps'],
                ]);
            }
        });

        return $out;
    }

    /** @return array<string, Food> keyed by key */
    protected function seedFoods(): array
    {
        // key => [en name, translations, region, category, kcal, p, c, f, diet tags, allergens]
        $rows = [
            'chicken' => ['Grilled chicken breast', ['ar' => 'صدر دجاج مشوي', 'fr' => 'Blanc de poulet grillé', 'es' => 'Pechuga de pollo a la plancha'], 'international', 'meat', 165, 31, 0, 3.6, ['halal', 'gluten_free', 'dairy_free'], []],
            'rice' => ['White rice cooked', ['ar' => 'أرز أبيض مطبوخ', 'fr' => 'Riz blanc cuit', 'es' => 'Arroz blanco cocido'], 'international', 'grains', 130, 2.7, 28, 0.3, ['halal', 'vegan', 'vegetarian', 'gluten_free', 'dairy_free'], []],
            'olive_oil' => ['Olive oil', ['ar' => 'زيت زيتون', 'fr' => 'Huile d’olive', 'es' => 'Aceite de oliva'], 'international', 'pantry', 884, 0, 0, 100, ['halal', 'vegan', 'vegetarian', 'gluten_free', 'dairy_free'], []],
            'dates' => ['Dates', ['ar' => 'تمر', 'fr' => 'Dattes', 'es' => 'Dátiles'], 'gulf', 'produce', 282, 2.5, 75, 0.4, ['halal', 'vegan', 'vegetarian', 'gluten_free', 'dairy_free'], []],
            'khubz' => ['Arabic bread khubz', ['ar' => 'خبز عربي', 'fr' => 'Pain arabe', 'es' => 'Pan árabe'], 'gulf', 'bakery', 275, 9, 55, 1.2, ['halal', 'vegan', 'vegetarian', 'dairy_free'], ['gluten']],
            'lamb' => ['Lean lamb', ['ar' => 'لحم غنم قليل الدهن', 'fr' => 'Agneau maigre', 'es' => 'Cordero magro'], 'gulf', 'meat', 206, 25, 0, 9, ['halal', 'gluten_free', 'dairy_free'], []],
            'hummus' => ['Hummus', ['ar' => 'حمص بالطحينة', 'fr' => 'Houmous', 'es' => 'Hummus'], 'levant', 'prepared', 166, 7.9, 14.3, 9.6, ['halal', 'vegan', 'vegetarian', 'gluten_free', 'dairy_free'], ['sesame']],
            'laban' => ['Laban yogurt drink', ['ar' => 'لبن', 'fr' => 'Leben', 'es' => 'Laban'], 'gulf', 'dairy', 60, 3.3, 4.7, 1.5, ['halal', 'vegetarian', 'gluten_free'], ['dairy']],
            'greek_yogurt' => ['Greek yogurt nonfat', ['ar' => 'زبادي يوناني خالي الدسم', 'fr' => 'Yaourt grec 0%', 'es' => 'Yogur griego desnatado'], 'international', 'dairy', 59, 10, 3.6, 0.4, ['halal', 'vegetarian', 'gluten_free'], ['dairy']],
            'lentils' => ['Lentils cooked', ['ar' => 'عدس مطبوخ', 'fr' => 'Lentilles cuites', 'es' => 'Lentejas cocidas'], 'levant', 'grains', 116, 9, 20, 0.4, ['halal', 'vegan', 'vegetarian', 'gluten_free', 'dairy_free'], []],
            'oats' => ['Rolled oats', ['ar' => 'شوفان', 'fr' => 'Flocons d’avoine', 'es' => 'Copos de avena'], 'international', 'grains', 389, 13, 66, 7, ['halal', 'vegan', 'vegetarian', 'dairy_free'], ['gluten']],
            'almonds' => ['Almonds', ['ar' => 'لوز', 'fr' => 'Amandes', 'es' => 'Almendras'], 'international', 'snacks', 579, 21, 22, 49, ['halal', 'vegan', 'vegetarian', 'gluten_free', 'dairy_free'], ['nuts']],
            'tuna' => ['Tuna in water', ['ar' => 'تونة بالماء', 'fr' => 'Thon au naturel', 'es' => 'Atún al natural'], 'international', 'pantry', 116, 26, 0, 1, ['halal', 'gluten_free', 'dairy_free'], ['fish']],
            'tofu' => ['Firm tofu', ['ar' => 'توفو', 'fr' => 'Tofu ferme', 'es' => 'Tofu firme'], 'international', 'produce', 144, 17, 3, 9, ['halal', 'vegan', 'vegetarian', 'gluten_free', 'dairy_free'], ['soy']],
            'banana' => ['Banana', ['ar' => 'موز', 'fr' => 'Banane', 'es' => 'Plátano'], 'international', 'produce', 89, 1.1, 23, 0.3, ['halal', 'vegan', 'vegetarian', 'gluten_free', 'dairy_free'], []],
            'couscous' => ['Couscous cooked', ['ar' => 'كسكس مطبوخ', 'fr' => 'Couscous cuit', 'es' => 'Cuscús cocido'], 'maghreb', 'grains', 112, 3.8, 23, 0.2, ['halal', 'vegan', 'vegetarian', 'dairy_free'], ['gluten']],
            'salad' => ['Mixed green salad', ['ar' => 'سلطة خضراء', 'fr' => 'Salade verte', 'es' => 'Ensalada verde'], 'international', 'produce', 20, 1.4, 3.6, 0.2, ['halal', 'vegan', 'vegetarian', 'gluten_free', 'dairy_free'], []],
            'avocado' => ['Avocado', ['ar' => 'أفوكادو', 'fr' => 'Avocat', 'es' => 'Aguacate'], 'international', 'produce', 160, 2, 8.5, 14.7, ['halal', 'vegan', 'vegetarian', 'gluten_free', 'dairy_free'], []],
            'egg_whites' => ['Egg whites', ['ar' => 'بياض البيض', 'fr' => 'Blancs d’œufs', 'es' => 'Claras de huevo'], 'international', 'dairy', 52, 11, 0.7, 0.2, ['halal', 'vegetarian', 'gluten_free', 'dairy_free'], ['egg']],
            'baguette' => ['Baguette', ['fr' => 'Baguette', 'ar' => 'باغيت', 'es' => 'Baguette'], 'french', 'bakery', 270, 9, 55, 1.5, ['halal', 'vegan', 'vegetarian', 'dairy_free'], ['gluten']],
        ];

        $out = [];
        app(TenantContext::class)->run(null, function () use ($rows, &$out) {
            foreach ($rows as $key => [$name, $tr, $region, $category, $kcal, $p, $c, $f, $tags, $allergens]) {
                $out[$key] = Food::query()->create([
                    'tenant_id' => null,
                    'name' => ['en' => $name] + $tr,
                    'source' => 'regional',
                    'region' => $region,
                    'category' => $category,
                    'kcal' => $kcal,
                    'protein_g' => $p,
                    'carbs_g' => $c,
                    'fat_g' => $f,
                    'diet_tags' => $tags,
                    'allergens' => $allergens,
                    'is_verified' => true,
                ]);
            }
        });

        return $out;
    }

    /** Platform starter templates (global, public). @return list<Program> */
    protected function seedTemplates(): array
    {
        $specs = [
            ['Beginner Full Body 3x', 'general', 'beginner', 3],
            ['Fat Loss Foundations', 'fat_loss', 'beginner', 3],
            ['Hypertrophy Upper/Lower', 'muscle_gain', 'intermediate', 4],
            ['Strength Basics 5x5', 'strength', 'intermediate', 3],
        ];

        return app(TenantContext::class)->run(null, function () use ($specs) {
            $out = [];
            foreach ($specs as [$name, $goal, $level, $days]) {
                $program = Program::query()->create([
                    'tenant_id' => null,
                    'name' => ['en' => $name],
                    'goal' => $goal,
                    'level' => $level,
                    'days_per_week' => $days,
                    'visibility' => 'public',
                    'is_template' => true,
                ]);
                foreach ([1, 2, 3, 4] as $n) {
                    ProgramWeek::query()->create(['program_id' => $program->id, 'number' => $n]);
                }
                $out[] = $program;
            }

            return $out;
        });
    }
}
