// @ts-check
const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: __dirname,
  timeout: 30_000,
  retries: 0,
  reporter: [['list'], ['html', { open: 'never', outputFolder: 'ui-tests/playwright-report' }]],
  use: {
  baseURL: 'http://127.0.0.1:5173/agent/',
    headless: true,
    ignoreHTTPSErrors: true,
    viewport: { width: 1280, height: 800 },
    launchOptions: {
      args: ['--no-sandbox', '--disable-dev-shm-usage']
    },
    screenshot: 'on',
    trace: 'retain-on-failure',
    video: 'retain-on-failure'
  },
  webServer: {
    command: `npx http-server ${__dirname.replace(/ui-tests$/, 'public')} -p 5173 -c-1`,
    url: 'http://127.0.0.1:5173/agent/index.html',
    reuseExistingServer: true,
    timeout: 30000,
    stdout: 'ignore',
  },
});
