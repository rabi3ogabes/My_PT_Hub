<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3 additions: accent/diacritic-insensitive search text for foods and
 * recipes (all locales + aliases), the plan item a food log came from (so
 * planned items can be flagged as logged) and check-in reminder bookkeeping.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $table->text('search_text')->nullable();
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->text('search_text')->nullable();
        });

        Schema::table('food_logs', function (Blueprint $table) {
            $table->ulid('meal_item_id')->nullable()->index();
        });

        Schema::table('checkin_responses', function (Blueprint $table) {
            $table->timestamp('reminded_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('checkin_responses', function (Blueprint $table) {
            $table->dropColumn(['reminded_at', 'escalated_at']);
        });
        Schema::table('food_logs', function (Blueprint $table) {
            $table->dropIndex(['meal_item_id']);
            $table->dropColumn('meal_item_id');
        });
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn('search_text');
        });
        Schema::table('foods', function (Blueprint $table) {
            $table->dropColumn('search_text');
        });
    }
};
