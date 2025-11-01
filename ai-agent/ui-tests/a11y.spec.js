// @ts-check
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { writeFile } from 'fs/promises';

test.describe('AI Agent UI - Accessibility', () => {
  test('has no serious or critical accessibility violations', async ({ page }, testInfo) => {
    await page.goto('index.html');
    await page.waitForLoadState('domcontentloaded');
    // Smoke presence
    await page.waitForSelector('#app-title', { state: 'attached' });

    const results = await new AxeBuilder({ page })
      .withTags(["wcag2a", "wcag2aa"]) // core WCAG rules
      .analyze();

    // Persist full report for triage
  const out = testInfo.outputPath('a11y-report.json');
  await page.context().storageState({ path: testInfo.outputPath('state.json') }); // keep contextual state if needed
  await writeFile(out, JSON.stringify(results, null, 2), 'utf8');

    const violations = results.violations.filter(v => ['serious', 'critical'].includes(v.impact || 'minor'));
    if (violations.length) {
      console.log('\nAccessibility violations (serious+):');
      for (const v of violations) console.log(`- ${v.id}: ${v.help} (${v.impact})`);
    }
    expect(violations).toHaveLength(0);
  });
});
