'use strict';

const { baseUrl, data } = require('../lib/api');

module.exports = {
  key: 'send_message',
  noun: 'Message',
  display: { label: 'Send Message', description: 'Sends an in-app message to a client (opens the direct conversation if needed).' },
  operation: {
    inputFields: [
      { key: 'client_id', label: 'Client', required: true, dynamic: 'client_list.id.name' },
      { key: 'body', label: 'Message', required: true, type: 'text' },
    ],
    perform: async (z, bundle) => {
      const conversation = data(await z.request({
        method: 'POST',
        url: `${baseUrl(bundle)}/conversations`,
        body: { type: 'direct', participant_ids: [bundle.inputData.client_id] },
      }));
      const response = await z.request({
        method: 'POST',
        url: `${baseUrl(bundle)}/conversations/${conversation.id}/messages`,
        body: { type: 'text', body: bundle.inputData.body },
      });
      return data(response);
    },
    sample: { id: '01JC0000000000000000000060', conversation_id: '01JC0000000000000000000061', type: 'text', body: 'See you at 6pm!', channel: 'app', created_at: '2026-11-01T09:00:00+00:00' },
  },
};
