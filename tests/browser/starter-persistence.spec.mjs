import { test, expect } from '@playwright/test';
import fs from 'node:fs/promises';
import path from 'node:path';

const diagnosticsPath = path.resolve('screenshots', 'starter-setup', 'diagnostics.json');

async function login(page) {
  const username = process.env.PF_WP_USER;
  const password = process.env.PF_WP_PASSWORD;
  expect(username, 'PF_WP_USER must contain the temporary local administrator username').toBeTruthy();
  expect(password, 'PF_WP_PASSWORD must contain the temporary local administrator password').toBeTruthy();
  await page.goto('/wp-login.php', { waitUntil: 'domcontentloaded' });
  await page.locator('#user_login').fill(username);
  await page.locator('#user_pass').fill(password);
  await page.locator('#wp-submit').click();
  await page.waitForURL(/\/wp-admin\/?(?:index\.php)?$/);
}

test('starter content and deactivated state persist after normal restart', async ({ page }) => {
  const diagnostics = JSON.parse(await fs.readFile(diagnosticsPath, 'utf8'));
  await login(page);
  await page.goto(`/wp-admin/post.php?post=${diagnostics.pageIds.kienyeji}&action=edit`, { waitUntil: 'domcontentloaded' });
  await page.waitForFunction(() => Boolean(window.wp?.data?.select('core/editor')?.getCurrentPostId()), null, { timeout: 90_000 });
  const pageState = await page.evaluate(() => {
    const editor = window.wp.data.select('core/editor');
    return { content: editor.getEditedPostContent(), template: editor.getEditedPostAttribute('template'), status: editor.getEditedPostAttribute('status') };
  });
  expect(pageState.content).toContain(diagnostics.ownerIntroduction);
  expect(pageState.content).toContain(String(diagnostics.categoryIds.kienyeji));
  expect(pageState.template).toBe('poultry-hub');
  expect(pageState.status).toBe('publish');
  await page.goto('/wp-admin/plugins.php', { waitUntil: 'domcontentloaded' });
  await expect(page.getByRole('link', { name: 'Activate Poultry Farmer Starter Setup' })).toBeVisible();
  await page.goto('/farming/kienyeji/', { waitUntil: 'load' });
  await expect(page.locator('main')).toContainText(diagnostics.ownerIntroduction);
  await expect(page.locator('main')).toContainText('Starter QA Kienyeji Feeding Guide');
});
