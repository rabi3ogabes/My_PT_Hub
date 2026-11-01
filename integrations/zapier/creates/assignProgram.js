'use strict';

const { baseUrl, data } = require('../lib/api');

module.exports = {
  key: 'assign_program',
  noun: 'Program assignment',
  display: { label: 'Assign Program', description: 'Assigns a training program to a client.' },
  operation: {
    inputFields: [
      { key: 'program_id', label: 'Program', required: true, dynamic: 'program_list.id.name' },
      { key: 'client_id', label: 'Client', required: true, dynamic: 'client_list.id.name' },
      { key: 'start_date', label: 'Start date', type: 'datetime', helpText: 'Defaults to today.' },
      { key: 'unlock_mode', label: 'Unlock mode', choices: ['all', 'weekly_drip', 'daily'], default: 'all' },
    ],
    perform: async (z, bundle) => {
      const start = bundle.inputData.start_date ? String(bundle.inputData.start_date).slice(0, 10) : new Date().toISOString().slice(0, 10);
      const response = await z.request({
        method: 'POST',
        url: `${baseUrl(bundle)}/programs/${bundle.inputData.program_id}/assign`,
        body: { client_ids: [bundle.inputData.client_id], start_date: start, unlock_mode: bundle.inputData.unlock_mode || 'all' },
      });
      const assignments = data(response) || [];
      return assignments[0] || {};
    },
    sample: { id: '01JC0000000000000000000050', program: { id: '01JC0000000000000000000051', name: 'Strength 101' }, client: { id: '01JC0000000000000000000001', name: 'Client Name' }, start_date: '2026-11-02', status: 'active' },
  },
};
