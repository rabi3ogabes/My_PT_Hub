'use strict';

/** Base URL of the tenant's Aurum API (no trailing slash). */
const baseUrl = (bundle) => String(bundle.authData.base_url || 'https://api.aurum.coach').replace(/\/+$/, '') + '/api/v1';

/** Every request carries the personal access token; Aurum pins the tenant from the token itself. */
const addAuthHeaders = (request, z, bundle) => {
  if (bundle.authData && bundle.authData.api_token) {
    request.headers = request.headers || {};
    request.headers.Authorization = `Bearer ${bundle.authData.api_token}`;
    request.headers.Accept = 'application/json';
    request.headers['Accept-Language'] = bundle.authData.locale || 'en';
  }
  return request;
};

/** Surfaces Aurum's error envelope {errors: [{code, message}]} as readable Zapier errors. */
const handleErrors = (response, z) => {
  if (response.status === 401) {
    throw new z.errors.RefreshAuthError('Your Aurum API token is invalid or expired.');
  }
  if (response.status >= 400) {
    let message = `Aurum API error (${response.status})`;
    try {
      const body = response.json || JSON.parse(response.content);
      if (body && body.errors && body.errors[0]) {
        message = `${body.errors[0].message} [${body.errors[0].code}]`;
      }
    } catch (e) {
      // Non-JSON body: keep the generic message.
    }
    throw new z.errors.Error(message, 'AurumApiError', response.status);
  }
  return response;
};

/** Unwraps the {data, meta, links, errors} envelope. */
const data = (response) => (response.json ? response.json.data : JSON.parse(response.content).data);

module.exports = { baseUrl, addAuthHeaders, handleErrors, data };
