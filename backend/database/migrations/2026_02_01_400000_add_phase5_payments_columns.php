<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Phase 5 — client payments: access windows, dunning state, credit notes, ZATCA QR, signed contract PDFs. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('access_expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->ulid('client_subscription_id')->nullable()->index();
            $table->index(['access_expires_at', 'revoked_at']);
        });

        Schema::table('client_subscriptions', function (Blueprint $table) {
            $table->ulid('payment_gateway_id')->nullable()->index();
            $table->string('gateway_customer_id')->nullable();
            $table->text('gateway_payment_method')->nullable(); // encrypted token for off-session renewals
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamp('pause_requested_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('ended_reason', 16)->nullable(); // cancelled|payment_failed|completed
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedTinyInteger('installments_total')->nullable();
            $table->unsignedTinyInteger('installments_paid')->default(0);
            $table->index('gateway_subscription_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->ulid('credit_note_for_id')->nullable()->index();
            $table->ulid('refund_id')->nullable();
            $table->text('qr_tlv')->nullable(); // ZATCA phase-1 TLV (base64)
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->string('pdf_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('contracts', fn (Blueprint $table) => $table->dropColumn('pdf_path'));
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['credit_note_for_id']);
            $table->dropColumn(['credit_note_for_id', 'refund_id', 'qr_tlv']);
        });
        Schema::table('client_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['payment_gateway_id']);
            $table->dropIndex(['gateway_subscription_id']);
            $table->dropColumn(['payment_gateway_id', 'gateway_customer_id', 'gateway_payment_method', 'next_retry_at', 'pause_requested_at', 'ended_at', 'ended_reason', 'revoked_at', 'installments_total', 'installments_paid']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['access_expires_at', 'revoked_at']);
            $table->dropIndex(['client_subscription_id']);
            $table->dropColumn(['access_expires_at', 'revoked_at', 'client_subscription_id']);
        });
    }
};
