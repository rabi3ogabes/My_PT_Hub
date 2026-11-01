#!/usr/bin/env node
/**
 * Aurum store metadata validator.
 *
 *   node store/validate.mjs            # validate store/{client,coach}/<locale> and store/whitelabel/<slug>/<locale>
 *   node store/validate.mjs --verbose  # also print character counts
 *
 * Rules (App Store Connect + Google Play Console):
 *   title.txt              ≤ 30   (App Store name / Play title)
 *   subtitle.txt           ≤ 30   (App Store subtitle)
 *   short_description.txt  ≤ 80   (Play short description)
 *   keywords.txt           ≤ 100  (App Store keyword field) — comma-separated, no spaces,
 *                                 no duplicates, no words already in the title, no competitor trademarks
 *   full_description.txt   ≤ 4000 (Play full description / App Store description)
 *   whats_new_template.txt ≤ 500  (Play "What's new" limit; App Store allows 4000)
 * Plus: every listing folder has all files incl. screenshots.md and promo_video_storyboard.md,
 * no unfilled template tokens, and no competitor trademarks in any customer-facing field.
 * Lengths count Unicode code points, as both consoles do.
 */
import { readFileSync, readdirSync, existsSync, statSync } from 'node:fs';
import { join, dirname, relative } from 'node:path';
import { fileURLToPath } from 'node:url';

const here = dirname(fileURLToPath(import.meta.url));
const verbose = process.argv.includes('--verbose');

const LIMITS = {
  'title.txt': 30,
  'subtitle.txt': 30,
  'short_description.txt': 80,
  'keywords.txt': 100,
  'full_description.txt': 4000,
  'whats_new_template.txt': 500,
};
const REQUIRED_MAIN = [...Object.keys(LIMITS), 'screenshots.md', 'promo_video_storyboard.md'];
const REQUIRED_WHITELABEL = ['title.txt', 'subtitle.txt', 'short_description.txt', 'keywords.txt', 'full_description.txt'];
const LOCALES = ['en', 'ar', 'fr', 'es'];

// Competitor and third-party trademarks that must not appear in metadata (Apple 2.3.7 / Play policy).
const TRADEMARKS = [
  'my pt hub', 'mypthub', 'trainerize', 'everfit', 'pt distinction', 'truecoach', 'true coach', 'myfitnesspal',
  'fitbod', 'strava', 'garmin', 'fitbit', 'whoop', 'oura', 'peloton', 'nike', 'apple watch', 'instagram',
  'tiktok', 'chatgpt', 'openai', 'claude', 'hevy', 'strong app', 'caliber', 'future fit', 'kahunas', 'exercise.com',
];
// Case-sensitive on purpose: Spanish/French "todo" is a normal word.
const TEMPLATE_TOKEN = /\{\{[^}]*\}\}|\[BRAND\]|\bTODO\b|\bTBD\b|[Ll]orem ipsum/;

