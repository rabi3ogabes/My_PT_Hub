'use strict';

const { restHookTrigger } = require('../lib/hooks');

module.exports = restHookTrigger({
  key: 'workout_completed',
  noun: 'Workout',
  label: 'Workout Completed',
  description: 'Triggers when a client completes a workout session.',
  event: 'workout.completed',
  sample: {
    id: '01JC0000000000000000000030',
    event: 'workout.completed',
    created_at: '2026-11-01T09:00:00+00:00',
    session_id: '01JC0000000000000000000031',
    client: { id: '01JC0000000000000000000001', name: 'Client Name', email: 'client@example.com', phone: null },
    workout_id: '01JC0000000000000000000032',
    name: 'Upper body A',
    started_at: '2026-11-01T08:00:00+00:00',
    ended_at: '2026-11-01T08:55:00+00:00',
    duration_seconds: 3300,
    total_volume_kg: 8420.5,
    personal_records: 2,
  },
  outputFields: [
    { key: 'session_id', label: 'Session ID' },
    { key: 'client__name', label: 'Client name' },
    { key: 'name', label: 'Workout name' },
    { key: 'duration_seconds', label: 'Duration (seconds)', type: 'integer' },
    { key: 'total_volume_kg', label: 'Total volume (kg)', type: 'number' },
    { key: 'personal_records', label: 'Personal records', type: 'integer' },
  ],
});
