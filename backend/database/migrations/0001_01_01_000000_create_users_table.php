<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 32)->nullable()->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('locale', 5)->default('en');
            $table->string('timezone', 64)->default('UTC');
            $table->string('units', 10)->default('metric'); // metric|imperial
            $table->string('theme', 10)->default('system');
            $table->boolean('is_super_admin')->default(false);
            $table->ulid('current_tenant_id')->nullable()->index();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->string('apple_id')->nullable()->unique();
            $table->string('google_id')->nullable()->unique();
            $table->json('notification_preferences')->nullable();
            $table->json('quiet_hours')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->unsignedSmallInteger('failed_logins')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUlid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('tenants', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('type', 16)->default('business'); // business|personal
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();
            $table->string('brand_color', 9)->default('#D4AF6A');
            $table->string('currency', 3)->default('USD');
            $table->string('country', 2)->nullable();
            $table->string('timezone', 64)->default('UTC');
            $table->string('locale', 5)->default('en');
            $table->string('plan', 32)->default('free');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('onboarded_at')->nullable();
            $table->json('settings')->nullable();
            $table->json('legal')->nullable(); // legal name, VAT number, address for invoices
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tenant_user', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 16); // owner|coach|assistant|client
            $table->string('status', 16)->default('active'); // active|invited|archived
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'role']);
        });

        Schema::create('invitations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code', 32)->unique();
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('name')->nullable();
            $table->string('role', 16)->default('client');
            $table->json('payload')->nullable(); // program to assign, tags...
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->foreignUlid('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'email']);
        });

        Schema::create('devices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('platform', 16); // ios|android|web
            $table->string('push_token', 512)->nullable();
            $table->string('app', 16)->default('client'); // client|coach
            $table->string('app_version', 32)->nullable();
            $table->string('locale', 5)->nullable();
            $table->string('timezone', 64)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'push_token']);
        });

        Schema::create('magic_links', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('email')->index();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tenant_id')->nullable()->index();
            $table->ulid('causer_id')->nullable()->index();
            $table->ulid('impersonator_id')->nullable();
            $table->string('event', 64)->index();
            $table->nullableUlidMorphs('subject');
            $table->json('properties')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('feature_flags', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('key')->unique();
            $table->string('description')->nullable();
            $table->boolean('enabled')->default(false);
            $table->json('plans')->nullable(); // restrict to plans
            $table->json('tenants')->nullable(); // allow-list tenant ids
            $table->unsignedTinyInteger('rollout')->default(100);
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('locale', 5);
            $table->string('group', 64)->default('*');
            $table->string('key');
            $table->text('value');
            $table->timestamps();
            $table->unique(['locale', 'group', 'key']);
        });

        Schema::create('coach_profiles', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('headline')->nullable();
            $table->json('bio')->nullable(); // translatable
            $table->json('specialties')->nullable();
            $table->json('languages')->nullable();
            $table->json('certifications')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('gender', 16)->nullable();
            $table->boolean('offers_online')->default(true);
            $table->boolean('offers_in_person')->default(false);
            $table->unsignedInteger('price_from_minor')->nullable();
            $table->string('instagram')->nullable();
            $table->string('intro_video_url')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->timestamps();
            $table->index(['is_published', 'city']);
        });

        Schema::create('client_profiles', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('coach_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 16)->default('active'); // lead|active|paused|archived
            $table->string('goal', 32)->nullable(); // fat_loss|muscle_gain|strength|performance|health
            $table->string('level', 16)->nullable(); // beginner|intermediate|advanced
            $table->text('date_of_birth')->nullable(); // encrypted
            $table->string('gender', 16)->nullable();
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->string('activity_level', 16)->nullable();
            $table->text('injuries')->nullable(); // encrypted
            $table->text('medical_notes')->nullable(); // encrypted
            $table->json('equipment')->nullable();
            $table->unsignedTinyInteger('days_per_week')->nullable();
            $table->json('diet_tags')->nullable();
            $table->json('tags')->nullable();
            $table->json('onboarding')->nullable();
            $table->boolean('parq_flagged')->default(false);
            $table->unsignedTinyInteger('compliance_score')->nullable();
            $table->string('risk_status', 8)->default('green'); // green|amber|red
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'coach_id', 'status']);
        });

        Schema::create('client_groups', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 9)->nullable();
            $table->timestamps();
        });

        Schema::create('client_group_members', function (Blueprint $table) {
            $table->foreignUlid('client_group_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['client_group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        foreach (['client_group_members', 'client_groups', 'client_profiles', 'coach_profiles', 'translations', 'feature_flags', 'activity_logs', 'magic_links', 'devices', 'invitations', 'tenant_user', 'tenants', 'sessions', 'password_reset_tokens', 'users'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
