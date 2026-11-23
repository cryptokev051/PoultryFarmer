import { test, expect } from '@playwright/test';
import fs from 'node:fs/promises';
import path from 'node:path';

const screenshotRoot = path.resolve('screenshots', 'starter-setup');
const diagnosticsPath = path.join(screenshotRoot, 'diagnostics.json');
const invalidPagePattern = /maintenance|critical error|error establishing a database connection|fatal error/i;
const categorySlugs = ['kienyeji', 'layers', 'broilers', 'hatching', 'feeding', 'housing', 'health', 'records'];
const pageSlugs = ['home', 'farming', 'kienyeji', 'layers', 'broilers', 'hatching', 'management', 'feeding', 'housing', 'health', 'records', 'prices', 'calculators', 'guides', 'start-here', 'about', 'editorial-policy', 'contact'];

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
  await page.goto('/wp-admin/themes.php', { waitUntil: 'domcontentloaded' });
  const installedTheme = page.locator('.theme[data-slug="poultryfarmer"]');
  if (await installedTheme.count()) {
    if (await installedTheme.evaluate(theme => theme.classList.contains('active'))) return;
    await installedTheme.getByRole('link', { name: 'Activate' }).click();
    await page.waitForLoadState('domcontentloaded');
    return;
  }
  await page.goto('/wp-admin/theme-install.php?upload', { waitUntil: 'domcontentloaded' });
  await page.getByRole('button', { name: 'Upload Theme' }).click();
  await page.locator('input[type="file"]').setInputFiles(path.resolve('..', 'releases', 'poultryfarmer.zip'));
  await page.locator('#install-theme-submit').click();
  await expect(page.locator('body')).toContainText('Theme installed successfully');
  await page.getByRole('link', { name: 'Activate' }).click();
  await page.waitForLoadState('domcontentloaded');
}

async function waitForEditor(page) {
  await page.waitForFunction(() => Boolean(window.wp?.data?.select('core/editor')?.getCurrentPostType()), null, { timeout: 90_000 });
  await page.waitForFunction(() => !window.wp.data.select('core/editor').isSavingPost(), null, { timeout: 90_000 });
}

async function editorApi(page, callback, argument) {
  await waitForEditor(page);
  return page.evaluate(callback, argument);
}

async function findPages(page) {
  await page.goto('/wp-admin/post-new.php?post_type=page', { waitUntil: 'domcontentloaded' });
  return editorApi(page, async slugs => {
    const result = {};
    for (const slug of slugs) {
      const pages = await window.wp.apiFetch({ path: `/wp/v2/pages?slug=${encodeURIComponent(slug)}&status=any&context=edit&per_page=100` });
      result[slug] = pages.map(item => ({ id: item.id, slug: item.slug, parent: item.parent, status: item.status, template: item.template, title: item.title.raw, content: item.content.raw, excerpt: item.excerpt.raw }));
    }
    return result;
  }, pageSlugs);
}

async function categories(page) {
  const response = await page.request.get('/wp-json/wp/v2/categories?per_page=100', { failOnStatusCode: false });
  expect(response.ok()).toBe(true);
  const terms = await response.json();
  return Object.fromEntries(terms.map(term => [term.slug, { id: term.id, name: term.name, count: term.count }]));
}

async function reopen(page, id) {
  await page.goto(`/wp-admin/post.php?post=${id}&action=edit`, { waitUntil: 'domcontentloaded' });
  await waitForEditor(page);
  return page.evaluate(() => {
    const editor = window.wp.data.select('core/editor');
    return {
      id: editor.getCurrentPostId(), title: editor.getEditedPostAttribute('title'), content: editor.getEditedPostContent(),
      status: editor.getEditedPostAttribute('status'), template: editor.getEditedPostAttribute('template'), categories: editor.getEditedPostAttribute('categories') || [],
    };
  });
}

async function saveEditor(page, attributes) {
  return editorApi(page, async attrs => {
    const dispatch = window.wp.data.dispatch('core/editor');
    const select = window.wp.data.select('core/editor');
    const { content, ...postAttributes } = attrs;
    if (content !== undefined) window.wp.data.dispatch('core/block-editor').resetBlocks(window.wp.blocks.parse(content));
    dispatch.editPost(postAttributes);
    await new Promise(resolve => window.setTimeout(resolve, 100));
    await dispatch.savePost();
    const id = select.getCurrentPostId();
    const restBase = 'page' === select.getCurrentPostType() ? 'pages' : 'posts';
    const saved = await window.wp.apiFetch({ path: `/wp/v2/${restBase}/${id}?context=edit` });
    return { id, slug: saved.slug, status: saved.status, content: saved.content.raw };
  }, attributes);
}

