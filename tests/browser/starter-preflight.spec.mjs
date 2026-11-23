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

async function snapshot(page) {
  await page.goto('/wp-admin/options-permalink.php', { waitUntil: 'domcontentloaded' });
  const permalink = await page.locator('#permalink_structure').inputValue();
  return page.evaluate(async () => {
    const settings = await window.wp.apiFetch({ path: '/wp/v2/settings' });
    const posts = await window.wp.apiFetch({ path: '/wp/v2/posts?status=any&per_page=100' });
    const pages = await window.wp.apiFetch({ path: '/wp/v2/pages?status=any&per_page=100' });
    const categories = await window.wp.apiFetch({ path: '/wp/v2/categories?hide_empty=false&per_page=100' });
    return {
      show_on_front: settings.show_on_front,
      page_on_front: settings.page_on_front,
      page_for_posts: settings.page_for_posts,
      posts: posts.map(post => post.id),
      pages: pages.map(post => post.id),
      categories: categories.map(term => term.id),
    };
  }).then(settings => ({ ...settings, permalink }));
}

async function setPermalink(page, value) {
  await page.goto('/wp-admin/options-permalink.php', { waitUntil: 'domcontentloaded' });
  await page.locator('#permalink_structure').fill(value);
  await page.getByRole('button', { name: 'Save Changes' }).click();
  await page.waitForLoadState('domcontentloaded');
}

async function openSetup(page) {
  await page.goto('/wp-admin/tools.php?page=poultryfarmer-starter', { waitUntil: 'domcontentloaded' });
  await expect(page.locator('h1')).toHaveText('Poultry Farmer Starter Setup');
}

test.describe.configure({ mode: 'serial' });

test('negative fresh-install preflight protects database and settings', async ({ page }) => {
  test.setTimeout(600_000);
  await login(page);
  await installTheme(page);

  await openSetup(page);
  await expect(page.locator('.notice-error')).toHaveCount(0);

  const fresh = await snapshot(page);
  await setPermalink(page, '/%postname%/');
  await openSetup(page);
  await expect(page.locator('.notice-error')).toContainText('Existing permalink configuration');
  expect(await snapshot(page)).toMatchObject({ ...fresh, permalink: '/%postname%/' });

  await setPermalink(page, '/news/%postname%/');
  await openSetup(page);
  await expect(page.locator('.notice-error')).toContainText('Existing permalink configuration');
  expect((await snapshot(page)).permalink).toBe('/news/%postname%/');

  await setPermalink(page, '/guides/%postname%/');
  await page.goto('/wp-admin/post-new.php', { waitUntil: 'domcontentloaded' });
  await page.waitForFunction(() => Boolean(window.wp?.data?.select('core/editor')), null, { timeout: 90_000 });
  await page.evaluate(async () => {
    await window.wp.data.dispatch('core/editor').editPost({
      title: 'Preflight owner Post',
      status: 'publish',
      content: '<!-- wp:paragraph --><p>Existing owner content fixture.</p><!-- /wp:paragraph -->',
    });
    await window.wp.data.dispatch('core/editor').savePost();
  });
  await openSetup(page);
  await expect(page.locator('.notice-error')).toContainText('Initial setup blocked');
  const ownerPostState = await snapshot(page);
  expect(ownerPostState.permalink).toBe('/guides/%postname%/');
  expect(ownerPostState.posts.length).toBeGreaterThan(1);

  await setPermalink(page, '/%year%/%monthnum%/%day%/%postname%/');
  await page.goto('/wp-admin/edit-tags.php?taxonomy=category', { waitUntil: 'domcontentloaded' });
  await page.locator('#tag-name').fill('Preflight custom category');
  await page.locator('#tag-slug').fill('preflight-custom');
  await page.locator('#submit').click();
  await openSetup(page);
  await expect(page.locator('.notice-error')).toContainText(/custom Category|Additional editorial or custom content/);

  await openSetup(page);
  await expect(page.locator('input[type="submit"]')).toBeDisabled();
});
