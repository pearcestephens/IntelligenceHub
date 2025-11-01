#!/usr/bin/env node
import { readdirSync, readFileSync, existsSync } from 'fs';
import { join, extname, dirname } from 'path';
import fetch from 'node-fetch';

const SERVER_BASE = 'https://staff.vapeshed.co.nz/';
const PUBLIC_DIR = join(process.cwd(), 'public');
const LOCAL_MODE = process.argv.includes('--local');

function* walk(dir) {
  for (const entry of readdirSync(dir, { withFileTypes: true })) {
    const full = join(dir, entry.name);
    if (entry.isDirectory()) yield* walk(full);
    else yield full;
  }
}

const htmlFiles = [...walk(PUBLIC_DIR)].filter(f => ['.html', '.htm'].includes(extname(f)));
const broken = [];

function resolveHrefForFile(href, htmlFile) {
  if (!href || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('#')) return null;
  if (href.startsWith('http://') || href.startsWith('https://')) return href;
  // Compute the URL path corresponding to the HTML file inside /public
  const relPath = htmlFile.replace(PUBLIC_DIR, '').replace(/\\/g, '/'); // windows-safe
  const relDir = dirname(relPath).replace(/^\/?/, '');
  const base = new URL(relDir.endsWith('/') ? relDir : relDir + '/', SERVER_BASE);
  // Use WHATWG URL to resolve ../ and ./ correctly
  const resolved = new URL(href, base).toString();
  return resolved;
}

async function checkLink(href) {
  try {
    let res = await fetch(href, { method: 'HEAD' });
    if (!res.ok && [403, 405, 501].includes(res.status)) {
      // Some servers block HEAD; retry with GET (no body read)
      res = await fetch(href, { method: 'GET' });
    }
    if (!res.ok) return `${href} -> ${res.status}`;
    return null;
  } catch (e) {
    return `${href} -> ${e.message}`;
  }
}

(async () => {
  for (const file of htmlFiles) {
    const html = readFileSync(file, 'utf8');
    const links = [...html.matchAll(/\s(?:href|src)=["']([^"']+)["']/g)].map(m => m[1]);
    for (const l of links) {
      const url = resolveHrefForFile(l, file);
      if (!url) continue;
      if (LOCAL_MODE) {
        try {
          const parsed = new URL(url);
          if (parsed.protocol === 'http:' || parsed.protocol === 'https:') {
            // Skip absolute external links in local mode
            continue;
          }
          const pathname = parsed.pathname;
          const localPath = join(PUBLIC_DIR, pathname.replace(/^\//, ''));
          if (!existsSync(localPath)) {
            broken.push({ file, link: l, error: `Missing local file: ${localPath}` });
          }
        } catch (e) {
          broken.push({ file, link: l, error: `Invalid URL: ${url}` });
        }
      } else {
        const err = await checkLink(url);
        if (err) broken.push({ file, link: l, error: err });
      }
    }
  }
  if (broken.length) {
    console.error('Broken links found:');
    for (const b of broken) console.error(`- ${b.file}: ${b.link} -> ${b.error}`);
    process.exit(1);
  } else {
    console.log('All links OK');
  }
})();
