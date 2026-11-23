import { test, expect } from '@playwright/test';
import fs from 'node:fs/promises';
import path from 'node:path';

const diagnosticsPath = path.resolve('screenshots', 'phase2b2-editor-workflow', 'workflow-diagnostics.json');

async function login(page) {
  const username = process.env.PF_WP_USER;
  const password = process.env.PF_WP_PASSWORD;
  expect(username).toBeTruthy();
  expect(password).toBeTruthy();
  await page.goto('/wp-login.php', { waitUntil: 'domcontentloaded' });
  await page.locator('#user_login').fill(username);
  await page.locator('#user_pass').fill(password);
  await page.locator('#wp-submit').click();
  await page.waitForURL(/\/wp-admin\/?(?:index\.php)?$/);
}

async function reopen(page, id) {
  await page.goto(`/wp-admin/post.php?post=${id}&action=edit`, { waitUntil: 'domcontentloaded' });
  await page.waitForFunction(() => Boolean(window.wp?.data?.select('core/editor')?.getCurrentPostId()), null, { timeout: 90_000 });
  return page.evaluate(() => {
    const editor = window.wp.data.select('core/editor');
    return {
      id: editor.getCurrentPostId(), title: editor.getEditedPostAttribute('title'), status: editor.getEditedPostAttribute('status'),
      template: editor.getEditedPostAttribute('template'), content: editor.getEditedPostContent(), featuredMedia: editor.getEditedPostAttribute('featured_media') || 0,
    };
  });
}

test('authenticated editor content persists after a normal Playground restart', async ({ page }) => {
  test.setTimeout(240_000);
  const diagnostics = JSON.parse(await fs.readFile(diagnosticsPath, 'utf8'));
  await login(page);
  const hub = await reopen(page, diagnostics.content.hub.id);
  expect(hub.title).toBe(diagnostics.content.hub.title);
  expect(hub.template).toBe('poultry-hub');
  expect(hub.content).toContain('Production model overview');
  const patternPost = await reopen(page, diagnostics.content.patternPost.id);
  expect(patternPost.content).toContain('Replacement text saved through the authenticated block editor.');
  const guide = await reopen(page, diagnostics.content.publishedGuide.id);
  expect(guide.status).toBe('publish');
  expect(guide.content).toContain('after editorial update');
  expect(guide.featuredMedia).toBe(diagnostics.content.publishedGuide.mediaId);
  const response = await page.goto(diagnostics.content.publishedGuide.url, { waitUntil: 'load' });
  expect(response?.status()).toBe(200);
  await expect(page.locator('main h1')).toHaveText('Editor QA 20261119 Published Workflow Guide');
});
