<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('type', 16)->default('direct'); // direct|group|broadcast|checkin
            $table->string('title')->nullable();
            $table->ulid('subject_id')->nullable(); // checkin_response id etc
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 16)->default('member'); // owner|member
            $table->boolean('is_muted')->default(false);
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();
            $table->unique(['conversation_id', 'user_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->ulid('id')->primary(); // client ULID for idempotent offline send
            $table->foreignUlid('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 16)->default('text'); // text|voice|image|video|file|share|system
            $table->text('body')->nullable(); // encrypted at rest
            $table->json('meta')->nullable(); // share card payload, waveform, duration, transcript
            $table->foreignUlid('reply_to_id')->nullable()->constrained('messages')->nullOnDelete();
            $table->string('channel', 16)->default('app'); // app|whatsapp|email
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['conversation_id', 'created_at']);
        });

        Schema::create('message_attachments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('message_id')->constrained()->cascadeOnDelete();
            $table->string('disk', 16)->default('private');
            $table->string('path');
            $table->string('mime', 128);
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('waveform')->nullable();
            $table->timestamps();
        });

        Schema::create('message_reactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('message_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('emoji', 16);
            $table->timestamps();
            $table->unique(['message_id', 'user_id', 'emoji']);
        });

        Schema::create('message_templates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('body'); // {locale: text with {first_name}}
            $table->timestamps();
        });

        Schema::create('scheduled_messages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('sender_id')->constrained('users')->cascadeOnDelete();
            $table->json('segment'); // {client_ids:[], tags:[], group_ids:[], program_id, at_risk:bool}
            $table->json('body'); // {locale: text}
            $table->timestamp('send_at')->index();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->timestamps();
        });

        Schema::create('automations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('trigger', 32); // client_joined|program_started|program_finished|workout_missed|checkin_overdue|pr_hit|birthday|package_expiring|payment_failed
            $table->json('trigger_config')->nullable(); // {days:3}
            $table->json('conditions')->nullable(); // [{field,op,value}]
            $table->json('actions'); // [{type: send_message|assign_habit|assign_program|notify_coach|add_tag, ...}]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('automation_runs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('automation_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('dedupe_key')->unique(); // guarantees fire-once
            $table->string('status', 16)->default('done');
            $table->json('result')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->ulidMorphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('community_spaces', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignUlid('client_group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('program_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('announcement_only')->default(false);
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('community_space_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 16)->default('text'); // text|media|poll|workout
            $table->text('body')->nullable();
            $table->json('meta')->nullable(); // poll options, media, workout share
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('reactions_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('post_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('author_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulidMorphs('reactable');
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('emoji', 16)->default('🔥');
            $table->timestamps();
            $table->unique(['reactable_type', 'reactable_id', 'user_id']);
        });

        Schema::create('content_reports', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->ulidMorphs('reportable');
            $table->foreignUlid('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 32);
            $table->string('status', 16)->default('open');
            $table->timestamps();
        });

        Schema::create('challenges', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('community_space_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('metric', 24); // steps|workouts|habit_streak|volume|custom
            $table->decimal('goal', 12, 2)->nullable();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('badge_key', 32)->nullable();
            $table->timestamps();
        });

        Schema::create('challenge_entries', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('team')->nullable();
            $table->decimal('score', 12, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['challenge_id', 'user_id']);
            $table->index(['challenge_id', 'score']);
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('badge_key', 32); // first_workout|streak_7|streak_30|streak_100|pr_10|checkin_streak_4|challenge_*
            $table->timestamp('awarded_at');
            $table->unique(['tenant_id', 'user_id', 'badge_key']);
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('timezone', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('availability_rules', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('coach_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday')->nullable(); // 1..7, null for date override
            $table->date('date')->nullable(); // override
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->boolean('is_unavailable')->default(false); // holiday
            $table->string('timezone', 64);
            $table->timestamps();
        });

        Schema::create('booking_settings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('coach_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('slot_minutes')->default(60);
            $table->unsignedSmallInteger('buffer_minutes')->default(0);
            $table->unsignedSmallInteger('min_notice_hours')->default(12);
            $table->unsignedSmallInteger('max_advance_days')->default(60);
            $table->unsignedSmallInteger('cancel_window_hours')->default(24);
            $table->string('no_show_policy', 16)->default('forfeit'); // forfeit|fee|none
            $table->unsignedSmallInteger('waitlist_claim_minutes')->default(120);
            $table->boolean('public_booking')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'coach_id']);
        });

        Schema::create('events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('coach_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 16)->default('session'); // session|class|event|live|block
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at');
            $table->string('timezone', 64);
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->unsignedSmallInteger('credits_cost')->default(1);
            $table->string('rrule')->nullable();
            $table->json('exdates')->nullable();
            $table->foreignUlid('series_id')->nullable()->constrained('events')->cascadeOnDelete();
            $table->string('meeting_url')->nullable();
            $table->string('external_id')->nullable(); // google/outlook event id
            $table->string('status', 16)->default('scheduled'); // scheduled|cancelled
            $table->timestamps();
            $table->softDeletes();
            $table->index(['coach_id', 'starts_at']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('event_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 16)->default('booked'); // booked|waitlisted|attended|no_show|late_cancel|cancelled
            $table->unsignedSmallInteger('waitlist_position')->nullable();
            $table->timestamp('waitlist_offered_at')->nullable();
            $table->string('checkin_code', 16)->unique();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('reminded_24h_at')->nullable();
            $table->timestamp('reminded_2h_at')->nullable();
            $table->timestamps();
            $table->unique(['event_id', 'client_id']);
        });

        Schema::create('credit_ledger', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->integer('amount'); // + purchase, - deduction
            $table->string('reason', 24); // purchase|attended|no_show|late_cancel|adjustment|refund|expiry
            $table->ulid('booking_id')->nullable();
            $table->ulid('order_id')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->date('expires_on')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['tenant_id', 'client_id']);
            $table->unique(['booking_id', 'reason']); // a booking can be charged once per reason
        });
    }

    public function down(): void
    {
        foreach (['credit_ledger', 'bookings', 'events', 'booking_settings', 'availability_rules', 'locations', 'user_badges', 'challenge_entries', 'challenges', 'content_reports', 'reactions', 'comments', 'posts', 'community_spaces', 'notifications', 'automation_runs', 'automations', 'scheduled_messages', 'message_templates', 'message_reactions', 'message_attachments', 'messages', 'conversation_participants', 'conversations'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
