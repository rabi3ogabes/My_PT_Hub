// Component schemas for docs/openapi.yaml. Field names mirror docs/API_CONTRACT.md exactly;
// shapes not fixed by the contract mirror the database columns (docs/ERD.md).
//
// Shorthand accepted by T():
//   'string' 'int' 'num' 'bool' 'date' 'datetime' 'time' 'ulid' 'uri' 'email' 'json' 'any'
//   'a|b|c'      string enum           '$Name'   $ref to components.schemas.Name
//   '[x]'        array of x            'x?'      nullable x
//   { ... }      inline object (all listed keys required unless suffixed with '?' in the key)

export function T(spec) {
  if (spec && typeof spec === 'object' && !Array.isArray(spec)) return obj(spec);
  let s = String(spec);
  let nullable = false;
  if (s.endsWith('?')) { nullable = true; s = s.slice(0, -1); }
  let out;
  if (s.startsWith('[') && s.endsWith(']')) out = { type: 'array', items: T(s.slice(1, -1)) };
  else if (s.startsWith('$')) out = { $ref: `#/components/schemas/${s.slice(1)}` };
  else if (s.includes('|')) out = { type: 'string', enum: s.split('|') };
  else {
    out = ({
      string: { type: 'string' }, int: { type: 'integer' }, num: { type: 'number' }, bool: { type: 'boolean' },
      date: { type: 'string', format: 'date' }, datetime: { type: 'string', format: 'date-time' },
      time: { type: 'string', pattern: '^\\d{2}:\\d{2}$' }, ulid: { $ref: '#/components/schemas/Ulid' },
      uri: { type: 'string', format: 'uri' }, email: { type: 'string', format: 'email' },
      json: { type: 'object', additionalProperties: true }, any: {},
      binary: { type: 'string', format: 'binary' },
    })[s];
    if (!out) throw new Error(`Unknown type shorthand: ${spec}`);
  }
  if (!nullable) return out;
  if (out.$ref) return { oneOf: [out, { type: 'null' }] };
  if (out.type) return { ...out, type: [out.type, 'null'], ...(out.enum ? { enum: [...out.enum, null] } : {}) };
  return out;
}

export function obj(fields, { required = true, additional = false } = {}) {
  const properties = {};
  const req = [];
  for (const [k, v] of Object.entries(fields)) {
    const optional = k.endsWith('?');
    const name = optional ? k.slice(0, -1) : k;
    properties[name] = T(v);
    if (required && !optional) req.push(name);
  }
  const out = { type: 'object', properties };
  if (req.length) out.required = req;
  if (additional) out.additionalProperties = true;
  return out;
}

const Macros = { kcal: 'num', protein_g: 'num', carbs_g: 'num', fat_g: 'num' };

