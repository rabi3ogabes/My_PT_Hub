'use strict';

const authentication = require('./authentication');
const { addAuthHeaders, handleErrors } = require('./lib/api');

const newClient = require('./triggers/newClient');
const payment = require('./triggers/payment');
const checkinSubmitted = require('./triggers/checkinSubmitted');
const workoutCompleted = require('./triggers/workoutCompleted');
const leadCreated = require('./triggers/leadCreated');
const { clientList, programList } = require('./triggers/lists');

const createClient = require('./creates/createClient');
const assignProgram = require('./creates/assignProgram');
const sendMessage = require('./creates/sendMessage');
const addLead = require('./creates/addLead');

module.exports = {
  version: require('./package.json').version,
  platformVersion: require('zapier-platform-core').version,

  authentication,
  beforeRequest: [addAuthHeaders],
  afterResponse: [handleErrors],

  triggers: {
    [newClient.key]: newClient,
    [payment.key]: payment,
    [checkinSubmitted.key]: checkinSubmitted,
    [workoutCompleted.key]: workoutCompleted,
    [leadCreated.key]: leadCreated,
    [clientList.key]: clientList,
    [programList.key]: programList,
  },

  creates: {
    [createClient.key]: createClient,
    [assignProgram.key]: assignProgram,
    [sendMessage.key]: sendMessage,
    [addLead.key]: addLead,
  },
};
