// @ts-check
import { test, expect } from '@playwright/test';

test.describe('AI Agent UI - Comprehensive controls', () => {
  test.beforeEach(async ({ page }) => {
    await page.addInitScript(() => {
      class FakeSpeechRecognition {
        constructor() { this.continuous = false; this.interimResults = false; this.lang = 'en-US'; }
        start() { this.onstart && this.onstart(); setTimeout(() => { this.onresult && this.onresult({ results: [[{ transcript: 'Test transcript' }]] }); this.onend && this.onend(); }, 10); }
        stop() { this.onend && this.onend(); }
      }
      // @ts-ignore
      window.SpeechRecognition = FakeSpeechRecognition;
      // @ts-ignore
      window.webkitSpeechRecognition = FakeSpeechRecognition;
    });

    await page.route('**/api/health.php**', async route => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ status: 'healthy' }) });
    });
    await page.route('**/api/conversations.php**', async route => {
      if (route.request().method() === 'GET') {
        await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true, conversations: [] }) });
      } else {
        await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true, conversation: { conversation_id: 'conv_all' } }) });
      }
    });
    await page.route('**/api/chat.php**', async route => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true, response: { content: 'Assistant reply', tool_calls: [] } }) });
    });
    await page.route('**/api/knowledge.php/documents**', async route => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true, documents: [{ title: 'Doc 1', created_at: new Date().toISOString() }] }) });
    });
    await page.route('**/api/upload.php**', async route => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true, document: { id: 'doc1' } }) });
    });
    await page.route('**/api/knowledge.php/search**', async route => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true, results: [{ title: 'Result A', content: 'Snippet', score: 0.99 }] }) });
    });
  });

  test('all controls exist, clickable, and produce expected UI effects', async ({ page }) => {
    const errors = [];
    page.on('pageerror', e => errors.push(String(e)));
    page.on('console', msg => { if (msg.type() === 'error') errors.push(`console:${msg.text()}`); });
    const failedRequests = [];
    page.on('requestfailed', req => failedRequests.push(`${req.method()} ${req.url()}`));

  // Use relative navigation so baseURL path (/agent) is preserved
  await page.goto('index.html');
  await page.waitForLoadState('domcontentloaded');
  await expect(page).toHaveTitle(/AI Agent/i);
  await page.waitForSelector('#app-title', { timeout: 15000, state: 'attached' });
  await page.screenshot({ path: test.info().outputPath('all-01-loaded.png'), fullPage: true });
  await expect(page.locator('#status-badge')).toBeVisible();

  const ids = ['#new-conversation', '#knowledge-toggle', '#health-check', '#voice-btn', '#message-input', '#send-btn', '#stream-toggle', '#tools-toggle', '#file-drop-zone', '#search-btn'];
  for (const id of ids) { await expect(page.locator(id)).toBeVisible(); }
  // Hidden file input should exist but remain hidden (triggered via drop zone)
  await expect(page.locator('#file-input')).toBeHidden();

  await page.click('#health-check');
    await expect(page.locator('#status-text')).toHaveText(/Online/i, { timeout: 4000 });
  await page.screenshot({ path: test.info().outputPath('all-02-health-checked.png'), fullPage: true });

  await page.click('#knowledge-toggle');
  await expect(page.locator('#knowledge-panel')).toHaveClass(/show/);
  await page.screenshot({ path: test.info().outputPath('all-03-knowledge-open.png'), fullPage: true });
    await page.fill('#knowledge-search', 'foo');
    await page.click('#search-btn');
    await expect(page.locator('#documents-list')).toContainText('Result A');
  await page.click('#knowledge-close');
    await expect(page.locator('#knowledge-panel')).not.toHaveClass(/show/);
  await page.screenshot({ path: test.info().outputPath('all-04-knowledge-closed.png'), fullPage: true });

  await page.click('#new-conversation');
    await expect(page.locator('#message-input')).toBeEnabled({ timeout: 5000 });
    await expect(page.locator('#send-btn')).toBeEnabled({ timeout: 5000 });
  await page.screenshot({ path: test.info().outputPath('all-05-new-conversation.png'), fullPage: true });

    const stream = page.locator('#stream-toggle');
    const tools = page.locator('#tools-toggle');
    if (await stream.isChecked()) await stream.click();
    if (!(await tools.isChecked())) await tools.click();

  await page.fill('#message-input', 'Hello');
  await page.click('#send-btn');
    await page.waitForSelector('#messages-container .message.assistant', { timeout: 5000 });
    await expect(page.locator('#messages-container .message.assistant')).toHaveCount(1);
  await page.screenshot({ path: test.info().outputPath('all-06-assistant-reply.png'), fullPage: true });

    await page.click('#voice-btn');
  await expect(page.locator('#voice-btn')).not.toHaveClass(/recording/, { timeout: 2000 });

    // Re-open knowledge panel for upload
    await page.click('#knowledge-toggle');
    await expect(page.locator('#knowledge-panel')).toHaveClass(/show/);
    // Upload using hidden input directly
    await page.setInputFiles('#file-input', { name: 'test.txt', mimeType: 'text/plain', buffer: Buffer.from('hello') });
    // Prefer notification, but fall back to documents list content if timing differs
    const notif = page.locator('text=uploaded successfully');
    try {
      await expect(notif).toBeVisible({ timeout: 4000 });
    } catch {
      await expect(page.locator('#documents-list')).toContainText('Doc 1', { timeout: 4000 });
    }
  await page.screenshot({ path: test.info().outputPath('all-07-upload-success.png'), fullPage: true });

    expect(errors, `Page errors/console: ${errors.join('\n')}`).toHaveLength(0);
    const ourFails = failedRequests.filter(u => u.includes('/agent/'));
    expect(ourFails, `Failed requests: ${ourFails.join('\n')}`).toHaveLength(0);
  });
});
