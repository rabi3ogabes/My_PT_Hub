#!/usr/bin/env node
// Generates platform theme files from tokens.json.
//   node packages/design_tokens/generate.mjs
// Outputs:
//   backend/resources/css/tokens.css          (Tailwind v4 @theme + light/dark CSS variables)
//   packages/core/lib/src/design/app_tokens.dart (Flutter constants)
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const here = dirname(fileURLToPath(import.meta.url));
const root = resolve(here, '../..');
const t = JSON.parse(readFileSync(resolve(here, 'tokens.json'), 'utf8'));

const kebab = (s) => s.replace(/([a-z0-9])([A-Z])/g, '$1-$2').toLowerCase();
const header = 'GENERATED FILE — do not edit. Source: packages/design_tokens/tokens.json';

// ---------- CSS (Tailwind v4) ----------
function css() {
  const lines = [`/* ${header} */`, '@theme {'];
  for (const [k, v] of Object.entries(t.color.brand)) lines.push(`  --color-${kebab(k)}: ${v};`);
  for (const [k, v] of Object.entries(t.color.semantic)) lines.push(`  --color-${kebab(k)}: ${v};`);
  for (const [k, v] of Object.entries(t.color.macro)) lines.push(`  --color-macro-${kebab(k)}: ${v};`);
  // Themeable surface colors resolve to runtime variables so `dark` flips them.
  for (const k of Object.keys(t.color.dark)) lines.push(`  --color-${kebab(k)}: var(--au-${kebab(k)});`);
  lines.push(`  --font-sans: "${t.font.latin}", "${t.font.arabic}", ui-sans-serif, system-ui, sans-serif;`);
  lines.push(`  --font-arabic: "${t.font.arabic}", "${t.font.latin}", ui-sans-serif, system-ui, sans-serif;`);
  lines.push(`  --font-mono: "${t.font.mono}", ui-monospace, monospace;`);
  for (const [k, v] of Object.entries(t.radius)) lines.push(`  --radius-${k}: ${v === 999 ? '9999px' : v + 'px'};`);
  for (const [k, v] of Object.entries(t.elevation)) lines.push(`  --shadow-e${k}: ${v};`);
  for (const [k, v] of Object.entries(t.typography)) {
    lines.push(`  --text-${kebab(k)}: ${v.size / 16}rem;`);
    lines.push(`  --text-${kebab(k)}--line-height: ${v.lineHeight};`);
    lines.push(`  --text-${kebab(k)}--letter-spacing: ${v.tracking}em;`);
    lines.push(`  --text-${kebab(k)}--font-weight: ${v.weight};`);
  }
  lines.push(`  --ease-out-expo: cubic-bezier(${t.motion.easeOut.join(', ')});`);
  lines.push(`  --ease-in-out-expo: cubic-bezier(${t.motion.easeInOut.join(', ')});`);
  lines.push(`  --duration-fast: ${t.motion.fast}ms;`);
  lines.push(`  --duration-base: ${t.motion.base}ms;`);
  lines.push(`  --duration-slow: ${t.motion.slow}ms;`);
  lines.push('}', '');
  lines.push(':root {');
  for (const [k, v] of Object.entries(t.color.light)) lines.push(`  --au-${kebab(k)}: ${v};`);
  lines.push('  color-scheme: light;', '}', '');
  lines.push(':root.dark, [data-theme="dark"] {');
  for (const [k, v] of Object.entries(t.color.dark)) lines.push(`  --au-${kebab(k)}: ${v};`);
  lines.push('  color-scheme: dark;', '}', '');
  return lines.join('\n');
}

// ---------- Dart ----------
function dartColor(hex) {
  let h = hex.replace('#', '');
  if (h.length === 6) h = 'FF' + h;
  else if (h.length === 8) h = h.slice(6, 8) + h.slice(0, 6); // RRGGBBAA -> AARRGGBB
  return `Color(0x${h.toUpperCase()})`;
}
function dart() {
  const out = [`// ${header}`, '// ignore_for_file: constant_identifier_names', '', "import 'package:flutter/widgets.dart';", ''];
  const block = (name, obj) => {
    out.push(`abstract final class ${name} {`);
    for (const [k, v] of Object.entries(obj)) out.push(`  static const ${k} = ${dartColor(v)};`);
    out.push('}', '');
  };
  block('AuBrandColors', t.color.brand);
  block('AuDarkColors', t.color.dark);
  block('AuLightColors', t.color.light);
  block('AuSemanticColors', t.color.semantic);
  block('AuMacroColors', t.color.macro);
  out.push('abstract final class AuSpacing {');
  for (const [k, v] of Object.entries(t.spacing)) out.push(`  static const double s${k} = ${v};`);
  out.push('}', '');
  out.push('abstract final class AuRadius {');
  for (const [k, v] of Object.entries(t.radius)) out.push(`  static const double ${k} = ${v};`);
  out.push('}', '');
  out.push('abstract final class AuMotion {');
  out.push(`  static const fast = Duration(milliseconds: ${t.motion.fast});`);
  out.push(`  static const base = Duration(milliseconds: ${t.motion.base});`);
  out.push(`  static const slow = Duration(milliseconds: ${t.motion.slow});`);
  out.push(`  static const easeOut = Cubic(${t.motion.easeOut.join(', ')});`);
  out.push(`  static const easeInOut = Cubic(${t.motion.easeInOut.join(', ')});`);
  out.push('}', '');
  out.push('class AuTypeSpec {');
  out.push('  const AuTypeSpec(this.size, this.height, this.weight, this.tracking);');
  out.push('  final double size;', '  final double height;', '  final int weight;', '  final double tracking;', '}', '');
  out.push('abstract final class AuType {');
  out.push(`  static const fontLatin = '${t.font.latin}';`);
  out.push(`  static const fontArabic = '${t.font.arabic}';`);
  for (const [k, v] of Object.entries(t.typography)) out.push(`  static const ${k} = AuTypeSpec(${v.size}, ${v.lineHeight}, ${v.weight}, ${v.tracking});`);
  out.push('}', '');
  out.push(`const double kAuTouchTarget = ${t.touchTarget};`, '');
  return out.join('\n');
}

const targets = [
  [resolve(root, 'backend/resources/css/tokens.css'), css()],
  [resolve(root, 'packages/core/lib/src/design/app_tokens.dart'), dart()],
];
for (const [file, content] of targets) {
  mkdirSync(dirname(file), { recursive: true });
  writeFileSync(file, content);
  console.log('wrote', file.replace(root + '/', ''));
}
