#!/usr/bin/env node
/**
 * Aurum ERD generator.
 *
 * Parses every migration in backend/database/migrations (the `up()` method
 * only), applies Schema::create and Schema::table blueprints in filename
 * order, joins the result with model metadata (tenant traits + encrypted
 * casts) read from backend/app/Domain/<Module>/Models, and writes:
 *
 *   docs/erd.mmd                Mermaid erDiagram (every table, PK/FK/UK)
 *   docs/erd.json               Machine-readable schema snapshot
 *   docs/ERD.md                 Replaces the block between
 *                               <!-- erd:tables:start --> and <!-- erd:tables:end -->
 *
 * Usage: node infra/scripts/generate-erd.mjs   (from the repo root, or anywhere)
 * Requires Node 18+. No dependencies.
 */
import { readFileSync, writeFileSync, readdirSync, existsSync, statSync } from 'node:fs';
import { join, dirname, basename } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const migDir = join(root, 'backend', 'database', 'migrations');
const domainDir = join(root, 'backend', 'app', 'Domain');

// spatie/permission dynamic names (config/permission.php: teams=true, team_foreign_key=tenant_id)
const DYNAMIC = {
  "$tableNames['permissions']": 'permissions',
  "$tableNames['roles']": 'roles',
  "$tableNames['model_has_permissions']": 'model_has_permissions',
  "$tableNames['model_has_roles']": 'model_has_roles',
  "$tableNames['role_has_permissions']": 'role_has_permissions',
  "$columnNames['team_foreign_key']": 'tenant_id',
  "$columnNames['model_morph_key']": 'model_id',
  '$pivotPermission': 'permission_id',
  '$pivotRole': 'role_id',
};

// Columns holding personal data (P) or special-category health data (H).
// Encrypted (E) flags come from model casts and are merged in automatically.
const PII = {
  users: { name: 'P', email: 'P', phone: 'P', avatar_path: 'P', apple_id: 'P', google_id: 'P', password: 'P (bcrypt hash)', two_factor_secret: 'P', two_factor_recovery_codes: 'P', remember_token: 'P' },
  password_reset_tokens: { email: 'P' },
  sessions: { ip_address: 'P', user_agent: 'P', payload: 'P' },
  invitations: { email: 'P', phone: 'P', name: 'P' },
  devices: { push_token: 'P', name: 'P' },
  magic_links: { email: 'P' },
  activity_logs: { ip: 'P', user_agent: 'P' },
  coach_profiles: { headline: 'P (public)', bio: 'P (public)', instagram: 'P (public)', intro_video_url: 'P (public)', gender: 'P (public)' },
  client_profiles: { date_of_birth: 'P', gender: 'H', height_cm: 'H', injuries: 'H', medical_notes: 'H', parq_flagged: 'H', onboarding: 'H' },
  workout_sessions: { notes: 'H', mood: 'H', perceived_effort: 'H' },
  food_logs: { name: 'H', kcal: 'H', protein_g: 'H', carbs_g: 'H', fat_g: 'H' },
  water_logs: { ml: 'H' },
  habit_logs: { value: 'H' },
  nutrition_targets: { kcal: 'H', calculation: 'H' },
  personal_records: { value: 'H' },
  session_sets: { weight_kg: 'H', reps: 'H', rpe: 'H' },
  locations: { address: 'P (business)' },
  checkin_responses: { answers: 'H', context: 'H', coach_feedback: 'H' },
  checkin_comments: { body: 'P' },
  body_metrics: { weight_kg: 'H', body_fat_pct: 'H', measurements: 'H' },
  progress_photos: { path: 'H (private disk, signed URLs)' },
  messages: { body: 'P' },
  message_attachments: { path: 'P' },
  posts: { body: 'P' },
  comments: { body: 'P' },
  leads: { name: 'P', email: 'P', phone: 'P', message: 'P' },
  lead_activities: { body: 'P' },
  contracts: { rendered_body: 'P', signature_data: 'P', signed_ip: 'P', signed_user_agent: 'P' },
  orders: { customer_email: 'P', customer_name: 'P', billing_details: 'P' },
  payments: { receipt_path: 'P' },
  invoices: { buyer: 'P' },
  payment_gateways: { credentials: 'secret' },
  integrations: { access_token: 'secret', refresh_token: 'secret' },
  webhook_endpoints: { secret: 'secret' },
  health_samples: { value: 'H', meta: 'H' },
  health_daily: { value: 'H' },
  ai_outputs: { payload: 'H', input: 'H' },
  reviews: { body: 'P' },
  marketing_leads: { email: 'P', name: 'P', phone: 'P' },
  personal_access_tokens: { token: 'secret (sha256)' },
};

