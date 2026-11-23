import { test, expect } from '@playwright/test';
import path from 'node:path';

async function login(page) {
  await page.goto('/wp-login.php', { waitUntil: 'domcontentloaded' });
  await page.locator('#user_login').fill(process.env.PF_WP_USER);
  await page.locator('#user_pass').fill(process.env.PF_WP_PASSWORD);
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

async function settings(page) {
  await page.goto('/wp-admin/options-permalink.php', { waitUntil: 'domcontentloaded' });
  const permalink = await page.locator('#permalink_structure').inputValue();
  return page.evaluate(async permalinkValue => {
    const value = await window.wp.apiFetch({ path: '/wp/v2/settings' });
    const pages = await window.wp.apiFetch({ path: '/wp/v2/pages?status=any&per_page=100' });
    const categories = await window.wp.apiFetch({ path: '/wp/v2/categories?hide_empty=false&per_page=100' });
    return { show_on_front: value.show_on_front, page_on_front: value.page_on_front, page_for_posts: value.page_for_posts, permalink: permalinkValue, pages: pages.map(item => item.id), categories: categories.map(item => item.id) };
  }, permalink);
}

async function setReading(page, data) {
  await page.evaluate(async value => window.wp.apiFetch({ path: '/wp/v2/settings', method: 'PUT', data: value }), data);
}

async function preview(page) {
  await page.goto('/wp-admin/tools.php?page=poultryfarmer-starter', { waitUntil: 'domcontentloaded' });
  return page.locator('.notice-error').allTextContents();
}

test('initial Reading settings and Privacy Policy guards block without writes', async ({ page }) => {
  test.setTimeout(300_000);
  await login(page);
  await installTheme(page);
  const baseline = await settings(page);
  expect(await preview(page)).toHaveLength(0);

  for (const mutation of [
    { show_on_front: 'page', page_on_front: 2, page_for_posts: 0 },
    { show_on_front: 'posts', page_on_front: 2, page_for_posts: 0 },
    { show_on_front: 'posts', page_on_front: 0, page_for_posts: 2 },
  ]) {
    await setReading(page, mutation);
    expect((await preview(page)).join(' ')).toContain('Existing Reading settings');
    expect((await settings(page)).pages).toEqual(baseline.pages);
    expect((await settings(page)).categories).toEqual(baseline.categories);
    await setReading(page, { show_on_front: 'posts', page_on_front: 0, page_for_posts: 0 });
  }

  await page.evaluate(async () => {
    const pages = await window.wp.apiFetch({ path: '/wp/v2/pages?slug=privacy-policy&status=any&context=edit' });
    await window.wp.apiFetch({ path: `/wp/v2/pages/${pages[0].id}`, method: 'POST', data: { title: 'Privacy Policy', slug: 'privacy-policy', status: 'draft', content: '<p>Original privacy policy text, then owner-edited.</p>' } });
  });
  expect((await preview(page)).join(' ')).toContain('Privacy Policy');
  expect((await settings(page)).pages).toEqual(baseline.pages);
  expect((await settings(page)).categories).toEqual(baseline.categories);
});