async function createPost(page, attributes) {
  await page.goto('/wp-admin/post-new.php', { waitUntil: 'domcontentloaded' });
  return saveEditor(page, attributes);
}

async function validFrontend(page, route) {
  const response = await page.goto(route, { waitUntil: 'load' });
  const text = await page.locator('body').innerText();
  expect(response?.status(), `${route} must return HTTP 200`).toBe(200);
  expect(invalidPagePattern.test(text), `${route} must be a valid WordPress page`).toBe(false);
  return text;
}

test.describe.configure({ mode: 'serial' });

test('fresh administrator setup, owner edit, second run and deactivation', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1440', 'Setup mutates the fresh fixture once.');
  test.setTimeout(900_000);
  await fs.mkdir(path.join(screenshotRoot, 'desktop-1440'), { recursive: true });
  const consoleIssues = [];
  page.on('console', message => { if (message.type() === 'error') consoleIssues.push(message.text()); });
  page.on('pageerror', error => consoleIssues.push(error.message));

  await login(page);
  await installTheme(page);
  await page.goto('/wp-admin/tools.php?page=poultryfarmer-starter', { waitUntil: 'domcontentloaded' });
  await expect(page.locator('h1')).toHaveText('Poultry Farmer Starter Setup');
  await expect(page.locator('.notice-error')).toHaveCount(0);
  await expect(page.locator('tbody tr')).toHaveCount(26);
  await page.screenshot({ path: path.join(screenshotRoot, 'desktop-1440', 'setup-preview.png'), fullPage: true, animations: 'disabled' });
  await page.locator('input[type="submit"]').click();
  await page.waitForURL(/pf_starter_message=success/);
  await expect(page.locator('.notice-success')).toContainText('Starter setup completed');

  const firstPages = await findPages(page);
  for (const slug of pageSlugs) expect(firstPages[slug], `${slug} Page should be unique`).toHaveLength(1);
  const firstCategories = await categories(page);
  for (const slug of categorySlugs) expect(firstCategories[slug]?.id, `${slug} category should exist`).toBeTruthy();

  expect(firstPages.home[0].status).toBe('publish');
  expect(firstPages.guides[0].status).toBe('publish');
  for (const slug of pageSlugs.filter(slug => !['home', 'guides'].includes(slug))) expect(firstPages[slug][0].status, `${slug} should be reviewable as a draft`).toBe('draft');

  const ownerHomepageText = 'Owner-edited homepage introduction retained by the WordPress Page Editor.';
  const homeBefore = await reopen(page, firstPages.home[0].id);
  const homeContent = homeBefore.content.replace(
    'Poultry Farmer provides practical Kenyan guidance for researching, starting and managing a poultry farm.',
    ownerHomepageText
  );
  const homeSaved = await saveEditor(page, { content: homeContent });
  expect(homeSaved.content).toContain(ownerHomepageText);
  await validFrontend(page, '/');
  await expect(page.locator('main')).toContainText(ownerHomepageText);
  await expect(page.locator('h1')).toHaveCount(1);
  const homeReopened = await reopen(page, firstPages.home[0].id);
  expect(homeReopened.content).toContain(ownerHomepageText);

  const managementText = 'Owner-edited farm management overview.';
  const managementBefore = await reopen(page, firstPages.management[0].id);
  const managementContent = managementBefore.content.replace(
    'Build dependable daily systems',
    managementText
  );
  await saveEditor(page, { content: managementContent });
  await validFrontend(page, '/management/');
  await expect(page.locator('main')).toContainText(managementText);
  const managementReopened = await reopen(page, firstPages.management[0].id);
  expect(managementReopened.content).toContain(managementText);
  for (const slug of ['kienyeji', 'layers', 'broilers', 'hatching']) expect(firstPages[slug][0].template).toBe('poultry-hub');
  for (const slug of ['management', 'feeding', 'housing', 'health', 'records', 'prices', 'calculators']) expect(firstPages[slug][0].template).toBe('section-hub');

  const farmingId = firstPages.farming[0].id;
  const managementId = firstPages.management[0].id;
  for (const slug of ['kienyeji', 'layers', 'broilers', 'hatching']) expect(firstPages[slug][0].parent).toBe(farmingId);
  for (const slug of ['feeding', 'housing', 'health', 'records']) expect(firstPages[slug][0].parent).toBe(managementId);

  for (const slug of categorySlugs) {
    const content = firstPages[slug][0].content;
    expect(content, `${slug} content should contain a native Query Loop`).toContain('wp:query');
    expect(content, `${slug} Query Loop should use the category resolved from slug`).toContain(String(firstCategories[slug].id));
  }

  const ownerIntroduction = 'Owner-edited Kienyeji introduction retained after starter setup rerun.';
  const kienyejiBefore = await reopen(page, firstPages.kienyeji[0].id);
  const ownerContent = kienyejiBefore.content.replace('Kienyeji flocks may use indigenous or improved birds for meat, eggs or both.', ownerIntroduction);
  const kienyejiSaved = await saveEditor(page, { content: ownerContent, status: 'publish' });
  expect(kienyejiSaved.content).toContain(ownerIntroduction);

  await editorApi(page, async ids => {
    for (const id of ids) await window.wp.apiFetch({ path: `/wp/v2/pages/${id}`, method: 'POST', data: { status: 'publish' } });
  }, pageSlugs.filter(slug => slug !== 'kienyeji').map(slug => firstPages[slug][0].id));

  const posts = {};
  posts.shared = await createPost(page, {
    title: 'Starter QA Kienyeji Feeding Guide', slug: 'starter-qa-kienyeji-feeding-guide', excerpt: 'Synthetic local relationship fixture.',
    content: '<!-- wp:paragraph --><p>Synthetic local guide created only for starter setup QA.</p><!-- /wp:paragraph -->',
    categories: [firstCategories.kienyeji.id, firstCategories.feeding.id], status: 'publish',
  });
  for (const slug of ['layers', 'broilers', 'hatching']) {
    posts[slug] = await createPost(page, {
      title: `Starter QA ${slug} Guide`, slug: `starter-qa-${slug}-guide`, excerpt: 'Synthetic local relationship fixture.',
      content: '<!-- wp:paragraph --><p>Synthetic local guide created only for starter setup QA.</p><!-- /wp:paragraph -->',
      categories: [firstCategories[slug].id], status: 'publish',
    });
  }
  posts.draft = await createPost(page, {
    title: 'Starter QA Kienyeji Draft Hidden', slug: 'starter-qa-kienyeji-draft-hidden', excerpt: 'Draft fixture.',
    content: '<!-- wp:paragraph --><p>Draft fixture.</p><!-- /wp:paragraph -->', categories: [firstCategories.kienyeji.id], status: 'draft',
  });
  posts.unrelated = await createPost(page, {
    title: 'Starter QA Unrelated Post', slug: 'starter-qa-unrelated-post', excerpt: 'Unrelated fixture.',
    content: '<!-- wp:paragraph --><p>Unrelated fixture.</p><!-- /wp:paragraph -->', status: 'publish',
  });

  await page.goto('/wp-admin/tools.php?page=poultryfarmer-starter', { waitUntil: 'domcontentloaded' });
  await expect(page.locator('.notice-error')).toHaveCount(0);
  await expect(page.locator('body')).toContainText('Preserve owner content and settings');
  await page.locator('input[type="submit"]').click();
  await page.waitForURL(/pf_starter_message=success/);
  await expect(page.locator('.notice-success')).toContainText('Existing tracked content was preserved');

  const secondPages = await findPages(page);
  for (const slug of pageSlugs) expect(secondPages[slug], `${slug} must not be duplicated`).toHaveLength(1);
  const secondCategories = await categories(page);
  for (const slug of categorySlugs) expect(secondCategories[slug].id).toBe(firstCategories[slug].id);
  const kienyejiAfter = await reopen(page, firstPages.kienyeji[0].id);
  expect(kienyejiAfter.content).toContain(ownerIntroduction);

  const relationshipResults = {};
  for (const [slug, expectedTitle] of Object.entries({ kienyeji: 'Starter QA Kienyeji Feeding Guide', layers: 'Starter QA layers Guide', broilers: 'Starter QA broilers Guide', hatching: 'Starter QA hatching Guide', feeding: 'Starter QA Kienyeji Feeding Guide' })) {
    const route = ['kienyeji', 'layers', 'broilers', 'hatching'].includes(slug) ? `/farming/${slug}/` : `/management/${slug}/`;
    const text = await validFrontend(page, route);
    relationshipResults[slug] = text.includes(expectedTitle);
    expect(text).toContain(expectedTitle);
    expect(text).not.toContain('Starter QA Kienyeji Draft Hidden');
    expect(text).not.toContain('Starter QA Unrelated Post');
  }
  const healthText = await validFrontend(page, '/management/health/');
  expect(healthText).toContain('No matching management guides are published yet.');

  const settings = await reopen(page, firstPages.guides[0].id);
  const siteSettings = await page.evaluate(() => window.wp.apiFetch({ path: '/wp/v2/settings' }));
  expect(siteSettings.show_on_front).toBe('page');
  expect(siteSettings.page_on_front).toBe(firstPages.home[0].id);
  expect(siteSettings.page_for_posts).toBe(firstPages.guides[0].id);

  await page.goto('/wp-admin/options-permalink.php', { waitUntil: 'domcontentloaded' });
  expect(await page.locator('#permalink_structure').inputValue()).toBe('/guides/%postname%/');
  await validFrontend(page, `/guides/${posts.shared.slug}/`);
  await validFrontend(page, '/');
  await expect(page.locator('main h1')).toHaveText('Poultry Farming in Kenya, Made Practical.');

  const starterRoutes = ['/', '/farming/', '/farming/kienyeji/', '/farming/layers/', '/farming/broilers/', '/farming/hatching/', '/management/', '/management/feeding/', '/management/housing/', '/management/health/', '/management/records/', '/prices/', '/calculators/', '/guides/', '/start-here/', '/about/', '/editorial-policy/', '/contact/'];
  for (const route of starterRoutes) await validFrontend(page, route);

  await page.goto('/wp-admin/plugins.php', { waitUntil: 'domcontentloaded' });
  const deactivateLink = page.getByRole('link', { name: 'Deactivate Poultry Farmer Starter Setup' });
  await expect(deactivateLink).toBeVisible();
  await deactivateLink.click();
  await page.waitForLoadState('domcontentloaded');
  await expect(page.getByRole('link', { name: 'Activate Poultry Farmer Starter Setup' })).toBeVisible();

  await validFrontend(page, '/farming/kienyeji/');
  await expect(page.locator('main')).toContainText(ownerIntroduction);
  await expect(page.locator('main')).toContainText('Starter QA Kienyeji Feeding Guide');

  const diagnostics = {
    generatedAt: new Date().toISOString(), firstRunPageCount: pageSlugs.length, categoryCount: categorySlugs.length,
    pageIds: Object.fromEntries(pageSlugs.map(slug => [slug, firstPages[slug][0].id])),
    categoryIds: Object.fromEntries(categorySlugs.map(slug => [slug, firstCategories[slug].id])),
    postIds: Object.fromEntries(Object.entries(posts).map(([key, value]) => [key, value.id])),
    ownerIntroduction, relationshipResults, pluginDeactivated: true, settings: { show_on_front: siteSettings.show_on_front, page_on_front: siteSettings.page_on_front, page_for_posts: siteSettings.page_for_posts, permalink: '/guides/%postname%/' },
    consoleIssues,
  };
  await fs.writeFile(diagnosticsPath, JSON.stringify(diagnostics, null, 2));
  expect(consoleIssues).toEqual([]);
});

