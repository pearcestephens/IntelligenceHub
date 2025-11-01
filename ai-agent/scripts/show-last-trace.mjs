#!/usr/bin/env node
import { readdirSync } from 'fs';
import { join } from 'path';
import { spawn } from 'child_process';

const ROOT = process.cwd();
const TR = join(ROOT, 'test-results');
const zips = [];
function walk(dir){
  try { for (const e of readdirSync(dir, { withFileTypes: true })) {
    const p = join(dir, e.name);
    if (e.isDirectory()) walk(p);
    else if (e.isFile() && e.name.endsWith('.zip')) zips.push(p);
  }} catch(_) {}
}
walk(TR);
if (!zips.length) { console.error('No traces found under test-results'); process.exit(1); }
const last = zips.sort((a,b)=>a.localeCompare(b)).pop();
console.log('Opening trace:', last);
const cmd = 'npx';
const args = ['playwright', 'show-trace', last];
const child = spawn(cmd, args, { stdio: 'inherit' });
child.on('exit', code => process.exit(code || 0));
