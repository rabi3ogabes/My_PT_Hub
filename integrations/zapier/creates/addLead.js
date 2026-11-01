'use strict';

const { baseUrl, data } = require('../lib/api');

module.exports = {
  key: 'add_lead',
  noun: 'Lead',
  display: { label: 'Add Lead', description: 'Adds a lead to your CRM pipeline.' },
  operation: {
    inputFields: [
      { key: 'name', label: 'Name', required: true },
      { key: 'email', label: 'Email' },
      { key: 'phone', label: 'Phone' },
      { key: 'message', label: 'Notes', type: 'text' },
      { key: 'tags', label: 'Tags', list: true },
      { key: 'consent', label: 'Marketing consent given', type: 'boolean', default: 'false' },
    ],
    perform: async (z, bundle) => {
      const response = await z.request({
        method: 'POST',
        url: `${baseUrl(bundle)}/leads`,
        body: {
          name: bundle.inputData.name,
          email: bundle.inputData.email || null,
          phone: bundle.inputData.phone || null,
          message: bundle.inputData.message || null,
          tags: bundle.inputData.tags || [],
          source: 'form',
          consent: bundle.inputData.consent === true || bundle.inputData.consent === 'true',
        },
      });
      return data(response);
    },
    sample: { id: '01JC0000000000000000000041', name: 'Lead Name', email: 'lead@example.com', source: 'form' },
  },
};