function singularToTable(col) {
  const base = col.replace(/_id$/, '');
  if (base.endsWith('y') && !/[aeiou]y$/.test(base)) return base.slice(0, -1) + 'ies';
  if (/(s|x|ch|sh)$/.test(base)) return base + 'es';
  return base + 's';
}

function upBody(src) {
  const start = src.indexOf('function up()');
  if (start < 0) return '';
  const end = src.indexOf('function down()', start);
  return src.slice(start, end < 0 ? undefined : end);
}

/** Return [{op, table, body}] for each Schema::create/table call in order. */
function blueprints(src) {
  const out = [];
  const re = /Schema::(create|table)\(\s*('([a-z_]+)'|\$tableNames\['[a-z_]+'\])\s*,[^{]*\{/g;
  let m;
  while ((m = re.exec(src))) {
    const table = m[3] ?? DYNAMIC[m[2]];
    let depth = 1;
    let i = re.lastIndex;
    while (i < src.length && depth > 0) {
      if (src[i] === '{') depth++;
      else if (src[i] === '}') depth--;
      i++;
    }
    out.push({ op: m[1], table, body: src.slice(re.lastIndex, i - 1) });
    re.lastIndex = i;
  }
  return out;
}

function resolveArg(raw) {
  raw = raw.trim();
  if (DYNAMIC[raw]) return DYNAMIC[raw];
  const s = raw.match(/^'([^']*)'$/) || raw.match(/^"([^"]*)"$/);
  if (s) return s[1];
  const arr = raw.match(/^\[(.*)\]$/s);
  if (arr) return arr[1].split(',').map((x) => resolveArg(x)).filter(Boolean);
  return raw;
}

function splitArgs(s) {
  const parts = [];
  let depth = 0, cur = '', q = false;
  for (const ch of s) {
    if (ch === "'" ) q = !q;
    if (!q && (ch === '(' || ch === '[')) depth++;
    if (!q && (ch === ')' || ch === ']')) depth--;
    if (!q && depth === 0 && ch === ',') { parts.push(cur); cur = ''; continue; }
    cur += ch;
  }
  if (cur.trim()) parts.push(cur);
  return parts;
}

/** Turn "$table->a(x)->b(y)" into [{name:'a', args:[...]}, ...]. */
function chain(stmt) {
  const calls = [];
  const re = /->([a-zA-Z]+)\(/g;
  let m;
  while ((m = re.exec(stmt))) {
    let depth = 1, i = re.lastIndex, q = false;
    while (i < stmt.length && depth > 0) {
      const ch = stmt[i];
      if (ch === "'") q = !q;
      if (!q && ch === '(') depth++;
      if (!q && ch === ')') depth--;
      i++;
    }
    calls.push({ name: m[1], args: splitArgs(stmt.slice(re.lastIndex, i - 1)).map(resolveArg) });
    re.lastIndex = i;
  }
  return calls;
}

function statements(body) {
  const out = [];
  let buf = '';
  for (const line of body.split('\n')) {
    const t = line.trim();
    if (!t || t.startsWith('//') || t.startsWith('*') || t.startsWith('/*')) continue;
    if (/^(if|}|else)/.test(t) && !t.includes('$table->')) continue;
    buf += ' ' + t;
    const semi = buf.lastIndexOf(';');
    if (semi >= 0 && /;\s*(\/\/.*)?$/.test(buf)) {
      const comment = (buf.slice(semi + 1).match(/\/\/\s*(.*)$/) || [])[1] || '';
      out.push({ code: buf.slice(0, semi).trim(), comment: comment.trim() });
      buf = '';
    }
  }
  return out;
}

const TYPE = {
  ulid: 'ulid', foreignUlid: 'ulid', string: 'string', text: 'text', mediumText: 'text', longText: 'text',
  json: 'json', boolean: 'boolean', timestamp: 'timestamp', date: 'date', time: 'time', decimal: 'decimal',
  integer: 'int', bigInteger: 'bigint', unsignedBigInteger: 'bigint', unsignedInteger: 'int',
  unsignedSmallInteger: 'smallint', unsignedTinyInteger: 'tinyint', uuid: 'uuid',
};

const tables = new Map();
const order = [];

function ensure(name) {
  if (!tables.has(name)) {
    tables.set(name, { name, columns: [], indexes: [], uniques: [], primary: [], softDeletes: false, source: '' });
    order.push(name);
  }
  return tables.get(name);
}

function addCol(t, col) {
  const existing = t.columns.find((c) => c.name === col.name);
  if (existing) Object.assign(existing, col);
  else t.columns.push(col);
  return col;
}

for (const file of readdirSync(migDir).filter((f) => f.endsWith('.php')).sort()) {
  const src = upBody(readFileSync(join(migDir, file), 'utf8'));
  for (const bp of blueprints(src)) {
    const t = ensure(bp.table);
    if (bp.op === 'create') t.source = file;
    for (const { code, comment } of statements(bp.body)) {
      if (!code.startsWith('$table->')) continue;
      const calls = chain(code);
      const [head, ...mods] = calls;
      const modNames = mods.map((x) => x.name);
      const nullable = modNames.includes('nullable');
      const arg0 = head.args[0];
      switch (head.name) {
        case 'id':
          addCol(t, { name: 'id', type: 'bigint', pk: true, nullable: false, comment });
          break;
        case 'timestamps':
          addCol(t, { name: 'created_at', type: 'timestamp', nullable: true });
          addCol(t, { name: 'updated_at', type: 'timestamp', nullable: true });
          break;
        case 'softDeletes':
          addCol(t, { name: 'deleted_at', type: 'timestamp', nullable: true });
          t.softDeletes = true;
          break;
        case 'rememberToken':
          addCol(t, { name: 'remember_token', type: 'string', nullable: true });
          break;
        case 'ulidMorphs':
        case 'nullableUlidMorphs':
          addCol(t, { name: `${arg0}_type`, type: 'string', nullable: head.name.startsWith('nullable'), comment: 'polymorphic' });
          addCol(t, { name: `${arg0}_id`, type: 'ulid', nullable: head.name.startsWith('nullable'), comment: 'polymorphic' });
          t.indexes.push([`${arg0}_type`, `${arg0}_id`]);
          break;
        case 'primary':
          t.primary = Array.isArray(arg0) ? arg0 : [arg0];
          break;
        case 'unique':
          t.uniques.push(Array.isArray(arg0) ? arg0 : [arg0]);
          break;
        case 'index':
          t.indexes.push(Array.isArray(arg0) ? arg0 : [arg0]);
          break;
        case 'foreign': {
          const col = t.columns.find((c) => c.name === arg0);
          const on = mods.find((x) => x.name === 'on');
          if (col && on) col.references = on.args[0];
          break;
        }
        case 'dropColumn': {
          const cols = Array.isArray(arg0) ? arg0 : [arg0];
          t.columns = t.columns.filter((c) => !cols.includes(c.name));
          break;
        }
        case 'dropIndex':
          break;
        default: {
          if (!TYPE[head.name]) break;
          const col = { name: arg0, type: TYPE[head.name], nullable, comment };
          if (head.name === 'decimal' && head.args[1]) col.type = `decimal`;
          if (modNames.includes('primary')) col.pk = true;
          if (modNames.includes('unique')) col.unique = true;
          if (modNames.includes('index')) t.indexes.push([arg0]);
          const def = mods.find((x) => x.name === 'default');
          if (def) col.default = String(def.args[0]);
          if (head.name === 'foreignUlid') {
            const c = mods.find((x) => x.name === 'constrained');
            if (c) col.references = c.args[0] && typeof c.args[0] === 'string' ? c.args[0] : singularToTable(arg0);
            if (modNames.includes('cascadeOnDelete')) col.onDelete = 'cascade';
            if (modNames.includes('nullOnDelete')) col.onDelete = 'set null';
            if (!c) col.logical = true;
          }
          addCol(t, col);
        }
      }
    }
    for (const p of t.primary) {
      const c = t.columns.find((x) => x.name === p);
      if (c) c.pk = true;
    }
  }
}

// ---------------------------------------------------------- model metadata
const models = {};
function walk(dir) {
  for (const f of readdirSync(dir)) {
    const p = join(dir, f);
    if (statSync(p).isDirectory()) walk(p);
    else if (p.includes(`${'/'}Models${'/'}`) && f.endsWith('.php')) {
      const src = readFileSync(p, 'utf8');
      const tm = src.match(/protected \$table = '([a-z_]+)'/);
      const cls = basename(f, '.php');
      const table = tm ? tm[1] : cls === 'User' ? 'users' : null;
      if (!table) continue;
      const encrypted = [...src.matchAll(/'([a-z_]+)' => 'encrypted(?::[a-z]+)?'/g)].map((x) => x[1]);
      models[table] = {
        model: `${p.split('/Domain/')[1].split('/')[0]}\\${cls}`,
        belongsToTenant: /\buse BelongsToTenant\b|BelongsToTenant;/.test(src) && /BelongsToTenant/.test(src.split('{').slice(1).join('{')),
        shared: /SharedWithTenant/.test(src.split('{').slice(1).join('{')),
        translatable: /HasTranslations/.test(src.split('{').slice(1).join('{')),
        encrypted,
      };
    }
  }
}
if (existsSync(domainDir)) walk(domainDir);

// Parent used to reach tenant scope for child tables without tenant_id
function scopeOf(t) {
  const m = models[t.name];
  const hasTenant = t.columns.some((c) => c.name === 'tenant_id');
  if (m?.shared) return 'shared library (tenant_id NULL = global)';
  if (m?.belongsToTenant) return 'tenant (global scope)';
  if (hasTenant) return 'tenant column (explicit where)';
  if (t.columns.some((c) => c.name === 'notifiable_id')) return 'user-owned (notifiable morph)';
  const parent = t.columns.find((c) => c.references && c.references !== 'users' && c.references !== t.name);
  if (parent) return `via ${parent.references}`;
  if (t.columns.some((c) => c.name === 'user_id')) return 'user-owned';
  return 'global / system';
}

function flags(t, c) {
  const out = [];
  const enc = models[t.name]?.encrypted ?? [];
  if (enc.includes(c.name)) out.push('ENCRYPTED');
  const p = PII[t.name]?.[c.name];
  if (p) out.push(p.startsWith('H') ? `HEALTH${p.slice(1)}` : p.startsWith('P') ? `PII${p.slice(1)}` : `SECRET${p.replace(/^secret/, '')}`);
  return out;
}

// ---------------------------------------------------------- outputs
const names = order.filter((n) => tables.get(n).columns.length);
const json = names.map((n) => {
  const t = tables.get(n);
  return { ...t, scope: scopeOf(t), model: models[n]?.model ?? null, columns: t.columns.map((c) => ({ ...c, flags: flags(t, c) })) };
});
writeFileSync(join(root, 'docs', 'erd.json'), JSON.stringify(json, null, 2) + '\n');

const esc = (s) => String(s).replace(/"/g, "'").replace(/[{}<>|]/g, ' ');
let mmd = '%% Generated by infra/scripts/generate-erd.mjs from backend/database/migrations — do not edit by hand.\n';
mmd += `%% ${names.length} tables. Comments: T=tenant scope, ENC=encrypted cast, PII/HEALTH/SECRET = data classification.\n`;
mmd += 'erDiagram\n';
for (const t of json) {
  mmd += `    ${t.name} {\n`;
  for (const c of t.columns) {
    const keys = [];
    if (c.pk) keys.push('PK');
    if (c.references) keys.push('FK');
    if (c.unique || t.uniques.some((u) => u.length === 1 && u[0] === c.name)) keys.push('UK');
    const notes = [];
    if (c.name === 'tenant_id') notes.push(`T: ${t.scope}`);
    if (c.flags.length) notes.push(c.flags.join(' ').replace(/ \(.*\)/, ''));
    if (c.nullable) notes.push('nullable');
    mmd += `        ${c.type} ${c.name}${keys.length ? ' ' + keys.join(',') : ''}${notes.length ? ` "${esc(notes.join('; '))}"` : ''}\n`;
  }
  mmd += '    }\n';
}
for (const t of json) {
  for (const c of t.columns) {
    if (!c.references || !tables.has(c.references)) continue;
    const card = c.nullable ? '|o--o{' : '||--o{';
    mmd += `    ${c.references} ${card} ${t.name} : "${c.name}"\n`;
  }
  for (const c of t.columns) {
    if (c.references || c.name !== 'tenant_id' || t.name === 'tenants') continue;
    mmd += `    tenants |o..o{ ${t.name} : "tenant_id (logical)"\n`;
  }
}
writeFileSync(join(root, 'docs', 'erd.mmd'), mmd);

// Per-table markdown appendix
let md = '';
for (const t of json) {
  md += `\n### \`${t.name}\`\n\n`;
  md += `- **Scope:** ${t.scope}${t.model ? ` · **Model:** \`App\\Domain\\${t.model}\`` : ''}${t.softDeletes ? ' · **Soft deletes**' : ''}\n`;
  md += `- **Migration:** \`${t.source || 'framework'}\`\n`;
  const idx = [...t.uniques.map((u) => `UNIQUE(${u.join(', ')})`), ...t.indexes.map((i) => `INDEX(${i.join(', ')})`)];
  if (t.primary.length > 1) idx.unshift(`PRIMARY(${t.primary.join(', ')})`);
  if (idx.length) md += `- **Indexes:** ${idx.join(' · ')}\n`;
  md += '\n| Column | Type | Null | Key / FK | Default | Flags | Notes |\n|---|---|---|---|---|---|---|\n';
  for (const c of t.columns) {
    const key = [c.pk ? 'PK' : '', c.unique ? 'UK' : '', c.references ? `FK → ${c.references}${c.onDelete ? ` (${c.onDelete})` : ''}` : ''].filter(Boolean).join(' ');
    md += `| ${c.name} | ${c.type} | ${c.nullable ? 'yes' : ''} | ${key} | ${c.default ?? ''} | ${c.flags.join(', ')} | ${esc(c.comment ?? '').replace(/\|/g, '/')} |\n`;
  }
}

const erdMd = join(root, 'docs', 'ERD.md');
if (existsSync(erdMd)) {
  const cur = readFileSync(erdMd, 'utf8');
  const a = '<!-- erd:tables:start -->';
  const b = '<!-- erd:tables:end -->';
  if (cur.includes(a) && cur.includes(b)) {
    const next = cur.slice(0, cur.indexOf(a) + a.length) + '\n' + md + '\n' + cur.slice(cur.indexOf(b));
    writeFileSync(erdMd, next);
  }
}

console.log(`ERD: ${names.length} tables, ${json.reduce((n, t) => n + t.columns.length, 0)} columns → docs/erd.mmd, docs/erd.json`);