export const schemas = {
  Ulid: { type: 'string', pattern: '^[0-9A-HJKMNP-TV-Z]{26}$', description: 'ULID (26 chars, Crockford base32). Client-generated for offline rows.', examples: ['01JBX6Q5Z8K3M4N5P6Q7R8S9T0'] },
  Locale: { type: 'string', enum: ['en', 'ar', 'fr', 'es'] },
  Money: obj({ amount_minor: 'int', currency: 'string', formatted: 'string' }),
  Macros: obj(Macros),
  Translations: { type: 'object', description: 'Raw translatable value keyed by locale.', properties: { en: { type: 'string' }, ar: { type: 'string' }, fr: { type: 'string' }, es: { type: 'string' } }, additionalProperties: false },
  ErrorItem: obj({ code: 'string', message: 'string', 'field_errors?': { type: 'object', additionalProperties: { type: 'array', items: { type: 'string' } } } }),
  Envelope: obj({ data: 'any', meta: 'json', links: 'json', errors: '[$ErrorItem]' }),
  ErrorEnvelope: obj({ data: { type: 'null' }, meta: 'json', links: 'json', errors: '[$ErrorItem]' }),
  CursorMeta: obj({ per_page: 'int', next_cursor: 'string?', prev_cursor: 'string?' }),
  PageMeta: obj({ per_page: 'int', current_page: 'int', total: 'int', last_page: 'int' }),
  Links: obj({ next: 'uri?', prev: 'uri?' }),

  // ------------------------------------------------------------ identity
  User: obj({
    id: 'ulid', name: 'string', first_name: 'string', email: 'email', phone: 'string?', avatar_url: 'uri?', locale: '$Locale', timezone: 'string',
    units: 'metric|imperial', theme: 'light|dark|system', email_verified: 'bool', two_factor_enabled: 'bool', current_tenant_id: 'ulid?', is_super_admin: 'bool',
    notification_preferences: { type: 'object', additionalProperties: obj({ push: 'bool', email: 'bool' }) },
    quiet_hours: obj({ enabled: 'bool', from: 'time?', to: 'time?' }), created_at: 'datetime',
  }),
  Tenant: obj({
    id: 'ulid', type: 'business|personal', name: 'string', slug: 'string', logo_url: 'uri?', brand_color: 'string', currency: 'string', country: 'string?',
    timezone: 'string', locale: '$Locale', plan: 'free|pro|studio|enterprise', trial_ends_at: 'datetime?', onboarded: 'bool', role: 'owner|coach|assistant|client',
  }),
  Tokens: obj({ access_token: 'string', refresh_token: 'string', token_type: 'Bearer', expires_at: 'datetime', refresh_expires_at: 'datetime' }),
  AuthSession: { allOf: [{ $ref: '#/components/schemas/Tokens' }, obj({ user: '$User', tenants: '[$Tenant]' })] },
  TwoFactorRequired: obj({ two_factor_required: { type: 'boolean', const: true }, challenge: 'string' }),
  Me: obj({ user: '$User', tenants: '[$Tenant]' }),
  DeviceFields: obj({ 'device_name?': 'string', 'platform?': 'ios|android|web', 'push_token?': 'string', 'app?': 'client|coach', 'app_version?': 'string', 'timezone?': 'string' }),
  RegisterRequest: obj({
    name: 'string', email: 'email', password: { type: 'string', minLength: 8 }, account_type: 'coach|client|standalone', 'invite_code?': 'string', 'business_name?': 'string',
    'currency?': 'string', 'country?': 'string', 'locale?': '$Locale', terms: { type: 'boolean', const: true }, 'device_name?': 'string', 'platform?': 'ios|android|web',
    'push_token?': 'string', 'app?': 'client|coach', 'app_version?': 'string', 'timezone?': 'string',
  }),
  InvitationPreview: obj({ code: 'string', role: 'coach|assistant|client', email: 'email?', name: 'string?', tenant: obj({ name: 'string', logo_url: 'uri?', brand_color: 'string' }), coach: obj({ name: 'string' }) }),
  Notification: obj({ id: 'string', category: 'string', title: 'string', body: 'string', link: 'string?', data: 'json', read_at: 'datetime?', created_at: 'datetime' }),
  TwoFactorSetup: obj({ secret: 'string', otpauth_url: 'string', qr_svg: 'string', recovery_codes: '[string]' }),
  ExportStatus: obj({ status: 'queued', message: 'string' }),

  // ------------------------------------------------------------ marketplace
  PublicCoach: obj({
    slug: 'string', name: 'string', avatar_url: 'uri?', headline: 'string?', bio: 'string?', specialties: '[string]', languages: '[string]', certifications: '[json]',
    city: 'string?', country: 'string?', offers_online: 'bool', offers_in_person: 'bool', price_from: '$Money?', is_verified: 'bool', rating_avg: 'num', rating_count: 'int',
    'packages?': '[$Product]', 'reviews?': '[$Review]',
  }),
  Review: obj({ id: 'ulid', rating: { type: 'integer', minimum: 1, maximum: 5 }, body: 'string?', author_name: 'string', coach_reply: 'string?', created_at: 'datetime' }),
  CoachProfile: obj({
    id: 'ulid', slug: 'string', headline: 'string?', bio: 'string?', bio_translations: '$Translations', specialties: '[string]', languages: '[string]', certifications: '[json]',
    city: 'string?', country: 'string?', gender: 'string?', offers_online: 'bool', offers_in_person: 'bool', price_from_minor: 'int?', instagram: 'string?', intro_video_url: 'uri?',
    is_published: 'bool', is_verified: 'bool', rating_avg: 'num', rating_count: 'int',
  }),
  LeadCapture: obj({ name: 'string', email: 'email', 'phone?': 'string', 'message?': 'string', consent: { type: 'boolean', const: true } }),
  MatchRequest: obj({ goal: 'string', 'city?': 'string', 'online?': 'bool', 'language?': '$Locale', 'budget_minor?': 'int', 'coach_gender?': 'string' }),

  // ------------------------------------------------------------ today
  TodayWorkout: obj({ workout_id: 'ulid', program_day_id: 'ulid?', program_assignment_id: 'ulid?', name: 'string', estimated_minutes: 'int?', exercise_count: 'int', week_number: 'int?', day_number: 'int?', status: 'pending|in_progress|completed', session_id: 'ulid?' }),
  Today: obj({
    date: 'date', greeting_name: 'string', coach: obj({ id: 'ulid', name: 'string', avatar_url: 'uri?' }, {}).properties ? { oneOf: [obj({ id: 'ulid', name: 'string', avatar_url: 'uri?' }), { type: 'null' }] } : null,
    workout: '$TodayWorkout?', streak: obj({ current: 'int', best: 'int' }), next_session: '$Booking?',
    nutrition: obj({ targets: '$Macros?', consumed: '$Macros', water_ml: 'int', water_target_ml: 'int' }),
    habits: obj({ completed: 'int', total: 'int' }), checkin_due: { oneOf: [obj({ id: 'ulid', due_on: 'date', form_name: 'string' }), { type: 'null' }] }, unread_messages: 'int',
  }),

  // ------------------------------------------------------------ workouts
  ExerciseVideo: obj({ provider: 'hls|youtube|vimeo|upload?', url: 'uri?', poster_url: 'uri?', preview_url: 'uri?', status: 'string?' }),
  ExerciseListItem: obj({
    id: 'ulid', slug: 'string', name: 'string', primary_muscle: 'string', secondary_muscles: '[string]', equipment: '[string]', pattern: 'string?', mechanics: 'string?',
    force: 'string?', level: 'string?', is_unilateral: 'bool', is_bodyweight: 'bool', metrics: '[string]', video: '$ExerciseVideo', is_custom: 'bool', is_favorite: 'bool',
  }),
  Exercise: { allOf: [{ $ref: '#/components/schemas/ExerciseListItem' }, obj({ instructions: '[string]', tips: '[string]', mistakes: '[string]', alternatives: '[' + '$ExerciseRef' + ']' })] },
  ExerciseRef: obj({ id: 'ulid', name: 'string' }),
  ExerciseWrite: obj({ 'name?': { oneOf: [{ type: 'string' }, { $ref: '#/components/schemas/Translations' }] }, 'primary_muscle?': 'string', 'secondary_muscles?': '[string]', 'equipment?': '[string]', 'pattern?': 'string', 'mechanics?': 'compound|isolation', 'force?': 'push|pull|static', 'level?': 'beginner|intermediate|advanced', 'is_unilateral?': 'bool', 'is_bodyweight?': 'bool', 'metrics?': '[string]', 'instructions?': 'any', 'tips?': 'any', 'mistakes?': 'any', 'video_url?': 'uri' }, { required: false }),
  SetTemplate: obj({ 'id?': 'ulid', position: 'int', set_type: 'warmup|working|drop|failure|amrap', reps_min: 'int?', reps_max: 'int?', weight_kg: 'num?', percent_1rm: 'num?', rpe: 'num?', rir: 'int?', duration_seconds: 'int?', distance_m: 'num?', rest_seconds: 'int?' }),
  WorkoutExercise: obj({ 'id?': 'ulid', position: 'int', exercise: '$ExerciseListItem', rest_seconds: 'int?', tempo: 'string?', notes: 'string?', alternative_ids: '[ulid]', sets: '[$SetTemplate]', 'last_performance?': '$LastPerformance?' }),
  Block: obj({ 'id?': 'ulid', position: 'int', type: 'straight|superset|giant|circuit|tabata|emom|amrap|interval|warmup|cooldown', name: 'string?', rounds: 'int?', work_seconds: 'int?', rest_seconds: 'int?', rest_between_rounds: 'int?', time_cap_seconds: 'int?', notes: 'string?', exercises: '[$WorkoutExercise]' }),
  Workout: obj({ id: 'ulid', name: 'string', description: 'string?', difficulty: 'string?', estimated_minutes: 'int?', tags: '[string]', equipment_summary: '[string]', blocks: '[$Block]', updated_at: 'datetime', 'download_size_bytes?': 'int' }),
  WorkoutWrite: obj({
    name: 'string', 'description?': 'string', 'difficulty?': 'string', 'tags?': '[string]',
    blocks: { type: 'array', items: obj({ 'id?': 'ulid', 'position?': 'int', type: 'string', 'name?': 'string', 'rounds?': 'int', 'work_seconds?': 'int', 'rest_seconds?': 'int', 'rest_between_rounds?': 'int', 'time_cap_seconds?': 'int', 'notes?': 'string', exercises: { type: 'array', items: obj({ exercise_id: 'ulid', 'position?': 'int', 'rest_seconds?': 'int', 'tempo?': 'string', 'notes?': 'string', 'alternative_ids?': '[ulid]', sets: '[$SetTemplate]' }) } }) },
  }),
  ProgramDay: obj({ id: 'ulid', day_number: 'int', title: 'string?', is_rest: 'bool', workout: { oneOf: [obj({ id: 'ulid', name: 'string', estimated_minutes: 'int?' }), { type: 'null' }] }, meal_plan_id: 'ulid?', habit_ids: '[ulid]' }),
  ProgramWeek: obj({ id: 'ulid', number: 'int', phase: 'string?', is_deload: 'bool', notes: 'string?', days: '[$ProgramDay]' }),
  Program: obj({ id: 'ulid', name: 'string', description: 'string?', goal: 'string?', level: 'string?', days_per_week: 'int?', equipment: '[string]', tags: '[string]', visibility: 'private|tenant|public', is_template: 'bool', weeks_count: 'int', weeks: '[$ProgramWeek]' }),
  ProgramWrite: obj({ name: 'string', 'description?': 'string', 'goal?': 'string', 'level?': 'string', 'days_per_week?': 'int', 'equipment?': '[string]', 'tags?': '[string]', 'visibility?': 'private|tenant|public', 'is_template?': 'bool', 'weeks?': { type: 'array', items: obj({ number: 'int', 'phase?': 'string', 'is_deload?': 'bool', 'notes?': 'string', days: { type: 'array', items: obj({ day_number: 'int', 'title?': 'string', 'is_rest?': 'bool', 'workout_id?': 'ulid?', 'meal_plan_id?': 'ulid?', 'habit_ids?': '[ulid]' }) } }) } }),
  AssignProgram: obj({ client_ids: '[ulid]', start_date: 'date', 'unlock_mode?': 'all|weekly_drip|daily' }),
  ProgramAssignment: obj({ id: 'ulid', program: '$ExerciseRef', client: '$ExerciseRef', start_date: 'date', status: 'active|paused|completed|cancelled', unlock_mode: 'all|weekly_drip|daily', current_week: 'int', progress_pct: 'num' }),
  ClientProgram: obj({ assignment: '$ProgramAssignment', program: '$Program', unlocked_through: 'date' }),
  SessionSet: obj({ id: 'ulid', exercise_id: 'ulid', workout_exercise_id: 'ulid?', set_number: 'int', set_type: 'warmup|working|drop|failure|amrap', reps: 'int?', weight_kg: 'num?', duration_seconds: 'int?', distance_m: 'num?', rpe: 'num?', is_completed: 'bool', completed_at: 'datetime?' }),
  PersonalRecord: obj({ id: 'ulid', exercise_id: 'ulid', exercise_name: 'string', type: 'e1rm|weight|reps|volume', value: 'num', previous_value: 'num?', achieved_at: 'datetime' }),
  WorkoutSession: obj({ id: 'ulid', workout_id: 'ulid?', program_day_id: 'ulid?', name: 'string', status: 'in_progress|completed|abandoned', started_at: 'datetime', ended_at: 'datetime?', duration_seconds: 'int?', total_volume_kg: 'num?', perceived_effort: 'int?', mood: 'string?', notes: 'string?', sets: '[$SessionSet]', personal_records: '[$PersonalRecord]' }),
  StartSession: obj({ 'id?': 'ulid', 'workout_id?': 'ulid', 'program_day_id?': 'ulid', 'program_assignment_id?': 'ulid', 'name?': 'string', 'started_at?': 'datetime' }),
  CompleteSession: obj({ 'ended_at?': 'datetime', 'perceived_effort?': { type: 'integer', minimum: 1, maximum: 10 }, 'mood?': 'string', 'notes?': 'string' }),
  LastPerformance: obj({ session_id: 'ulid', performed_at: 'datetime', sets: { type: 'array', items: obj({ set_number: 'int', reps: 'int?', weight_kg: 'num?', duration_seconds: 'int?', distance_m: 'num?', rpe: 'num?' }) } }),
  ExerciseHistory: obj({ best_e1rm_kg: 'num?', best_weight_kg: 'num?', sessions: { type: 'array', items: obj({ session_id: 'ulid', date: 'date', top_set: obj({ reps: 'int?', weight_kg: 'num?' }), volume_kg: 'num', e1rm_kg: 'num?' }) } }),

  // ------------------------------------------------------------ nutrition
  Food: obj({
    id: 'ulid', name: 'string', brand: 'string?', barcode: 'string?', source: 'usda|off|custom|regional|string', region: 'string?', category: 'string?',
    per_100g: obj({ kcal: 'num', protein_g: 'num', carbs_g: 'num', fat_g: 'num', fiber_g: 'num?', sugar_g: 'num?', sodium_mg: 'num?' }),
    servings: { type: 'array', items: obj({ unit: 'string', grams: 'num', label: 'string' }) }, diet_tags: '[string]', allergens: '[string]', is_verified: 'bool', is_custom: 'bool', is_favorite: 'bool',
  }),
  FoodWrite: obj({ name: 'any', 'brand?': 'string', 'barcode?': 'string', 'category?': 'string', kcal: 'num', protein_g: 'num', carbs_g: 'num', fat_g: 'num', 'fiber_g?': 'num', 'sugar_g?': 'num', 'sodium_mg?': 'num', 'servings?': 'any', 'diet_tags?': '[string]', 'allergens?': '[string]' }),
  Recipe: obj({ id: 'ulid', slug: 'string', name: 'string', description: 'string?', cuisine: 'string?', prep_minutes: 'int?', cook_minutes: 'int?', servings: 'int', tags: '[string]', photo_url: 'uri?', per_serving: '$Macros', items: { type: 'array', items: obj({ food: '$Food', grams: 'num', display_qty: 'num?', display_unit: 'string?' }) }, steps: '[string]' }),
  RecipeWrite: obj({ name: 'any', 'description?': 'any', 'cuisine?': 'string', 'prep_minutes?': 'int', 'cook_minutes?': 'int', servings: 'int', 'tags?': '[string]', items: { type: 'array', items: obj({ food_id: 'ulid', grams: 'num', 'display_qty?': 'num', 'display_unit?': 'string' }) }, 'steps?': 'any' }),
  FoodLog: obj({ id: 'ulid', log_date: 'date', slot: 'string', food_id: 'ulid?', recipe_id: 'ulid?', name: 'string', quantity: 'num', unit: 'string', ...Macros, source: 'search|barcode|quick|photo|plan|copy', logged_at: 'datetime' }),
  FoodLogWrite: obj({ 'id?': 'ulid', log_date: 'date', slot: 'string', 'food_id?': 'ulid', 'recipe_id?': 'ulid', 'name?': 'string', quantity: 'num', unit: 'string', 'kcal?': 'num', 'protein_g?': 'num', 'carbs_g?': 'num', 'fat_g?': 'num', source: 'search|barcode|quick|photo|plan|copy' }),
  PlannedItem: obj({ meal_item_id: 'ulid', food_id: 'ulid?', recipe_id: 'ulid?', name: 'string', quantity: 'num', unit: 'string', ...Macros, logged: 'bool' }),
  NutritionDay: obj({
    date: 'date', targets: '$Macros?', water_ml: 'int', water_target_ml: 'int', consumed: '$Macros',
    meals: { type: 'array', items: obj({ slot: 'string', label: 'string', consumed: '$Macros', logs: '[$FoodLog]', planned: '[$PlannedItem]' }) },
    fasting: { oneOf: [obj({ mode: 'none|intermittent|ramadan', window_start: 'time?', window_end: 'time?' }), { type: 'null' }] },
  }),
  NutritionWeek: obj({ from: 'date', to: 'date', days: { type: 'array', items: obj({ date: 'date', consumed: '$Macros', targets: '$Macros?', water_ml: 'int' }) }, averages: '$Macros', adherence_pct: 'num' }),
  MealPlan: obj({ id: 'ulid', name: 'string', type: 'fixed|macros|portions|fasting|ramadan', 'diet_tags?': '[string]', 'is_template?': 'bool', 'client_id?': 'ulid?', days: { type: 'array', items: obj({ day_number: 'int', label: 'string?', meals: { type: 'array', items: obj({ slot: 'string', name: 'string?', time: 'time?', items: '[$PlannedItem]' }) } }) } }),
  MealPlanWrite: obj({ name: 'string', type: 'fixed|macros|portions|fasting|ramadan', 'diet_tags?': '[string]', 'is_template?': 'bool', 'fasting?': 'json', days: { type: 'array', items: obj({ day_number: 'int', 'label?': 'string', 'day_type?': 'training|rest', meals: { type: 'array', items: obj({ slot: 'string', 'name?': 'string', 'time?': 'time', items: { type: 'array', items: obj({ 'food_id?': 'ulid', 'recipe_id?': 'ulid', quantity: 'num', unit: 'string' }) } }) } }) } }),
  GroceryList: obj({ aisles: { type: 'array', items: obj({ aisle: 'string', items: { type: 'array', items: obj({ food_id: 'ulid', name: 'string', grams: 'num', display: 'string' }) } }) } }),
  NutritionTarget: obj({ id: 'ulid', day_type: 'default|training|rest', kcal: 'int', protein_g: 'int', carbs_g: 'int', fat_g: 'int', fiber_g: 'int?', water_ml: 'int?', effective_from: 'date', calculation: 'json?', override_safety: 'bool' }),
  CalculateTargets: obj({ sex: 'male|female', age: 'int', weight_kg: 'num', height_cm: 'num', 'body_fat_pct?': 'num', activity_level: 'sedentary|light|moderate|active|very_active', goal: 'fat_loss|maintenance|muscle_gain', 'formula?': 'mifflin|katch', 'protein_g_per_kg?': 'num', 'split?': 'string' }),
  PhotoFoodResult: obj({ candidates: { type: 'array', items: obj({ food: '$Food?', name: 'string', grams: 'num', confidence: 'num', ...Macros }) }, ai_output_id: 'ulid' }),

  // ------------------------------------------------------------ habits / check-ins / progress
  Habit: obj({ id: 'ulid', key: 'string?', name: 'string', icon: 'string?', type: 'boolean|count|duration|numeric', target: 'num?', unit: 'string?', health_metric: 'string?' }),
  HabitToday: obj({ assignment_id: 'ulid', habit: '$Habit', target: 'num?', scheduled_today: 'bool', value: 'num?', completed: 'bool', streak: obj({ current: 'int', best: 'int' }), completion_pct_30d: 'num', reminder_times: '[time]' }),
  HabitAssignment: obj({ id: 'ulid', habit: '$Habit', client_id: 'ulid', schedule: 'daily|days|per_week', days: '[int]', per_week: 'int?', target: 'num?', reminder_times: '[time]', start_date: 'date?', end_date: 'date?', is_active: 'bool' }),
  CheckinQuestion: obj({ id: 'string', type: 'short_text|long_text|scale|single_choice|multi_choice|number|photo|measurement|file', label: 'string', required: 'bool', 'options?': '[string]', 'min?': 'num', 'max?': 'num', 'unit?': 'string' }),
  CheckinForm: obj({ id: 'ulid', name: 'string', intro: 'string?', questions: '[$CheckinQuestion]', include_metrics: 'bool', include_photos: 'bool' }),
  CheckinListItem: obj({ id: 'ulid', form_name: 'string', due_on: 'date', status: 'pending|submitted|reviewed|overdue', submitted_at: 'datetime?', reviewed_at: 'datetime?', 'client?': '$ExerciseRef' }),
  CheckinComment: obj({ id: 'ulid', user: '$ExerciseRef', body: 'string', created_at: 'datetime' }),
  CheckinDetail: obj({ id: 'ulid', status: 'pending|submitted|reviewed|overdue', due_on: 'date', form: '$CheckinForm', answers: 'json?', coach_feedback: 'string?', comments: '[$CheckinComment]', 'context?': 'json' }),
  CheckinSubmit: obj({ answers: 'json', 'weight_kg?': 'num', 'body_fat_pct?': 'num', 'measurements?': { type: 'object', additionalProperties: { type: 'number' } }, 'photo_ids?': '[ulid]' }),
  ProgressPhoto: obj({ id: 'ulid', pose: 'front|side|back', taken_on: 'date', url: 'uri' }),
  BodyMetricWrite: obj({ measured_on: 'date', 'weight_kg?': 'num', 'body_fat_pct?': 'num', 'measurements?': { type: 'object', additionalProperties: { type: 'number' } } }),
  ProgressSummary: obj({
    weight: { type: 'array', items: obj({ date: 'date', weight_kg: 'num' }) }, body_fat: { type: 'array', items: obj({ date: 'date', value: 'num' }) },
    measurements: { type: 'array', items: obj({ date: 'date', values: { type: 'object', additionalProperties: { type: 'number' } } }) }, photos: '[$ProgressPhoto]', records: '[$PersonalRecord]',
    compliance: obj({ score: 'num', workouts_pct: 'num', nutrition_pct: 'num', habits_pct: 'num', checkins_pct: 'num' }),
  }),

  // ------------------------------------------------------------ messaging
  Participant: obj({ id: 'ulid', name: 'string', avatar_url: 'uri?', role: 'string' }),
  Conversation: obj({ id: 'ulid', type: 'direct|group|broadcast|checkin', title: 'string?', participants: '[$Participant]', last_message: '$Message?', unread_count: 'int', is_muted: 'bool', updated_at: 'datetime' }),
  Attachment: obj({ id: 'ulid', mime: 'string', size: 'int', url: 'uri', duration_ms: 'int?', waveform: '[num]?' }),
  Message: obj({
    id: 'ulid', conversation_id: 'ulid', sender: { oneOf: [obj({ id: 'ulid', name: 'string', avatar_url: 'uri?' }), { type: 'null' }] }, type: 'text|voice|image|video|file|share|system',
    body: 'string?', meta: 'json', attachments: '[$Attachment]', reply_to: { oneOf: [obj({ id: 'ulid', body: 'string?', sender_name: 'string' }), { type: 'null' }] },
    reactions: { type: 'array', items: obj({ emoji: 'string', count: 'int', mine: 'bool' }) }, channel: 'app|whatsapp|email|string', edited_at: 'datetime?', created_at: 'datetime', deleted: 'bool',
  }),
  SendMessage: obj({ 'id?': 'ulid', type: 'text|voice|image|video|file|share', 'body?': 'string', 'reply_to_id?': 'ulid', 'meta?': 'json' }),
  SendMessageMultipart: obj({ 'id?': 'ulid', type: 'voice|image|video|file', file: 'binary', 'body?': 'string', 'reply_to_id?': 'ulid' }),
  Broadcast: obj({ id: 'ulid', segment: 'json', body: 'string', send_at: 'datetime', sent_at: 'datetime?', recipients_count: 'int' }),
  BroadcastWrite: obj({ segment: obj({ 'tags?': '[string]', 'group_ids?': '[ulid]', 'program_ids?': '[ulid]', 'risk?': '[string]', 'client_ids?': '[ulid]' }, { required: false }), body: 'string', 'send_at?': 'datetime' }),
  MessageTemplate: obj({ id: 'ulid', name: 'string', body: 'string', body_translations: '$Translations' }),
  Automation: obj({ id: 'ulid', name: 'string', trigger: 'client_joined|program_started|program_finished|workout_missed|checkin_overdue|pr_hit|birthday|package_expiring|payment_failed', trigger_config: 'json', conditions: '[json]', actions: '[json]', is_active: 'bool' }),

  // ------------------------------------------------------------ community
  Space: obj({ id: 'ulid', name: 'string', description: 'string?', client_group_id: 'ulid?', program_id: 'ulid?', announcement_only: 'bool' }),
  Post: obj({ id: 'ulid', author: '$Participant', type: 'text|media|poll|workout_share', body: 'string?', meta: 'json', is_pinned: 'bool', comments_count: 'int', reactions_count: 'int', my_reaction: 'string?', created_at: 'datetime' }),
  Comment: obj({ id: 'ulid', author: '$Participant', body: 'string', created_at: 'datetime' }),
  Challenge: obj({ id: 'ulid', name: 'string', description: 'string?', metric: 'steps|workouts|habit_streak|volume|custom', goal: 'num?', starts_on: 'date', ends_on: 'date', badge_key: 'string?', joined: 'bool', 'leaderboard?': { type: 'array', items: obj({ user: '$Participant', score: 'num', rank: 'int' }) } }),
  Badge: obj({ key: 'string', name: 'string', awarded_at: 'datetime?' }),
  Report: obj({ id: 'ulid', reportable_type: 'post|comment|message', reportable_id: 'ulid', reason: 'string', status: 'open|resolved|dismissed', created_at: 'datetime' }),

  // ------------------------------------------------------------ calendar
  Event: obj({ id: 'ulid', type: 'session|class|event|live|block', title: 'string', description: 'string?', starts_at: 'datetime', ends_at: 'datetime', timezone: 'string', capacity: 'int?', booked_count: 'int', waitlist_count: 'int', credits_cost: 'int', coach: '$ExerciseRef', location: '$ExerciseRef?', meeting_url: 'uri?', rrule: 'string?', status: 'scheduled|cancelled|completed' }),
  EventWrite: obj({ type: 'session|class|event|live|block', title: 'string', 'description?': 'string', starts_at: 'datetime', ends_at: 'datetime', 'timezone?': 'string', 'capacity?': 'int', 'credits_cost?': 'int', 'coach_id?': 'ulid', 'location_id?': 'ulid', 'meeting_url?': 'uri', 'rrule?': 'string', 'client_ids?': '[ulid]', 'scope?': 'this|following|all' }),
  Booking: obj({ id: 'ulid', event: '$Event', status: 'booked|waitlisted|attended|no_show|late_cancel|cancelled', waitlist_position: 'int?', checkin_code: 'string?', can_cancel_free_until: 'datetime?', created_at: 'datetime' }),
  Slot: obj({ starts_at: 'datetime', ends_at: 'datetime' }),
  Credits: obj({ balance: 'int', entries: { type: 'array', items: obj({ id: 'ulid', amount: 'int', reason: 'string', note: 'string?', created_at: 'datetime' }) } }),
  Availability: obj({ timezone: 'string', rules: { type: 'array', items: obj({ 'weekday?': 'int?', 'date?': 'date?', starts_at: 'time', ends_at: 'time', 'is_unavailable?': 'bool' }) }, settings: obj({ slot_minutes: 'int', buffer_minutes: 'int', min_notice_hours: 'int', max_advance_days: 'int', cancel_window_hours: 'int', no_show_policy: 'forfeit|fee|none', waitlist_claim_minutes: 'int', public_booking: 'bool' }) }),
  Location: obj({ id: 'ulid', name: 'string', address: 'string?', timezone: 'string' }),

  // ------------------------------------------------------------ payments
  Price: obj({ id: 'ulid', type: 'one_time|recurring|installments', amount: '$Money', interval: 'day|week|month|year?', interval_count: 'int?', installments: 'int?', trial_days: 'int?' }),
  Product: obj({ id: 'ulid', type: 'package|membership|credit_pack|session|digital_program|physical|ticket', slug: 'string', name: 'string', description: 'string?', image_url: 'uri?', prices: '[$Price]', ios_purchase_mode: 'hidden|external|iap', 'fulfilment?': 'json', 'is_active?': 'bool' }),
  ProductWrite: obj({ type: 'package|membership|credit_pack|session|digital_program|physical|ticket', name: 'any', 'description?': 'any', 'image_url?': 'uri', 'fulfilment?': obj({ 'program_id?': 'ulid', 'meal_plan_id?': 'ulid', 'habit_ids?': '[ulid]', 'checkin_form_id?': 'ulid', 'group_id?': 'ulid', 'credits?': 'int', 'welcome_message?': 'string' }, { required: false }), 'grace_days?': 'int', 'sell_in_app_ios?': 'hidden|external|iap', 'is_active?': 'bool', prices: { type: 'array', items: obj({ 'id?': 'ulid', type: 'one_time|recurring|installments', amount_minor: 'int', currency: 'string', 'interval?': 'day|week|month|year', 'interval_count?': 'int', 'installments?': 'int', 'trial_days?': 'int', 'setup_fee_minor?': 'int', 'tax_inclusive?': 'bool' }) } }),
  CheckoutRequest: obj({ price_id: 'ulid', 'coupon?': 'string' }),
  PublicCheckoutRequest: obj({ price_id: 'ulid', email: 'email', name: 'string', 'coupon?': 'string', 'locale?': '$Locale' }),
  CheckoutResult: obj({ order_id: 'ulid', checkout_url: 'uri?', status: 'pending|paid|failed|cancelled' }),
  OrderStatus: obj({ status: 'pending|paid|failed|refunded|cancelled', fulfilled: 'bool' }),
  ClientSubscription: obj({ id: 'ulid', product_name: 'string', status: 'trialing|active|past_due|paused|cancelled|expired', amount: '$Money', interval: 'string', current_period_end: 'datetime?', cancel_at_period_end: 'bool', 'client?': '$ExerciseRef' }),
  Order: obj({ id: 'ulid', status: 'pending|paid|failed|refunded|partially_refunded|cancelled', client: '$ExerciseRef?', customer_email: 'email?', items: { type: 'array', items: obj({ product_id: 'ulid?', description: 'string', quantity: 'int', unit_amount: '$Money', total: '$Money' }) }, subtotal: '$Money', discount: '$Money', tax: '$Money', total: '$Money', gateway: 'string?', paid_at: 'datetime?', fulfilled_at: 'datetime?', created_at: 'datetime', 'payments?': '[json]', 'refunds?': '[json]' }),
  Purchases: obj({ orders: '[$Order]', subscriptions: '[$ClientSubscription]' }),
  Invoice: obj({ id: 'ulid', number: 'string', type: 'invoice|credit_note', status: 'issued|paid|void|due', total: '$Money', tax: '$Money', due_on: 'date?', issued_at: 'datetime', pdf_url: 'uri' }),
  ManualPayment: obj({ client_id: 'ulid', 'product_id?': 'ulid', 'price_id?': 'ulid', amount_minor: 'int', currency: 'string', method: 'cash|bank_transfer|other', 'paid_at?': 'datetime', 'note?': 'string', 'receipt?': 'binary' }),
  Refund: obj({ amount_minor: 'int', 'reason?': 'string' }),
  Gateway: obj({ id: 'ulid', driver: 'stripe|paypal|tap|paytabs|hyperpay|myfatoorah|paymob|flutterwave|manual', label: 'string', currencies: '[string]', is_default: 'bool', is_live: 'bool', is_active: 'bool', connected: 'bool' }),
  GatewayWrite: obj({ driver: 'stripe|paypal|tap|paytabs|hyperpay|myfatoorah|paymob|flutterwave|manual', 'label?': 'string', 'credentials?': 'json', 'currencies?': '[string]', 'is_default?': 'bool', 'is_live?': 'bool', 'is_active?': 'bool' }),
  Coupon: obj({ id: 'ulid', code: 'string', type: 'percent|fixed', value: 'int', currency: 'string?', duration: 'once|repeating|forever', duration_months: 'int?', max_redemptions: 'int?', redemptions: 'int', product_ids: '[ulid]', expires_at: 'datetime?', is_active: 'bool' }),
  PaymentLink: obj({ url: 'uri', qr_svg: 'string', whatsapp_url: 'uri' }),
  AnalyticsOverview: obj({ mrr: '$Money', arr: '$Money', revenue_month: '$Money', revenue_prev_month: '$Money', active_clients: 'int', client_churn_rate: 'num', ltv: '$Money', arpu: '$Money', refunds: '$Money', outstanding_manual: '$Money', credit_liability: 'int', mrr_movement: obj({ new: '$Money', expansion: '$Money', churned: '$Money' }), revenue_by_product: { type: 'array', items: obj({ product: 'string', revenue: '$Money' }) }, daily: '[json]' }),
  RetentionCohorts: obj({ cohorts: { type: 'array', items: obj({ month: 'string', size: 'int', retention: '[num]' }) } }),
  CoachPerformance: obj({ coaches: { type: 'array', items: obj({ coach: '$ExerciseRef', revenue: '$Money', active_clients: 'int', retention_pct: 'num', sessions_delivered: 'int', commission: '$Money' }) } }),
  CommissionRule: obj({ coach_id: 'ulid?', type: 'percent_package|per_session', value: 'int' }),

  // ------------------------------------------------------------ CRM
  LeadStage: obj({ id: 'ulid', name: 'string', position: 'int', kind: 'open|won|lost' }),
  Lead: obj({ id: 'ulid', stage: '$LeadStage', owner: '$ExerciseRef?', name: 'string', email: 'email?', phone: 'string?', source: 'profile|form|referral|instagram|whatsapp|manual|marketplace', message: 'string?', tags: '[string]', value: '$Money?', consent: 'bool', converted_at: 'datetime?', created_at: 'datetime', 'activities?': '[$LeadActivity]' }),
  LeadWrite: obj({ 'lead_stage_id?': 'ulid', 'owner_id?': 'ulid', name: 'string', 'email?': 'email', 'phone?': 'string', 'source?': 'string', 'message?': 'string', 'tags?': '[string]', 'value_minor?': 'int', 'consent?': 'bool' }),
  LeadActivity: obj({ id: 'ulid', type: 'note|call|email|task|stage_change', body: 'string?', due_at: 'datetime?', completed_at: 'datetime?', user: '$ExerciseRef?', created_at: 'datetime' }),
  ContractTemplate: obj({ id: 'ulid', name: 'string', kind: 'contract|waiver|parq', body: 'string' }),
  Contract: obj({ id: 'ulid', title: 'string', client: '$ExerciseRef', status: 'pending|signed|void', rendered_body: 'string', signature_type: 'draw|type?', signed_at: 'datetime?', document_hash: 'string?', 'pdf_url?': 'uri' }),
  SignContract: obj({ signature_type: 'draw|type', signature_data: 'string', 'answers?': 'json', agree: { type: 'boolean', const: true } }),

  // ------------------------------------------------------------ AI
  AiOutput: obj({ id: 'ulid', feature: 'program|meal_plan|checkin_summary|reply_draft|progression|photo_food|form_check|ask|content|thread_summary|starter_plan|coach_chat|churn', status: 'draft|approved|edited|rejected|queued|failed', client: '$ExerciseRef?', payload: 'json', grounding: 'json', flags: '[string]', created_at: 'datetime', reviewed_at: 'datetime?' }),
  AiQueued: obj({ ai_output_id: 'ulid', status: 'queued|draft', channel: 'string', 'output?': '$AiOutput' }),
  AiSettings: obj({ tone: 'motivational|technical|nurturing|tough_love', sign_off: 'string?', use_emoji: 'bool', philosophy: 'string?', banned_words: '[string]', sample_replies: '[string]', disabled_features: '[string]', opt_out: 'bool' }),
  AiUsage: obj({ period: 'string', requests: 'int', limit: 'int?', cost_micros: 'int', by_feature: { type: 'array', items: obj({ feature: 'string', requests: 'int', tokens_in: 'int', tokens_out: 'int', cost_micros: 'int', acceptance_rate: 'num?' }) } }),
  ChurnItem: obj({ client: '$ClientSummary', score: 'num', reasons: '[string]', suggested_message: 'string?' }),

  // ------------------------------------------------------------ integrations
  Integration: obj({ provider: 'string', status: 'connected|error|disconnected|available', last_sync_at: 'datetime?', last_error: 'string?', scopes: '[string]' }),
  ConnectResult: obj({ authorize_url: 'uri?', status: 'connected|pending' }),
  HealthSampleBatch: obj({ samples: { type: 'array', maxItems: 500, items: obj({ type: 'steps|active_energy|workout|heart_rate|resting_hr|hrv|sleep|weight|body_fat|nutrition|string', value: 'num', unit: 'string', start_at: 'datetime', end_at: 'datetime', source: 'apple_health|health_connect|string', external_id: 'string', 'meta?': 'json' }) } }),
  HealthSummary: obj({ days: { type: 'array', items: obj({ date: 'date', values: { type: 'object', additionalProperties: { type: 'number' } }, sources: { type: 'object', additionalProperties: { type: 'string' } } }) }, readiness: 'num?' }),
  WebhookEndpoint: obj({ id: 'ulid', url: 'uri', events: '[string]', is_active: 'bool', 'secret?': 'string' }),
  WebhookDelivery: obj({ id: 'ulid', event: 'string', response_code: 'int?', attempts: 'int', status: 'pending|delivered|failed', next_retry_at: 'datetime?', created_at: 'datetime' }),
  ApiToken: obj({ id: 'string', name: 'string', abilities: '[string]', last_used_at: 'datetime?', expires_at: 'datetime?', 'plain_text_token?': 'string' }),

  // ------------------------------------------------------------ sync
  SyncPull: obj({ server_time: 'datetime', changes: obj({ sessions: '[json]', session_sets: '[json]', food_logs: '[json]', water_logs: '[json]', habit_logs: '[json]', body_metrics: '[json]', bookings: '[json]' }, { required: false }), deleted: { type: 'object', additionalProperties: { type: 'array', items: { $ref: '#/components/schemas/Ulid' } } } }),
  SyncPush: obj({ mutations: { type: 'array', maxItems: 500, items: obj({ id: 'ulid', entity: 'session|session_set|food_log|water_log|habit_log|body_metric|message', op: 'upsert|delete', payload: 'json', client_time: 'datetime' }) } }),
  SyncPushResult: obj({ results: { type: 'array', items: obj({ id: 'ulid', status: 'applied|duplicate|rejected', 'error?': 'string' }) }, server_time: 'datetime' }),

  // ------------------------------------------------------------ coach
  ClientSummary: obj({ id: 'ulid', name: 'string', email: 'email', avatar_url: 'uri?', status: 'lead|active|paused|archived|invited', goal: 'string?', level: 'string?', tags: '[string]', coach: '$ExerciseRef?', compliance_score: 'int?', risk_status: 'green|amber|red', last_active_at: 'datetime?', program: '$ExerciseRef?', unread_messages: 'int', joined_at: 'datetime?' }),
  ClientDetail: { allOf: [{ $ref: '#/components/schemas/ClientSummary' }, obj({ phone: 'string?', profile: obj({ date_of_birth: 'date?', gender: 'string?', height_cm: 'num?', activity_level: 'string?', injuries: 'string?', medical_notes: 'string?', equipment: '[string]', days_per_week: 'int?', diet_tags: '[string]', parq_flagged: 'bool' }, { required: false }), groups: '[$ExerciseRef]' }, { required: false })] },
  ClientCreate: obj({ name: 'string', 'email?': 'email', 'phone?': 'string', 'coach_id?': 'ulid', 'tags?': '[string]', 'program_id?': 'ulid', 'send_invite?': 'bool', 'channel?': 'email|link|qr|whatsapp' }),
  ClientUpdate: obj({ 'status?': 'active|paused|archived', 'coach_id?': 'ulid?', 'goal?': 'string', 'level?': 'string', 'tags?': '[string]', 'injuries?': 'string', 'medical_notes?': 'string', 'equipment?': '[string]', 'days_per_week?': 'int', 'diet_tags?': '[string]' }),
  ClientSchedule: obj({ from: 'date', to: 'date', days: { type: 'array', items: obj({ date: 'date', workouts: '[json]', meals: '[json]', habits: '[json]', checkins: '[json]', bookings: '[json]' }) } }),
  Compliance: obj({ score: 'int', risk_status: 'green|amber|red', workouts_pct: 'num', nutrition_pct: 'num', habits_pct: 'num', checkins_pct: 'num', trend: '[json]' }),
  Invitation: obj({ id: 'ulid', code: 'string', email: 'email?', phone: 'string?', name: 'string?', role: 'string', expires_at: 'datetime?', accepted_at: 'datetime?', link: 'uri' }),
  Group: obj({ id: 'ulid', name: 'string', color: 'string?', member_ids: '[ulid]', members_count: 'int' }),
  Dashboard: obj({
    kpis: obj({ active_clients: 'int', at_risk: 'int', checkins_to_review: 'int', unread_messages: 'int', sessions_today: 'int', mrr: '$Money', revenue_month: '$Money' }),
    at_risk_clients: '[$ClientSummary]', todays_sessions: '[$Event]', recent_activity: { type: 'array', items: obj({ type: 'string', client: '$ExerciseRef', text: 'string', at: 'datetime' }) },
    onboarding: obj({ steps: { type: 'array', items: obj({ key: 'string', done: 'bool' }) } }),
  }),
  SearchResults: obj({ clients: '[$ClientSummary]', programs: '[$ExerciseRef]', workouts: '[$ExerciseRef]', exercises: '[$ExerciseRef]', foods: '[$ExerciseRef]' }),
  TeamMember: obj({ id: 'ulid', name: 'string', email: 'email', role: 'owner|coach|assistant', status: 'active|invited|archived', permissions: '[string]', clients_count: 'int' }),
  Billing: obj({ plan: 'free|pro|studio|enterprise', interval: 'month|year?', status: 'trialing|active|past_due|cancelled|expired|none', trial_ends_at: 'datetime?', current_period_end: 'datetime?', cancel_at_period_end: 'bool', card: { oneOf: [obj({ brand: 'string', last4: 'string' }), { type: 'null' }] }, usage: obj({ clients: 'int', coaches: 'int', ai_requests_month: 'int' }), limits: 'json', plans: '[json]', invoices: '[json]' }),
};
