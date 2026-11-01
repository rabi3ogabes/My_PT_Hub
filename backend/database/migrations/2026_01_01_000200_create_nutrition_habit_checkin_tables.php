<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foods', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->json('name');
            $table->json('aliases')->nullable();
            $table->string('brand')->nullable();
            $table->string('barcode', 32)->nullable()->index();
            $table->string('source', 16)->default('custom'); // usda|off|regional|custom|user
            $table->string('region', 16)->nullable(); // gulf|levant|maghreb|french|international
            $table->string('category', 32)->nullable(); // aisle: produce|meat|dairy|bakery|grains|pantry|drinks|frozen|snacks|prepared
            // per 100 g
            $table->decimal('kcal', 7, 2);
            $table->decimal('protein_g', 7, 2)->default(0);
            $table->decimal('carbs_g', 7, 2)->default(0);
            $table->decimal('fat_g', 7, 2)->default(0);
            $table->decimal('fiber_g', 7, 2)->default(0);
            $table->decimal('sugar_g', 7, 2)->default(0);
            $table->decimal('sodium_mg', 8, 2)->default(0);
            $table->json('micros')->nullable();
            $table->json('servings')->nullable(); // [{unit:"cup", grams:240, label:{en:..}}]
            $table->json('diet_tags')->nullable(); // halal|vegan|vegetarian|gluten_free|dairy_free|keto
            $table->json('allergens')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('moderation', 16)->default('approved'); // pending|approved|rejected
            $table->foreignUlid('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('recipes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('slug')->index();
            $table->json('name');
            $table->json('description')->nullable();
            $table->json('steps')->nullable(); // {locale: [..]}
            $table->string('cuisine', 24)->nullable();
            $table->unsignedSmallInteger('prep_minutes')->default(0);
            $table->unsignedSmallInteger('cook_minutes')->default(0);
            $table->unsignedTinyInteger('servings')->default(1);
            $table->json('tags')->nullable();
            $table->string('photo_url')->nullable();
            // cached per serving totals
            $table->decimal('kcal', 8, 2)->default(0);
            $table->decimal('protein_g', 7, 2)->default(0);
            $table->decimal('carbs_g', 7, 2)->default(0);
            $table->decimal('fat_g', 7, 2)->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('recipe_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('food_id')->constrained()->restrictOnDelete();
            $table->decimal('grams', 8, 2);
            $table->string('display_unit', 16)->nullable();
            $table->decimal('display_qty', 8, 2)->nullable();
            $table->unsignedSmallInteger('position')->default(0);
        });

        Schema::create('nutrition_targets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->string('day_type', 12)->default('all'); // all|training|rest
            $table->unsignedSmallInteger('kcal');
            $table->unsignedSmallInteger('protein_g');
            $table->unsignedSmallInteger('carbs_g');
            $table->unsignedSmallInteger('fat_g');
            $table->unsignedSmallInteger('fiber_g')->nullable();
            $table->unsignedSmallInteger('water_ml')->default(2500);
            $table->date('effective_from');
            $table->json('calculation')->nullable(); // inputs + formula used
            $table->boolean('override_safety')->default(false);
            $table->timestamps();
            $table->index(['client_id', 'effective_from']);
        });

        Schema::create('meal_plans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUlid('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('type', 16)->default('fixed'); // fixed|flexible|portion|fasting|ramadan
            $table->json('diet_tags')->nullable();
            $table->boolean('is_template')->default(false);
            $table->json('fasting')->nullable(); // {window_start:"12:00", window_end:"20:00"} | ramadan {city}
            $table->date('start_date')->nullable();
            $table->string('status', 16)->default('draft'); // draft|active|archived
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('meal_plan_days', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('meal_plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_number');
            $table->string('label')->nullable();
            $table->string('day_type', 12)->default('all');
        });

        Schema::create('meals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('meal_plan_day_id')->constrained()->cascadeOnDelete();
            $table->string('slot', 16); // breakfast|lunch|dinner|snack|suhoor|iftar|custom
            $table->string('name')->nullable();
            $table->time('time')->nullable();
            $table->unsignedTinyInteger('position')->default(0);
        });

        Schema::create('meal_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('meal_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('food_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignUlid('recipe_id')->nullable()->constrained()->restrictOnDelete();
            $table->decimal('quantity', 8, 2); // grams for food, servings for recipe
            $table->string('unit', 16)->default('g');
            $table->unsignedTinyInteger('position')->default(0);
        });

        Schema::create('food_logs', function (Blueprint $table) {
            $table->ulid('id')->primary(); // client ULID
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->date('log_date');
            $table->string('slot', 16);
            $table->foreignUlid('food_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('recipe_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable(); // quick-add label
            $table->decimal('quantity', 8, 2)->default(0);
            $table->string('unit', 16)->default('g');
            $table->decimal('kcal', 8, 2);
            $table->decimal('protein_g', 7, 2)->default(0);
            $table->decimal('carbs_g', 7, 2)->default(0);
            $table->decimal('fat_g', 7, 2)->default(0);
            $table->string('source', 16)->default('search'); // search|barcode|quick|photo|plan|copy
            $table->timestamp('logged_at');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['client_id', 'log_date']);
        });

        Schema::create('water_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->date('log_date');
            $table->unsignedSmallInteger('ml');
            $table->timestamp('logged_at');
            $table->timestamps();
            $table->index(['client_id', 'log_date']);
        });

        Schema::create('food_favorites', function (Blueprint $table) {
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('food_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'food_id']);
        });

        Schema::create('habits', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('key', 32)->nullable(); // template key: water|steps|sleep|protein|veggies|meditation|no_sugar
            $table->json('name');
            $table->string('icon', 32)->default('check');
            $table->string('type', 12)->default('boolean'); // boolean|count|duration|numeric
            $table->decimal('target', 10, 2)->nullable();
            $table->string('unit', 16)->nullable();
            $table->string('health_metric', 24)->nullable(); // steps|sleep_minutes|water_ml — Phase 7 auto-complete
            $table->timestamps();
        });

        Schema::create('habit_assignments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('habit_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->string('schedule', 16)->default('daily'); // daily|days|per_week
            $table->json('days')->nullable(); // [1..7] ISO weekdays
            $table->unsignedTinyInteger('per_week')->nullable();
            $table->decimal('target', 10, 2)->nullable();
            $table->json('reminder_times')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['client_id', 'is_active']);
        });

        Schema::create('habit_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('habit_assignment_id')->constrained()->cascadeOnDelete();
            $table->date('log_date');
            $table->decimal('value', 10, 2)->default(1);
            $table->boolean('completed')->default(true);
            $table->string('source', 16)->default('manual'); // manual|health
            $table->timestamps();
            $table->unique(['habit_assignment_id', 'log_date']);
        });

        Schema::create('checkin_forms', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('intro')->nullable();
            $table->json('questions'); // [{id,type,label,required,options,min,max,unit}]
            $table->boolean('include_metrics')->default(true);
            $table->boolean('include_photos')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('checkin_schedules', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('checkin_form_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->string('frequency', 16)->default('weekly'); // weekly|every_n_days|program_week_end
            $table->unsignedTinyInteger('weekday')->nullable(); // 1..7
            $table->unsignedSmallInteger('every_days')->nullable();
            $table->time('due_time')->default('09:00');
            $table->date('next_due_on');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['next_due_on', 'is_active']);
        });

        Schema::create('checkin_responses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('checkin_form_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('checkin_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->date('due_on')->nullable();
            $table->string('status', 16)->default('pending'); // pending|submitted|reviewed|overdue
            $table->json('answers')->nullable();
            $table->json('context')->nullable(); // snapshot of metrics/compliance at submission
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignUlid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('coach_feedback')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('checkin_comments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('checkin_response_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('body_metrics', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->date('measured_on');
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->decimal('body_fat_pct', 5, 2)->nullable();
            $table->json('measurements')->nullable(); // {neck,chest,waist,hips,arm_l,arm_r,thigh_l,thigh_r,calf_l,calf_r} cm
            $table->string('source', 16)->default('manual');
            $table->timestamps();
            $table->index(['client_id', 'measured_on']);
        });

        Schema::create('progress_photos', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('checkin_response_id')->nullable()->constrained()->nullOnDelete();
            $table->date('taken_on');
            $table->string('pose', 8); // front|side|back
            $table->string('disk', 16)->default('private');
            $table->string('path');
            $table->boolean('is_private')->default(true);
            $table->timestamps();
            $table->index(['client_id', 'taken_on']);
        });
    }

    public function down(): void
    {
        foreach (['progress_photos', 'body_metrics', 'checkin_comments', 'checkin_responses', 'checkin_schedules', 'checkin_forms', 'habit_logs', 'habit_assignments', 'habits', 'food_favorites', 'water_logs', 'food_logs', 'meal_items', 'meals', 'meal_plan_days', 'meal_plans', 'nutrition_targets', 'recipe_items', 'recipes', 'foods'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
