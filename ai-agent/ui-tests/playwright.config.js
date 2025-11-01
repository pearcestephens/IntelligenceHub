// @ts-check
const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: __dirname,
  timeout: 30_000,
  retries: 0,
  reporter: [['list']],
  use: {
    baseURL: 'https://staff.vapeshed.co.nz/agent',
    headless: true,
    ignoreHTTPSErrors: true,
    viewport: { width: 1280, height: 800 },
    launchOptions: {
      args: ['--no-sandbox', '--disable-dev-shm-usage']
    }
  },
});
