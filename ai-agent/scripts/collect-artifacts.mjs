#!/usr/bin/env node
import { readdirSync, statSync, writeFileSync } from 'fs';
import { join, extname, basename, dirname } from 'path';

const ROOT = process.cwd();
const RESULTS_DIR = join(ROOT, 'test-results');
const REPORT_DIR = join(ROOT, 'ui-tests', 'playwright-report');

function* walk(dir) {
  try {
    for (const entry of readdirSync(dir, { withFileTypes: true })) {
      const full = join(dir, entry.name);
      if (entry.isDirectory()) yield* walk(full);
      else yield full;
    }
  } catch (_) { /* ignore missing dirs */ }
}

const assets = [];
for (const file of walk(RESULTS_DIR)) {
  const ext = extname(file).toLowerCase();
  if (!['.png', '.webm', '.zip'].includes(ext)) continue;
  const name = basename(file);
  const rel = file.replace(`${ROOT}/`, '').replace(/\\/g, '/');
  const info = { file, name, rel };
  assets.push(info);
}

const summary = {
  generated_at: new Date().toISOString(),
  total: assets.length,
  assets,
  report_hint: REPORT_DIR,
};

const outJson = join(ROOT, 'ui-tests', 'artifacts.json');
writeFileSync(outJson, JSON.stringify(summary, null, 2), 'utf8');

const promptLines = [];
promptLines.push('# AI Agent UI – Visual QA Prompt');
promptLines.push('Goal: Review these screenshots and suggest concrete UI/UX improvements (contrast, spacing, hierarchy, focus states, accessibility). Provide specific CSS/HTML changes.');
promptLines.push('Context: Bootstrap 5, icons via Font Awesome, single-page UI under /agent/, JS in public/agent/js/app.js.');
promptLines.push('For each issue, include: what/why, minimal CSS/HTML fix, and any accessibility notes.');
promptLines.push('Screenshots:');
for (const a of assets.filter(a => a.name.endsWith('.png'))) {
  promptLines.push(`- ${a.rel}`);
}
promptLines.push('If available, use traces/videos for flow insights.');

const outPrompt = join(ROOT, 'ui-tests', 'vision_prompt.txt');
writeFileSync(outPrompt, promptLines.join('\n'), 'utf8');

console.log('Artifacts summary written to:', outJson);
console.log('Vision prompt written to:', outPrompt);
console.log('HTML report (if generated):', REPORT_DIR);