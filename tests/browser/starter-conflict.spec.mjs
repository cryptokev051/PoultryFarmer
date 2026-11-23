import { test, expect } from '@playwright/test';
import path from 'node:path';

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

async function installTheme(page) {
  await page.goto('/wp-admin/theme-install.php?upload', { waitUntil: 'domcontentloaded' });
  await page.getByRole('button', { name: 'Upload Theme' }).click();
  await page.locator('input[type="file"]').setInputFiles(path.resolve('..', 'releases', 'poultryfarmer.zip'));
  await page.locator('#install-theme-submit').click();
  await expect(page.locator('body')).toContainText('Theme installed successfully');
  await page.getByRole('link', { name: 'Activate' }).click();
  await page.waitForLoadState('domcontentloaded');
}

test('starter setup blocks an untracked conflicting Page slug before writes', async ({ page }) => {
  test.setTimeout(240_000);
  await login(page);
  await installTheme(page);
  await page.goto('/wp-admin/tools.php?page=poultryfarmer-starter', { waitUntil: 'domcontentloaded' });
  await expect(page.locator('h1')).toHaveText('Poultry Farmer Starter Setup');
  await expect(page.locator('.notice-error')).toContainText('Page slug "farming" already belongs to an untracked Page');
  await expect(page.locator('input[type="submit"]')).toBeDisabled();
  await expect(page.locator('body')).not.toContainText('Starter setup completed');
});
