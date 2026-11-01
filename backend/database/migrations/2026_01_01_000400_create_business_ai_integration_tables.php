<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------- SaaS billing (coach pays platform) ----------
        Schema::create('saas_plans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('code', 32)->unique(); // free|pro|studio|enterprise
            $table->json('name');
            $table->unsignedInteger('monthly_price_minor')->default(0);
            $table->unsignedInteger('annual_price_minor')->default(0);
            $table->string('currency', 3)->default('USD');
            $table->json('limits'); // {clients: 3, coaches: 1, ai_requests_month: 0, video_mb: 200}
            $table->json('features'); // ["ai_copilot","branded_app",...]
            $table->string('stripe_monthly_price_id')->nullable();
            $table->string('stripe_annual_price_id')->nullable();
            $table->unsignedTinyInteger('trial_days')->default(14);
            $table->unsignedTinyInteger('sort')->default(0);
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        Schema::create('saas_subscriptions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('saas_plan_id')->constrained()->restrictOnDelete();
            $table->string('interval', 8)->default('month'); // month|year
            $table->string('status', 16); // trialing|active|past_due|cancelled|expired
            $table->unsignedInteger('price_minor');
            $table->string('currency', 3);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->string('gateway', 16)->default('stripe');
            $table->string('gateway_customer_id')->nullable();
            $table->string('gateway_subscription_id')->nullable()->index();
            $table->string('card_brand', 16)->nullable();
            $table->string('card_last4', 4)->nullable();
            $table->timestamp('reminder_7d_sent_at')->nullable();
            $table->timestamp('reminder_1d_sent_at')->nullable();
            $table->unsignedTinyInteger('failed_attempts')->default(0);
            $table->timestamps();
            $table->index(['status', 'current_period_end']);
        });

        Schema::create('saas_invoices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('saas_subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number')->unique();
            $table->integer('amount_minor');
            $table->string('currency', 3);
            $table->string('status', 16); // paid|open|refunded|void
            $table->string('gateway_invoice_id')->nullable();
            $table->string('description')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('usage_meters', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('metric', 32); // ai_requests|ai_tokens|video_mb|sms
            $table->string('period', 7); // YYYY-MM
            $table->unsignedBigInteger('value')->default(0);
            $table->unsignedBigInteger('cost_micros')->default(0);
            $table->timestamps();
            $table->unique(['tenant_id', 'metric', 'period']);
        });

        // ---------- Client payments (client pays coach) ----------
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('driver', 16); // stripe|paypal|tap|paytabs|hyperpay|myfatoorah|paymob|flutterwave|manual
            $table->string('label')->nullable();
            $table->text('credentials')->nullable(); // encrypted json
            $table->json('currencies')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_live')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('type', 24); // coaching_package|membership|credit_pack|single_session|digital_program|physical|event_ticket
            $table->string('slug');
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('image_url')->nullable();
            $table->json('fulfilment')->nullable(); // {program_id, meal_plan_id, habit_ids, checkin_form_id, group_id, credits, welcome_message, access_days}
            $table->unsignedSmallInteger('grace_days')->default(3);
            $table->string('sell_in_app_ios', 16)->default('hidden'); // hidden|external|iap
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('prices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained()->cascadeOnDelete();
            $table->string('type', 12)->default('one_time'); // one_time|recurring|installments
            $table->unsignedInteger('amount_minor');
            $table->string('currency', 3);
            $table->string('interval', 8)->nullable(); // week|month|year
            $table->unsignedTinyInteger('interval_count')->default(1);
            $table->unsignedTinyInteger('installments')->nullable();
            $table->unsignedSmallInteger('trial_days')->default(0);
            $table->unsignedInteger('setup_fee_minor')->default(0);
            $table->boolean('tax_inclusive')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('type', 8); // percent|fixed
            $table->unsignedInteger('value'); // percent (0-100) or minor amount
            $table->string('currency', 3)->nullable();
            $table->string('duration', 12)->default('once'); // once|repeating|forever
            $table->unsignedTinyInteger('duration_months')->nullable();
            $table->unsignedInteger('max_redemptions')->nullable();
            $table->unsignedInteger('redemptions')->default(0);
            $table->json('product_ids')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignUlid('referrer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_email')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('status', 16)->default('pending'); // pending|paid|failed|refunded|partially_refunded|cancelled
            $table->string('currency', 3);
            $table->integer('subtotal_minor');
            $table->integer('discount_minor')->default(0);
            $table->integer('tax_minor')->default(0);
            $table->integer('total_minor');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('tax_country', 2)->nullable();
            $table->foreignUlid('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('payment_gateway_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gateway_reference')->nullable()->index();
            $table->string('checkout_url', 1024)->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('price_id')->constrained()->restrictOnDelete();
            $table->string('description');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->integer('unit_amount_minor');
            $table->integer('total_minor');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('order_id')->nullable()->constrained()->nullOnDelete();
            $table->ulid('client_subscription_id')->nullable()->index();
            $table->string('driver', 16);
            $table->string('method', 24)->nullable(); // card|mada|knet|apple_pay|cash|bank_transfer|wallet
            $table->integer('amount_minor');
            $table->string('currency', 3);
            $table->string('status', 16); // succeeded|failed|pending
            $table->string('gateway_payment_id')->nullable()->index();
            $table->string('receipt_path')->nullable();
            $table->foreignUlid('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('payment_id')->constrained()->cascadeOnDelete();
            $table->integer('amount_minor');
            $table->string('reason')->nullable();
            $table->string('status', 16)->default('succeeded');
            $table->string('gateway_refund_id')->nullable();
            $table->timestamps();
        });

        Schema::create('client_subscriptions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('price_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 16); // trialing|active|past_due|paused|cancelled|expired
            $table->unsignedInteger('amount_minor');
            $table->string('currency', 3);
            $table->string('interval', 8);
            $table->unsignedTinyInteger('interval_count')->default(1);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start');
            $table->timestamp('current_period_end')->index();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->unsignedTinyInteger('failed_attempts')->default(0);
            $table->string('gateway_subscription_id')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 12)->default('invoice'); // invoice|credit_note
            $table->unsignedInteger('sequence');
            $table->string('number');
            $table->string('currency', 3);
            $table->integer('subtotal_minor');
            $table->integer('tax_minor');
            $table->integer('total_minor');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('status', 12)->default('paid'); // draft|open|paid|void
            $table->date('due_on')->nullable();
            $table->json('seller'); // snapshot of tenant legal details
            $table->json('buyer');
            $table->json('lines');
            $table->string('locale', 5)->default('en');
            $table->timestamp('issued_at');
            $table->timestamps();
            $table->unique(['tenant_id', 'type', 'sequence']);
        });

        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('type', 12);
            $table->unsignedInteger('last_value')->default(0);
            $table->primary(['tenant_id', 'type']);
        });

        Schema::create('webhook_events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('driver', 16);
            $table->string('external_id');
            $table->string('type', 64)->nullable();
            $table->ulid('tenant_id')->nullable()->index();
            $table->json('payload');
            $table->string('status', 16)->default('received'); // received|processed|failed|ignored
            $table->text('error')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['driver', 'external_id']);
        });

        Schema::create('analytics_daily', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->bigInteger('revenue_minor')->default(0);
            $table->bigInteger('refunds_minor')->default(0);
            $table->bigInteger('mrr_minor')->default(0);
            $table->bigInteger('new_mrr_minor')->default(0);
            $table->bigInteger('churned_mrr_minor')->default(0);
            $table->unsignedInteger('active_clients')->default(0);
            $table->unsignedInteger('new_clients')->default(0);
            $table->unsignedInteger('churned_clients')->default(0);
            $table->unsignedInteger('sessions_delivered')->default(0);
            $table->integer('credit_liability')->default(0);
            $table->string('currency', 3);
            $table->timestamps();
            $table->unique(['tenant_id', 'date']);
        });

        Schema::create('commission_rules', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('coach_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 16); // percent_package|per_session
            $table->unsignedInteger('value'); // basis points or minor amount
            $table->timestamps();
        });

        // ---------- CRM ----------
        Schema::create('lead_stages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('position');
            $table->string('kind', 8)->default('open'); // open|won|lost
            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('lead_stage_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('source', 16)->default('manual'); // profile|form|referral|instagram|whatsapp|manual|marketplace
            $table->text('message')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedInteger('value_minor')->nullable();
            $table->boolean('consent')->default(false);
            $table->foreignUlid('converted_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lead_activities', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 12); // note|task|call|email|stage
            $table->text('body')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('contract_templates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('kind', 16)->default('contract'); // contract|waiver|parq
            $table->longText('body'); // with {client_name} {date} {business_name}
            $table->timestamps();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('contract_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->longText('rendered_body');
            $table->string('status', 12)->default('sent'); // sent|signed|void
            $table->string('signature_type', 8)->nullable(); // draw|type
            $table->longText('signature_data')->nullable();
            $table->string('signed_ip', 45)->nullable();
            $table->string('signed_user_agent')->nullable();
            $table->string('document_hash', 64)->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });

        // ---------- AI ----------
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('tone', 16)->default('motivational'); // motivational|technical|nurturing|tough_love
            $table->string('sign_off')->nullable();
            $table->boolean('use_emoji')->default(true);
            $table->text('philosophy')->nullable();
            $table->json('banned_words')->nullable();
            $table->json('sample_replies')->nullable();
            $table->json('disabled_features')->nullable();
            $table->boolean('opt_out')->default(false);
            $table->timestamps();
            $table->unique(['tenant_id', 'user_id']);
        });

        Schema::create('ai_requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('feature', 32)->index();
            $table->string('provider', 16);
            $table->string('model', 64);
            $table->string('prompt_version', 16)->nullable();
            $table->unsignedInteger('tokens_in')->default(0);
            $table->unsignedInteger('tokens_out')->default(0);
            $table->unsignedBigInteger('cost_micros')->default(0);
            $table->unsignedInteger('latency_ms')->default(0);
            $table->string('status', 16); // ok|invalid|refused|error
            $table->string('error')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_outputs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUlid('ai_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('feature', 32);
            $table->json('input')->nullable();
            $table->json('payload');
            $table->json('grounding')->nullable(); // which data was used
            $table->json('flags')->nullable(); // safety flags
            $table->string('status', 16)->default('draft'); // draft|approved|edited|rejected|applied
            $table->json('edited_payload')->nullable();
            $table->foreignUlid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'feature', 'status']);
        });

        // ---------- Integrations ----------
        Schema::create('integrations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 24); // apple_health|health_connect|garmin|fitbit|whoop|oura|polar|strava|withings|google_calendar|outlook|zoom|whatsapp|mailchimp
            $table->string('status', 16)->default('connected'); // connected|error|revoked
            $table->text('access_token')->nullable(); // encrypted
            $table->text('refresh_token')->nullable(); // encrypted
            $table->timestamp('token_expires_at')->nullable();
            $table->string('external_user_id')->nullable();
            $table->json('scopes')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->string('last_error')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'provider']);
        });

        Schema::create('health_samples', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 24); // steps|active_kcal|heart_rate|resting_hr|hrv|sleep_minutes|weight_kg|body_fat_pct|workout|distance_m|readiness
            $table->decimal('value', 12, 3);
            $table->string('unit', 12);
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->string('source', 24);
            $table->string('external_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'source', 'external_id']);
            $table->index(['user_id', 'type', 'start_at']);
        });

        Schema::create('health_daily', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('type', 24);
            $table->decimal('value', 12, 3);
            $table->string('source', 24);
            $table->timestamps();
            $table->unique(['user_id', 'date', 'type']);
        });

        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('url', 1024);
            $table->text('secret'); // encrypted
            $table->json('events');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('webhook_endpoint_id')->constrained()->cascadeOnDelete();
            $table->string('event', 48);
            $table->json('payload');
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('status', 12)->default('pending'); // pending|delivered|failed
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();
        });

        // ---------- Marketing ----------
        Schema::create('reviews', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('coach_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('body')->nullable();
            $table->text('coach_reply')->nullable();
            $table->string('status', 12)->default('pending'); // pending|approved|rejected
            $table->timestamps();
            $table->unique(['coach_profile_id', 'client_id']);
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('locale', 5);
            $table->string('slug');
            $table->string('title');
            $table->string('excerpt', 500)->nullable();
            $table->longText('body');
            $table->string('category', 32)->nullable();
            $table->string('cover_url')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->boolean('noindex')->default(false);
            $table->ulid('translation_group')->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->unique(['locale', 'slug']);
        });

        Schema::create('marketing_leads', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('email');
            $table->string('kind', 24); // template_download|waitlist|demo|newsletter
            $table->string('resource')->nullable();
            $table->string('locale', 5)->nullable();
            $table->boolean('consent')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['marketing_leads', 'blog_posts', 'reviews', 'webhook_deliveries', 'webhook_endpoints', 'health_daily', 'health_samples', 'integrations', 'ai_outputs', 'ai_requests', 'ai_settings', 'contracts', 'contract_templates', 'lead_activities', 'leads', 'lead_stages', 'commission_rules', 'analytics_daily', 'webhook_events', 'invoice_sequences', 'invoices', 'client_subscriptions', 'refunds', 'payments', 'order_items', 'orders', 'coupons', 'prices', 'products', 'payment_gateways', 'usage_meters', 'saas_invoices', 'saas_subscriptions', 'saas_plans'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
