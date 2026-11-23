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

test('release ZIP installs and activates through WordPress administration', async ({ page }) => {
  test.setTimeout(180_000);
  await login(page);
  await page.goto('/wp-admin/plugin-install.php?tab=upload', { waitUntil: 'domcontentloaded' });
  await page.locator('input[type="file"]').setInputFiles(path.resolve('..', 'releases', 'poultryfarmer-starter.zip'));
  await page.locator('#install-plugin-submit').click();
  await expect(page.locator('body')).toContainText('Plugin installed successfully');
  await page.getByRole('link', { name: 'Activate Plugin' }).click();
  await page.goto('/wp-admin/plugins.php', { waitUntil: 'domcontentloaded' });
  await expect(page.getByRole('link', { name: 'Deactivate Poultry Farmer Starter Setup' })).toBeVisible();
  await page.goto('/wp-admin/tools.php?page=poultryfarmer-starter', { waitUntil: 'domcontentloaded' });
  await expect(page.locator('h1')).toHaveText('Poultry Farmer Starter Setup');
  await expect(page.locator('body')).toContainText('The active theme is not Poultry Farmer. Activate the approved theme before setup.');
});
