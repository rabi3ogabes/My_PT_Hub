<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6 additions: review bookkeeping on AI drafts (what the coach changed,
 * why it was rejected, which real artifact an applied draft created) and a
 * drafting-language preference on coach AI settings.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_outputs', function (Blueprint $table) {
            $table->json('edited_diff')->nullable();
            $table->text('review_note')->nullable();
            $table->string('applied_type', 32)->nullable();
            $table->ulid('applied_id')->nullable();
            $table->index(['tenant_id', 'client_id', 'created_at']);
        });

        Schema::table('ai_settings', function (Blueprint $table) {
            $table->string('language', 5)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            $table->dropColumn('language');
        });

        Schema::table('ai_outputs', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'client_id', 'created_at']);
            $table->dropColumn(['edited_diff', 'review_note', 'applied_type', 'applied_id']);
        });
    }
};
