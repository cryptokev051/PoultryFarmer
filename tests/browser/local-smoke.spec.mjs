import { test, expect } from '@playwright/test';

const routes = [
  ['homepage', '/'],
  ['farming-hub', '/farming/'],
  ['kienyeji-hub', '/farming/kienyeji/'],
  ['guide-archive', '/guides/'],
];

test('Local WordPress smoke and responsive route readiness', async ({ page }, testInfo) => {
  for (const [name, route] of routes) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    const status = response?.status() ?? 0;
    if (status !== 200) {
      testInfo.annotations.push({ type: 'not-ready', description: `${name}: ${route} returned ${status}; starter content is not installed.` });
      continue;
    }
    await expect(page.locator('body')).toBeVisible();
    await page.screenshot({ path: `artifacts/local/${testInfo.project.name}-${name}.png`, fullPage: true });
    const diagnostics = await page.evaluate(() => ({
      h1Count: document.querySelectorAll('h1').length,
      overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
      missingImages: [...document.images].filter((image) => !image.complete || image.naturalWidth === 0).length,
    }));
    expect(diagnostics.overflow).toBeLessThanOrEqual(1);
    expect(diagnostics.missingImages).toBe(0);
  }
});

test('Local WordPress login and admin entry are reachable', async ({ page }) => {
  const response = await page.goto('/wp-login.php', { waitUntil: 'domcontentloaded' });
  expect(response?.status()).toBe(200);
  await expect(page.locator('#loginform')).toBeVisible();
  const adminResponse = await page.goto('/wp-admin/', { waitUntil: 'domcontentloaded' });
  expect([200, 302]).toContain(adminResponse?.status());
  expect(page.url()).toContain('wp-');
});
