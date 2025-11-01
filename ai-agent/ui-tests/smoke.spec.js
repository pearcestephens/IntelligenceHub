// @ts-check
import { test, expect } from '@playwright/test';

test.describe('AI Agent E2E Smoke', () => {
  test('loads UI and sends a message (stubbed)', async ({ page }) => {
    await page.route('**/api/health.php', async route => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ status: 'healthy' }) });
    });
    await page.route('**/api/conversations.php', async route => {
      if (route.request().method() === 'GET') {
        await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true, conversations: [] }) });
      } else {
        await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true, conversation: { conversation_id: 'conv_e2e' } }) });
      }
    });
    await page.route('**/api/chat.php', async route => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true, response: { content: 'Hello from E2E', tool_calls: [] } }) });
    });

  // Use relative navigation so baseURL path (/agent) is preserved
  await page.goto('index.html');
  await page.waitForLoadState('domcontentloaded');
  await expect(page).toHaveTitle(/AI Agent/i);
  await page.waitForSelector('#app-title', { timeout: 15000, state: 'attached' });
  await page.screenshot({ path: test.info().outputPath('smoke-01-loaded.png'), fullPage: true });

  await page.locator('#new-conversation').click();
  await expect(page.locator('#message-input')).toBeEnabled();

    const streamToggle = page.locator('#stream-toggle');
    if (await streamToggle.isChecked()) {
      await streamToggle.click();
    }
    await page.fill('#message-input', 'Hello');
  await page.locator('#send-btn').click();
  await page.screenshot({ path: test.info().outputPath('smoke-02-after-send.png'), fullPage: true });

    await expect(page.locator('#messages-container .message.assistant')).toHaveCount(1);
    await page.screenshot({ path: test.info().outputPath('smoke-03-assistant-visible.png'), fullPage: true });
  });
});
