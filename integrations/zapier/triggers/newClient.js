'use strict';

const { restHookTrigger } = require('../lib/hooks');

module.exports = restHookTrigger({
  key: 'new_client',
  noun: 'Client',
  label: 'New Client',
  description: 'Triggers when a client joins your coaching business.',
  event: 'client.created',
  sample: {
    id: '01JC0000000000000000000000',
    event: 'client.created',
    created_at: '2026-11-01T09:00:00+00:00',
    client: { id: '01JC0000000000000000000001', name: 'Client Name', email: 'client@example.com', phone: '+97400000000' },
    joined_at: '2026-11-01T09:00:00+00:00',
  },
  outputFields: [
    { key: 'client__id', label: 'Client ID' },
    { key: 'client__name', label: 'Client name' },
    { key: 'client__email', label: 'Client email' },
    { key: 'client__phone', label: 'Client phone' },
    { key: 'joined_at', label: 'Joined at', type: 'datetime' },
  ],
});
