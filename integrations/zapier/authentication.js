'use strict';

const { baseUrl } = require('./lib/api');

/**
 * Personal access token created in Aurum → Settings → Developer → API tokens.
 * Recommended scopes: webhooks:manage, clients:read, clients:write, programs:read,
 * programs:write, messages:write, leads:write.
 */
module.exports = {
  type: 'custom',
  fields: [
    {
      key: 'api_token',
      label: 'API token',
      type: 'password',
      required: true,
      helpText: 'Create a token in Aurum → Settings → Developer → API tokens. It is shown only once.',
    },
    {
      key: 'base_url',
      label: 'API base URL',
      type: 'string',
      required: false,
      default: 'https://api.aurum.coach',
      helpText: 'Leave as is unless your business uses a custom domain.',
    },
  ],
  test: async (z, bundle) => {
    const response = await z.request({ url: `${baseUrl(bundle)}/me` });
    return response.json.data;
  },
  connectionLabel: '{{user.name}} ({{user.email}})',
};
