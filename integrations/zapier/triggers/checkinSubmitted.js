'use strict';

const { restHookTrigger } = require('../lib/hooks');

module.exports = restHookTrigger({
  key: 'checkin_submitted',
  noun: 'Check-in',
  label: 'Check-in Submitted',
  description: 'Triggers when a client submits a check-in.',
  event: 'checkin.submitted',
  sample: {
    id: '01JC0000000000000000000020',
    event: 'checkin.submitted',
    created_at: '2026-11-01T09:00:00+00:00',
    response: { id: '01JC0000000000000000000021', client_id: '01JC0000000000000000000001', status: 'submitted', submitted_at: '2026-11-01T09:00:00+00:00' },
  },
  outputFields: [
    { key: 'response__id', label: 'Check-in ID' },
    { key: 'response__client_id', label: 'Client ID' },
    { key: 'response__submitted_at', label: 'Submitted at', type: 'datetime' },
  ],
});
