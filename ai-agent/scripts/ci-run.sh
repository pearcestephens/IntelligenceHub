#!/usr/bin/env bash
set -euo pipefail

echo "[1/6] Install deps"
npm ci --no-audit --no-fund

echo "[2/6] Install Playwright browser"
PLAYWRIGHT_BROWSERS_PATH=$PWD/.playwright-browsers npx playwright install chromium

echo "[3/6] Run unit tests"
npm test

echo "[4/6] Check links (local)"
npm run links -- --local

echo "[5/6] Run E2E tests"
npx playwright test -c ui-tests/playwright.config.cjs --reporter=list --workers=1

echo "[6/6] Collect artifacts"
node ./scripts/collect-artifacts.mjs
echo "Artifacts written to ui-tests/artifacts.json and ui-tests/vision_prompt.txt"
