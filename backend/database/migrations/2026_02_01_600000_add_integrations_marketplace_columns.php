<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->unsignedSmallInteger('failure_count')->default(0);
            $table->timestamp('next_attempt_at')->nullable();
            $table->index(['provider', 'external_user_id']);
        });

        Schema::table('health_samples', function (Blueprint $table) {
            // Set when a sample from another source describes the same event (time-window dedupe).
            $table->ulid('duplicate_of_id')->nullable()->index();
        });

        Schema::table('webhook_endpoints', function (Blueprint $table) {
            $table->string('description')->nullable();
            $table->timestamp('disabled_at')->nullable();
        });

        Schema::table('webhook_deliveries', function (Blueprint $table) {
            $table->text('response_body')->nullable();
            $table->string('error')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->index(['status', 'next_retry_at']);
            $table->index(['webhook_endpoint_id', 'created_at']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false);
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('moderated_at')->nullable();
            $table->index(['coach_profile_id', 'status']);
        });

        Schema::create('review_requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('coach_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('sent_at');
            $table->timestamps();
            $table->unique(['tenant_id', 'client_id']);
        });

        Schema::create('calendar_event_links', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('event_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('integration_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->timestamps();
            $table->unique(['event_id', 'integration_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_event_links');
        Schema::dropIfExists('review_requests');
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['coach_profile_id', 'status']);
            $table->dropColumn(['is_verified', 'replied_at', 'moderated_at']);
        });
        Schema::table('webhook_deliveries', function (Blueprint $table) {
            $table->dropIndex(['status', 'next_retry_at']);
            $table->dropIndex(['webhook_endpoint_id', 'created_at']);
            $table->dropColumn(['response_body', 'error', 'duration_ms', 'delivered_at']);
        });
        Schema::table('webhook_endpoints', function (Blueprint $table) {
            $table->dropColumn(['description', 'disabled_at']);
        });
        Schema::table('health_samples', function (Blueprint $table) {
            $table->dropIndex(['duplicate_of_id']);
            $table->dropColumn('duplicate_of_id');
        });
        Schema::table('integrations', function (Blueprint $table) {
            $table->dropIndex(['provider', 'external_user_id']);
            $table->dropColumn(['failure_count', 'next_attempt_at']);
        });
    }
};
