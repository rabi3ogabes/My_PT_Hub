'use strict';

const { baseUrl, data } = require('../lib/api');

module.exports = {
  key: 'create_client',
  noun: 'Client',
  display: { label: 'Create Client', description: 'Invites a new client to your coaching business.' },
  operation: {
    inputFields: [
      { key: 'name', label: 'Full name', required: true },
      { key: 'email', label: 'Email', required: true },
      { key: 'phone', label: 'Phone (with country code)' },
      { key: 'goal', label: 'Goal', choices: ['fat_loss', 'muscle_gain', 'strength', 'performance', 'health'] },
      { key: 'program_id', label: 'Program to assign', dynamic: 'program_list.id.name' },
    ],
    perform: async (z, bundle) => {
      const response = await z.request({
        method: 'POST',
        url: `${baseUrl(bundle)}/clients`,
        body: {
          name: bundle.inputData.name,
          email: bundle.inputData.email,
          phone: bundle.inputData.phone || null,
          goal: bundle.inputData.goal || null,
          program_id: bundle.inputData.program_id || null,
        },
      });
      return data(response);
    },
    sample: { id: '01JC0000000000000000000001', name: 'Client Name', email: 'client@example.com', status: 'invited' },
  },
};
