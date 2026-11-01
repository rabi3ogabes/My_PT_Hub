<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('muscle_groups', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('key', 32)->unique();
            $table->json('name');
            $table->string('region', 16); // upper|lower|core|full
        });

        Schema::create('equipment', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('key', 32)->unique();
            $table->json('name');
        });

        Schema::create('exercises', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->nullable()->constrained()->cascadeOnDelete(); // null = global library
            $table->string('slug')->index();
            $table->json('name');
            $table->json('instructions')->nullable(); // {locale: [steps]}
            $table->json('tips')->nullable();
            $table->json('mistakes')->nullable();
            $table->json('aliases')->nullable(); // search synonyms per locale
            $table->string('primary_muscle', 32)->nullable()->index();
            $table->json('secondary_muscles')->nullable();
            $table->json('equipment')->nullable();
            $table->string('pattern', 24)->nullable(); // squat|hinge|push_h|push_v|pull_h|pull_v|lunge|carry|rotation|core|cardio|mobility
            $table->string('mechanics', 12)->nullable(); // compound|isolation
            $table->string('force', 8)->nullable(); // push|pull|static
            $table->string('level', 16)->default('beginner');
            $table->boolean('is_unilateral')->default(false);
            $table->boolean('is_bodyweight')->default(false);
            $table->json('metrics')->nullable(); // ["weight","reps"]
            $table->foreignUlid('progression_parent_id')->nullable()->constrained('exercises')->nullOnDelete();
            $table->unsignedTinyInteger('progression_step')->nullable();
            $table->string('video_provider', 16)->nullable(); // hls|youtube|vimeo|upload
            $table->string('video_url')->nullable();
            $table->string('video_status', 16)->nullable(); // pending|processing|ready|failed
            $table->string('poster_url')->nullable();
            $table->string('preview_url')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('exercise_alternatives', function (Blueprint $table) {
            $table->foreignUlid('exercise_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('alternative_id')->constrained('exercises')->cascadeOnDelete();
            $table->primary(['exercise_id', 'alternative_id']);
        });

        Schema::create('exercise_favorites', function (Blueprint $table) {
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('exercise_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'exercise_id']);
        });

        Schema::create('workouts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('tags')->nullable();
            $table->string('difficulty', 16)->default('intermediate');
            $table->unsignedSmallInteger('estimated_minutes')->default(0);
            $table->json('equipment_summary')->nullable();
            $table->boolean('is_template')->default(false);
            $table->unsignedInteger('version')->default(1);
            $table->foreignUlid('source_id')->nullable()->constrained('workouts')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('workout_blocks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('workout_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('type', 16)->default('straight'); // straight|superset|giant|circuit|tabata|emom|amrap|interval|warmup|cooldown
            $table->string('name')->nullable();
            $table->unsignedSmallInteger('rounds')->default(1);
            $table->unsignedSmallInteger('work_seconds')->nullable();
            $table->unsignedSmallInteger('rest_seconds')->nullable();
            $table->unsignedSmallInteger('rest_between_rounds')->nullable();
            $table->unsignedSmallInteger('time_cap_seconds')->nullable();
            $table->text('notes')->nullable();
            $table->index(['workout_id', 'position']);
        });

        Schema::create('workout_exercises', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('workout_block_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('exercise_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('position');
            $table->unsignedSmallInteger('rest_seconds')->nullable();
            $table->string('tempo', 16)->nullable();
            $table->text('notes')->nullable();
            $table->json('alternative_ids')->nullable();
            $table->json('progression_rule')->nullable(); // {type: linear_load|percent_week|rep_progression, ...}
            $table->index(['workout_block_id', 'position']);
        });

        Schema::create('workout_set_templates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('workout_exercise_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('set_type', 12)->default('working'); // warmup|working|drop|failure|amrap
            $table->unsignedSmallInteger('reps_min')->nullable();
            $table->unsignedSmallInteger('reps_max')->nullable();
            $table->decimal('weight_kg', 7, 2)->nullable();
            $table->decimal('percent_1rm', 5, 2)->nullable();
            $table->decimal('rpe', 3, 1)->nullable();
            $table->unsignedTinyInteger('rir')->nullable();
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->unsignedInteger('distance_m')->nullable();
            $table->unsignedSmallInteger('rest_seconds')->nullable();
            $table->index(['workout_exercise_id', 'position']);
        });

        Schema::create('programs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('slug')->nullable()->index();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('goal', 32)->nullable();
            $table->string('level', 16)->default('intermediate');
            $table->unsignedTinyInteger('days_per_week')->default(3);
            $table->json('equipment')->nullable();
            $table->json('tags')->nullable();
            $table->string('visibility', 16)->default('private'); // private|tenant_template|public
            $table->boolean('is_template')->default(false);
            $table->string('cover_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('program_weeks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('program_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('number');
            $table->string('phase', 24)->nullable(); // hypertrophy|strength|peak|deload
            $table->boolean('is_deload')->default(false);
            $table->text('notes')->nullable();
            $table->unique(['program_id', 'number']);
        });

        Schema::create('program_days', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('program_week_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_number'); // 1..7
            $table->foreignUlid('workout_id')->nullable()->constrained()->nullOnDelete();
            $table->ulid('meal_plan_id')->nullable();
            $table->json('habit_ids')->nullable();
            $table->string('title')->nullable();
            $table->boolean('is_rest')->default(false);
            $table->index(['program_week_id', 'day_number']);
        });

        Schema::create('program_assignments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('program_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date');
            $table->string('status', 16)->default('active'); // scheduled|active|paused|completed|cancelled
            $table->string('unlock_mode', 16)->default('all'); // all|weekly_drip|daily
            $table->date('paused_at')->nullable();
            $table->unsignedSmallInteger('shift_days')->default(0);
            $table->timestamps();
            $table->index(['tenant_id', 'client_id', 'status']);
        });

        Schema::create('workout_sessions', function (Blueprint $table) {
            $table->ulid('id')->primary(); // client-generated ULID allowed
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('workout_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('program_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('program_day_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('status', 16)->default('in_progress'); // in_progress|completed|abandoned
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->decimal('total_volume_kg', 10, 2)->default(0);
            $table->unsignedTinyInteger('perceived_effort')->nullable();
            $table->string('mood', 16)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'client_id', 'started_at']);
        });

        Schema::create('session_sets', function (Blueprint $table) {
            $table->ulid('id')->primary(); // client mutation ULID — idempotent sync
            $table->foreignUlid('workout_session_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('exercise_id')->constrained()->restrictOnDelete();
            $table->ulid('workout_exercise_id')->nullable();
            $table->unsignedSmallInteger('set_number');
            $table->string('set_type', 12)->default('working');
            $table->unsignedSmallInteger('reps')->nullable();
            $table->decimal('weight_kg', 7, 2)->nullable();
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->unsignedInteger('distance_m')->nullable();
            $table->decimal('rpe', 3, 1)->nullable();
            $table->boolean('is_completed')->default(true);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['exercise_id', 'completed_at']);
        });

        Schema::create('personal_records', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('exercise_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('session_set_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 16); // e1rm|weight|reps|volume
            $table->decimal('value', 10, 2);
            $table->decimal('previous_value', 10, 2)->nullable();
            $table->timestamp('achieved_at');
            $table->timestamps();
            $table->index(['client_id', 'exercise_id', 'type']);
        });
    }

    public function down(): void
    {
        foreach (['personal_records', 'session_sets', 'workout_sessions', 'program_assignments', 'program_days', 'program_weeks', 'programs', 'workout_set_templates', 'workout_exercises', 'workout_blocks', 'workouts', 'exercise_favorites', 'exercise_alternatives', 'exercises', 'equipment', 'muscle_groups'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
