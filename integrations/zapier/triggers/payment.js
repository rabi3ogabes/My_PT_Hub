'use strict';

const { restHookTrigger } = require('../lib/hooks');

module.exports = restHookTrigger({
  key: 'payment',
  noun: 'Payment',
  label: 'New Payment',
  description: 'Triggers when a client payment succeeds (online, manual or free order).',
  event: 'payment.succeeded',
  sample: {
    id: '01JC0000000000000000000010',
    event: 'payment.succeeded',
    created_at: '2026-11-01T09:00:00+00:00',
    order_id: '01JC0000000000000000000011',
    client_id: '01JC0000000000000000000001',
    customer_email: 'client@example.com',
    customer_name: 'Client Name',
    amount_minor: 39900,
    currency: 'QAR',
    status: 'paid',
    paid_at: '2026-11-01T09:00:00+00:00',
  },
  outputFields: [
    { key: 'order_id', label: 'Order ID' },
    { key: 'amount_minor', label: 'Amount (minor units)', type: 'integer' },
    { key: 'currency', label: 'Currency' },
    { key: 'customer_email', label: 'Customer email' },
    { key: 'paid_at', label: 'Paid at', type: 'datetime' },
  ],
});
