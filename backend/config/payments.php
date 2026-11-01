<?php

/*
| Client → coach payments (Phase 5).
*/
return [
    // Optional platform fee taken on Stripe Connect charges, in basis points (100 = 1%). Default 0%.
    'platform_fee_bps' => (int) env('PAYMENTS_PLATFORM_FEE_BPS', 0),

    // VAT / sales tax by tenant country (percent). Tenants may disable tax in settings.payments.tax_enabled.
    'tax_rates' => [
        'QA' => 0,
        'SA' => 15,
        'AE' => 5,
        'FR' => 20,
        'MA' => 20,
        'EG' => 14,
    ],

    // Dunning: retry a failed renewal after these many days, then auto-pause.
    'dunning_retry_days' => [1, 3, 5],

    // Manual (cash / transfer) invoices are due after this many days.
    'manual_due_days' => (int) env('PAYMENTS_MANUAL_DUE_DAYS', 7),
];