const len = (s) => [...s].length;
const words = (s) => s.toLowerCase().normalize('NFKC').split(/[\s:：,،&+\-—–|/()!?.·«»"']+/u).filter((w) => w.length > 1);

let errors = 0;
let warnings = 0;
const err = (file, msg) => { errors++; console.log(`  ✗ ${relative(here, file)}: ${msg}`); };
const warn = (file, msg) => { warnings++; console.log(`  ! ${relative(here, file)}: ${msg}`); };

function read(file) {
  // Trailing newline is not part of the field.
  return readFileSync(file, 'utf8').replace(/\r\n/g, '\n').replace(/\n+$/, '');
}

function checkListing(dir, required) {
  const label = relative(here, dir);
  const counts = [];
  for (const name of required) {
    if (!existsSync(join(dir, name))) err(join(dir, name), 'missing');
  }
  const get = (name) => (existsSync(join(dir, name)) ? read(join(dir, name)) : null);

  for (const [name, max] of Object.entries(LIMITS)) {
    const value = get(name);
    if (value === null) continue;
    const n = len(value);
    counts.push(`${name.replace('.txt', '')}=${n}/${max}`);
    if (n === 0) err(join(dir, name), 'empty');
    if (n > max) err(join(dir, name), `${n} chars > ${max}`);
    if (['title.txt', 'subtitle.txt', 'short_description.txt', 'keywords.txt'].includes(name) && value.includes('\n')) {
      err(join(dir, name), 'must be a single line');
    }
    if (TEMPLATE_TOKEN.test(value) && name !== 'whats_new_template.txt') err(join(dir, name), 'contains an unfilled template token');
    const lower = value.toLowerCase();
    for (const tm of TRADEMARKS) {
      if (lower.includes(tm)) err(join(dir, name), `contains third-party trademark "${tm}"`);
    }
  }

  const title = get('title.txt');
  const keywords = get('keywords.txt');
  if (keywords !== null) {
    const file = join(dir, 'keywords.txt');
    if (/\s/.test(keywords)) err(file, 'keywords must not contain spaces (use commas only)');
    if (/,,|^,|,$/.test(keywords)) err(file, 'empty keyword (double/leading/trailing comma)');
    const list = keywords.split(',').map((k) => k.trim().toLowerCase());
    const dupes = list.filter((k, i) => list.indexOf(k) !== i);
    if (dupes.length) err(file, `duplicate keywords: ${[...new Set(dupes)].join(', ')}`);
    if (title !== null) {
      const titleWords = new Set(words(title));
      const repeated = list.filter((k) => titleWords.has(k));
      if (repeated.length) err(file, `repeats title words (already indexed): ${repeated.join(', ')}`);
    }
    const subtitle = get('subtitle.txt');
    if (subtitle !== null) {
      const subWords = new Set(words(subtitle));
      const overlap = list.filter((k) => subWords.has(k));
      if (overlap.length) warn(file, `repeats subtitle words (wasted characters): ${overlap.join(', ')}`);
    }
    if (len(keywords) < 85) warn(file, `only ${len(keywords)}/100 characters used`);
  }

  const full = get('full_description.txt');
  if (full !== null) {
    const [l1 = '', l2 = ''] = full.split('\n');
    if (len(l1) > 170 || len(l2) > 170) warn(join(dir, 'full_description.txt'), 'hook lines are long; first two lines should read as a headline');
  }

  const shots = get('screenshots.md');
  if (shots !== null) {
    const frames = (shots.match(/^##+\s*Frame\s+\d/gim) || []).length;
    if (frames !== 8) err(join(dir, 'screenshots.md'), `expected 8 frames, found ${frames}`);
  }
  console.log(`  ✓ ${label}${verbose && counts.length ? `  (${counts.join(', ')})` : ''}`);
}

console.log('Aurum store metadata\n');
for (const app of ['client', 'coach']) {
  for (const locale of LOCALES) {
    const dir = join(here, app, locale);
    if (!existsSync(dir)) { err(dir, 'listing folder missing'); continue; }
    checkListing(dir, REQUIRED_MAIN);
  }
}

const wlRoot = join(here, 'whitelabel');
if (existsSync(wlRoot)) {
  for (const slug of readdirSync(wlRoot)) {
    const tenantDir = join(wlRoot, slug);
    if (!statSync(tenantDir).isDirectory()) continue;
    for (const locale of readdirSync(tenantDir)) {
      const dir = join(tenantDir, locale);
      if (statSync(dir).isDirectory()) checkListing(dir, REQUIRED_WHITELABEL);
    }
  }
}

for (const doc of ['privacy/data_safety.md', 'privacy/app_privacy_labels.md', 'categories_and_ratings.md', 'custom_product_pages.md']) {
  if (!existsSync(join(here, doc))) err(join(here, doc), 'missing');
}

console.log(`\n${errors ? '✗' : '✓'} ${errors} error(s), ${warnings} warning(s)`);
process.exit(errors ? 1 : 0);
