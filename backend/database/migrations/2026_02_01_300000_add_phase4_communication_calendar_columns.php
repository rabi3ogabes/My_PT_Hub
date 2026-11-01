<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 4 additions:
 *  - conversations.direct_key: one direct conversation per pair of users per tenant (DB-enforced).
 *  - events.slot_key: DB-level double-booking guard for 1:1 slots (coach + start instant).
 *  - events.recurrence_id: original occurrence start of a materialized recurring instance.
 *  - poll_votes: one vote per user per poll post.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('direct_key', 96)->nullable()->unique();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('slot_key', 96)->nullable()->unique();
            $table->timestamp('recurrence_id')->nullable();
            $table->unique(['series_id', 'recurrence_id']);
        });

        Schema::create('poll_votes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('post_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('option');
            $table->timestamps();
            $table->unique(['post_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['series_id', 'recurrence_id']);
            $table->dropUnique(['slot_key']);
            $table->dropColumn(['slot_key', 'recurrence_id']);
        });
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropUnique(['direct_key']);
            $table->dropColumn('direct_key');
        });
    }
};
