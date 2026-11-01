'use strict';

const { restHookTrigger } = require('../lib/hooks');

module.exports = restHookTrigger({
  key: 'lead_created',
  noun: 'Lead',
  label: 'New Lead',
  description: 'Triggers when a new lead arrives (profile contact form, coach finder, or CRM).',
  event: 'lead.created',
  sample: {
    id: '01JC0000000000000000000040',
    event: 'lead.created',
    created_at: '2026-11-01T09:00:00+00:00',
    lead: { id: '01JC0000000000000000000041', name: 'Lead Name', email: 'lead@example.com', phone: null, source: 'profile', message: 'I would like to start coaching.', tags: ['marketplace'], consent: true },
    coach: { id: '01JC0000000000000000000042', profile_slug: 'coach-name' },
  },
  outputFields: [
    { key: 'lead__id', label: 'Lead ID' },
    { key: 'lead__name', label: 'Lead name' },
    { key: 'lead__email', label: 'Lead email' },
    { key: 'lead__phone', label: 'Lead phone' },
    { key: 'lead__source', label: 'Source' },
    { key: 'lead__message', label: 'Message' },
  ],
});
