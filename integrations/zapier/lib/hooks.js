'use strict';

const { baseUrl, data } = require('./api');

/**
 * Builds a REST-hook trigger backed by Aurum webhook endpoints:
 * subscribe = POST /webhook-endpoints, unsubscribe = DELETE /webhook-endpoints/{id}.
 * Deliveries are signed with X-Aurum-Signature (t=<unix>,v1=<hmac>), see README.
 */
const restHookTrigger = ({ key, noun, label, description, event, sample, outputFields }) => {
  const subscribeHook = async (z, bundle) => {
    const response = await z.request({
      method: 'POST',
      url: `${baseUrl(bundle)}/webhook-endpoints`,
      body: { url: bundle.targetUrl, events: [event], description: `Zapier: ${label}` },
    });
    return data(response);
  };

  const unsubscribeHook = async (z, bundle) => {
    const response = await z.request({
      method: 'DELETE',
      url: `${baseUrl(bundle)}/webhook-endpoints/${bundle.subscribeData.id}`,
    });
    return data(response);
  };

  /** Incoming delivery: {id, event, created_at, tenant_id, data: {...}}. */
  const perform = (z, bundle) => {
    const payload = bundle.cleanedRequest || {};
    if (payload.event && payload.event !== event) {
      return [];
    }
    return [{ id: payload.id, event: payload.event, created_at: payload.created_at, ...(payload.data || {}) }];
  };

  /**
   * Sample data for the Zap editor: the most recent real deliveries of this
   * event from the account's webhook endpoints (same shape as live hooks).
   */
  const performList = async (z, bundle) => {
    const endpoints = data(await z.request({ url: `${baseUrl(bundle)}/webhook-endpoints` })) || [];
    const matching = endpoints.filter((e) => (e.events || []).includes(event) || (e.events || []).includes('*'));
    const items = [];
    for (const endpoint of matching.slice(0, 5)) {
      const deliveries = data(await z.request({ url: `${baseUrl(bundle)}/webhook-endpoints/${endpoint.id}/deliveries`, params: { per_page: 10 } })) || [];
      deliveries
        .filter((d) => d.event === event && d.payload)
        .forEach((d) => items.push({ id: d.payload.id, event: d.payload.event, created_at: d.payload.created_at, ...(d.payload.data || {}) }));
    }
    const unique = new Map(items.map((i) => [i.id, i]));
    return Array.from(unique.values()).sort((a, b) => String(b.created_at).localeCompare(String(a.created_at))).slice(0, 10);
  };

  return {
    key,
    noun,
    display: { label, description },
    operation: {
      type: 'hook',
      perform,
      performList,
      performSubscribe: subscribeHook,
      performUnsubscribe: unsubscribeHook,
      sample,
      outputFields,
    },
  };
};

module.exports = { restHookTrigger };