test('starter content renders after plugin deactivation at the configured viewport', async ({ page }, testInfo) => {
  test.setTimeout(240_000);
  try { await fs.access(diagnosticsPath); } catch { test.skip(true, 'Setup diagnostics are not available.'); }
  const outputDir = path.join(screenshotRoot, testInfo.project.name);
  await fs.mkdir(outputDir, { recursive: true });
  const routes = [['homepage', '/'], ['kienyeji-hub', '/farming/kienyeji/'], ['feeding-hub', '/management/feeding/'], ['guide', '/guides/starter-qa-kienyeji-feeding-guide/']];
  const results = [];
  for (const [name, route] of routes) {
    await validFrontend(page, route);
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    const h1Count = await page.locator('main h1:visible').count();
    const missingImages = await page.locator('img').evaluateAll(images => images.filter(image => image.complete && image.naturalWidth === 0).map(image => image.src));
    expect(overflow).toBeLessThanOrEqual(1);
    expect(h1Count).toBe(1);
    expect(missingImages).toEqual([]);
    await page.screenshot({ path: path.join(outputDir, `${name}.png`), fullPage: true, animations: 'disabled' });
    results.push({ name, route, overflow, h1Count, missingImages });
  }
  await fs.writeFile(path.join(outputDir, 'viewport-diagnostics.json'), JSON.stringify({ project: testInfo.project.name, results }, null, 2));
});
