<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 (workouts) additions:
 * - exercises.search_text: normalised multi-locale haystack (names + aliases) for fast LIKE search;
 *   exercises.video_path / video_size_bytes for the upload → HLS pipeline and offline download sizing.
 * - workout_sessions / session_sets soft deletes so offline deletes sync as tombstones (sets are never hard-deleted).
 * - personal_records.workout_session_id + weight_kg (rep PRs are "reps at a weight").
 * - sync_mutations: applied offline mutation ids → idempotent /sync/push.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->text('search_text')->nullable();
            $table->string('video_path')->nullable();
            $table->unsignedBigInteger('video_size_bytes')->nullable();
        });

        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->softDeletes();
            $table->index(['client_id', 'updated_at']);
        });

        Schema::table('session_sets', function (Blueprint $table) {
            $table->softDeletes();
            $table->index(['workout_session_id', 'updated_at']);
        });

        Schema::table('personal_records', function (Blueprint $table) {
            $table->foreignUlid('workout_session_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('weight_kg', 7, 2)->nullable();
        });

        Schema::create('sync_mutations', function (Blueprint $table) {
            $table->ulid('id')->primary(); // client mutation ULID
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('entity', 24);
            $table->string('op', 8);
            $table->string('status', 16); // applied|rejected
            $table->string('error', 64)->nullable();
            $table->timestamp('client_time')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_mutations');

        Schema::table('personal_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('workout_session_id');
            $table->dropColumn('weight_kg');
        });

        Schema::table('session_sets', function (Blueprint $table) {
            $table->dropIndex(['workout_session_id', 'updated_at']);
            $table->dropSoftDeletes();
        });

        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->dropIndex(['client_id', 'updated_at']);
            $table->dropSoftDeletes();
        });

        Schema::table('exercises', function (Blueprint $table) {
            $table->dropColumn(['search_text', 'video_path', 'video_size_bytes']);
        });
    }
};
