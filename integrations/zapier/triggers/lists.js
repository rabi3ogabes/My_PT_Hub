'use strict';

const { baseUrl, data } = require('../lib/api');

/** Hidden polling triggers that feed dynamic dropdowns in actions. */
const listTrigger = (key, noun, path, map) => ({
  key,
  noun,
  display: { label: `List ${noun}s`, description: `Lists ${noun.toLowerCase()}s for dropdowns.`, hidden: true },
  operation: {
    perform: async (z, bundle) => {
      const response = await z.request({ url: `${baseUrl(bundle)}${path}`, params: { per_page: 100, cursor: bundle.meta && bundle.meta.page ? bundle.meta.cursor : undefined } });
      return (data(response) || []).map(map);
    },
    canPaginate: false,
  },
});

module.exports = {
  clientList: listTrigger('client_list', 'Client', '/clients', (c) => ({ id: c.id, name: `${c.name} <${c.email}>` })),
  programList: listTrigger('program_list', 'Program', '/programs', (p) => ({ id: p.id, name: p.name })),
};
