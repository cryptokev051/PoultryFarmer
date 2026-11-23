import { test, expect } from '@playwright/test';
import fs from 'node:fs/promises';
import path from 'node:path';

const screenshotRoot = path.resolve('screenshots', 'phase2b2-editor-workflow');
const evidenceRoot = screenshotRoot;
const invalidPagePattern = /maintenance|critical error|error establishing a database connection|fatal error/i;
const qaPrefix = 'Editor QA 20261119';

const credentials = {
  username: process.env.PF_WP_USER,
  password: process.env.PF_WP_PASSWORD,
};

async function login(page) {
  expect(credentials.username, 'PF_WP_USER must contain the temporary local Playground administrator username').toBeTruthy();
  expect(credentials.password, 'PF_WP_PASSWORD must contain the temporary local Playground administrator password').toBeTruthy();
  await page.goto('/wp-login.php', { waitUntil: 'domcontentloaded' });
  await page.locator('#user_login').fill(credentials.username);
  await page.locator('#user_pass').fill(credentials.password);
  await page.locator('#wp-submit').click();
  await page.waitForURL(/\/wp-admin\/?(?:index\.php)?$/);
  await expect(page.locator('#wpadminbar')).toBeVisible();
}

async function waitForEditor(page) {
  await page.waitForFunction(() => Boolean(window.wp?.data?.select('core/editor')?.getCurrentPostType()), null, { timeout: 90_000 });
  await page.waitForFunction(() => !window.wp.data.select('core/editor').isSavingPost(), null, { timeout: 90_000 });
}

async function saveInEditor(page, attributes) {
  await waitForEditor(page);
  return page.evaluate(async attrs => {
    const dispatch = window.wp.data.dispatch('core/editor');
    const select = window.wp.data.select('core/editor');
    dispatch.editPost(attrs);
    await dispatch.savePost();
    return {
      id: select.getCurrentPostId(),
      type: select.getCurrentPostType(),
      title: select.getEditedPostAttribute('title'),
      slug: select.getEditedPostAttribute('slug'),
      status: select.getEditedPostAttribute('status'),
      excerpt: select.getEditedPostAttribute('excerpt'),
      content: select.getEditedPostContent(),
      template: select.getEditedPostAttribute('template'),
      link: select.getCurrentPostAttribute('link'),
      preview: select.getEditedPostPreviewLink?.() || null,
    };
  }, attributes);
}

async function reopenAndRead(page, postId) {
  await page.goto(`/wp-admin/post.php?post=${postId}&action=edit`, { waitUntil: 'domcontentloaded' });
  await waitForEditor(page);
  return page.evaluate(() => {
    const editor = window.wp.data.select('core/editor');
    return {
      id: editor.getCurrentPostId(),
      type: editor.getCurrentPostType(),
      title: editor.getEditedPostAttribute('title'),
      slug: editor.getEditedPostAttribute('slug'),
      status: editor.getEditedPostAttribute('status'),
      excerpt: editor.getEditedPostAttribute('excerpt'),
      content: editor.getEditedPostContent(),
      template: editor.getEditedPostAttribute('template'),
      categories: editor.getEditedPostAttribute('categories') || [],
      featuredMedia: editor.getEditedPostAttribute('featured_media') || 0,
      availableTemplates: editor.getEditorSettings()?.availableTemplates || [],
    };
  });
}

async function createEditorPost(page, postType, attributes) {
  const suffix = postType === 'page' ? 'page' : 'post';
  await page.goto(`/wp-admin/post-new.php?post_type=${suffix}`, { waitUntil: 'domcontentloaded' });
  await waitForEditor(page);
  if (attributes.slug) {
    const existing = await page.evaluate(async ({ type, slug }) => {
      const endpoint = type === 'page' ? 'pages' : 'posts';
      return window.wp.apiFetch({ path: `/wp/v2/${endpoint}?slug=${encodeURIComponent(slug)}&status=any&context=edit&per_page=1` });
    }, { type: postType, slug: attributes.slug });
    if (existing[0]?.id) {
      await page.goto(`/wp-admin/post.php?post=${existing[0].id}&action=edit`, { waitUntil: 'domcontentloaded' });
    }
  }
  return saveInEditor(page, attributes);
}

async function apiNonce(page) {
  return page.evaluate(() => window.wpApiSettings?.nonce || window.wp?.data?.select('core')?.getEntityConfig?.('root', 'site')?._links?.['wp:action-unfiltered-html']?.[0]?.href || '');
}

async function getJson(page, route) {
  const response = await page.request.get(route, { failOnStatusCode: false });
  expect(response.ok(), `${route} should return an authenticated success response`).toBe(true);
  return response.json();
}

async function validFrontend(page, route) {
  const response = await page.goto(route, { waitUntil: 'load' });
  const text = await page.locator('body').innerText();
  expect(response?.status(), `${route} should return HTTP 200`).toBe(200);
  expect(invalidPagePattern.test(text), `${route} should not render a maintenance or fatal-error page`).toBe(false);
  return text;
}

function queryLoop(categoryId, emptyText = 'No matching synthetic guides are published.') {
  const attrs = JSON.stringify({
    queryId: 911,
    query: { perPage: 3, pages: 0, offset: 0, postType: 'post', order: 'desc', orderBy: 'date', taxQuery: { category: [categoryId] }, inherit: false },
  });
  return `<!-- wp:heading --><h2 class="wp-block-heading">Related synthetic guides</h2><!-- /wp:heading -->
<!-- wp:query ${attrs} --><div class="wp-block-query"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} --><!-- wp:group {"className":"poultryfarmer-card","layout":{"type":"constrained"}} --><div class="wp-block-group poultryfarmer-card"><!-- wp:post-title {"isLink":true,"level":3} /--><!-- wp:post-excerpt /--></div><!-- /wp:group --><!-- /wp:post-template --><!-- wp:query-no-results --><!-- wp:paragraph --><p>${emptyText}</p><!-- /wp:paragraph --><!-- /wp:query-no-results --></div><!-- /wp:query -->`;
}

test.describe.configure({ mode: 'serial' });

test('authenticated editor workflow creates, saves, reopens and publishes synthetic content', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1440', 'Content mutation runs once; responsive projects consume its saved content.');
  test.setTimeout(600_000);
  await fs.mkdir(evidenceRoot, { recursive: true });
  await fs.mkdir(path.join(screenshotRoot, 'desktop-1440'), { recursive: true });

  const consoleIssues = [];
  const requestFailures = [];
  page.on('console', message => {
    if (['error', 'warning'].includes(message.type())) consoleIssues.push({ type: message.type(), text: message.text() });
  });
  page.on('pageerror', error => consoleIssues.push({ type: 'pageerror', text: error.message }));
  page.on('requestfailed', request => requestFailures.push({ url: request.url(), error: request.failure()?.errorText || 'unknown' }));

  await login(page);
  const dashboard = { url: page.url(), title: await page.title(), heading: await page.locator('h1').innerText() };

  await page.goto('/wp-admin/site-editor.php', { waitUntil: 'domcontentloaded' });
  await expect(page.locator('body')).toContainText('Design');
  await expect(page.locator('body')).toContainText('Templates');
  const siteEditor = { url: page.url(), title: await page.title(), valid: true };

  await page.goto('/wp-admin/post-new.php?post_type=page', { waitUntil: 'domcontentloaded' });
  await waitForEditor(page);
  const templates = await page.evaluate(() => window.wp.data.select('core/editor').getEditorSettings()?.availableTemplates || []);
  const templateValues = Array.isArray(templates) ? templates.map(item => item.value || item.slug || item) : Object.keys(templates);
  expect(templateValues).toContain('poultry-hub');
  expect(templateValues).toContain('section-hub');

  const categories = await getJson(page, '/wp-json/wp/v2/categories?per_page=100');
  const categoryIds = Object.fromEntries(categories.map(category => [category.name, category.id]));
  for (const name of ['Kienyeji', 'Layers', 'Broilers', 'Hatching', 'Uncategorized']) expect(categoryIds[name], `${name} category should exist`).toBeTruthy();

  const nonce = await apiNonce(page);
  expect(nonce, 'authenticated editor should expose a REST nonce').toBeTruthy();
  const emptyTermResponse = await page.request.post('/wp-json/wp/v2/categories', {
    headers: { 'X-WP-Nonce': nonce },
    data: { name: `${qaPrefix} Empty`, slug: 'editor-qa-empty' },
    failOnStatusCode: false,
  });
  let emptyCategory;
  if (emptyTermResponse.status() === 400) {
    emptyCategory = (await getJson(page, '/wp-json/wp/v2/categories?slug=editor-qa-empty'))[0];
  } else {
    expect(emptyTermResponse.ok(), 'empty-state category should be created locally').toBe(true);
    emptyCategory = await emptyTermResponse.json();
  }

  const hubContent = `<!-- wp:paragraph --><p><strong>Synthetic editor fixture:</strong> This draft verifies that hub content remains editable in WordPress.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2 class="wp-block-heading">Production model overview</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Editors can replace this local introduction without editing theme source.</p><!-- /wp:paragraph -->
${queryLoop(categoryIds.Kienyeji)}`;
  const hub = await createEditorPost(page, 'page', {
    title: `${qaPrefix} Poultry Hub Draft`, slug: 'editor-qa-poultry-hub', excerpt: 'Synthetic local excerpt for editor persistence testing.', content: hubContent, template: 'poultry-hub', status: 'draft',
  });
  expect(hub.id).toBeTruthy();
  const reopenedHub = await reopenAndRead(page, hub.id);
  expect(reopenedHub.template).toBe('poultry-hub');
  expect(reopenedHub.excerpt).toContain('Synthetic local excerpt');
  expect(reopenedHub.content).toContain('wp:query');
  expect(reopenedHub.content).toContain('Production model overview');
  const hubPreview = await page.evaluate(() => window.wp.data.select('core/editor').getEditedPostPreviewLink());
  await validFrontend(page, hubPreview);
  await expect(page.locator('.site-header-wrapper')).toBeVisible();
  await expect(page.locator('.site-footer')).toBeVisible();
  await expect(page.locator('main h1')).toHaveText(`${qaPrefix} Poultry Hub Draft`);
  await page.screenshot({ path: path.join(screenshotRoot, 'desktop-1440', 'poultry-hub-draft-preview.png'), fullPage: true, animations: 'disabled' });

  const section = await createEditorPost(page, 'page', {
    title: `${qaPrefix} Section Hub Draft`, slug: 'editor-qa-section-hub', excerpt: 'Synthetic section-hub excerpt.', content: '<!-- wp:heading --><h2 class="wp-block-heading">Editable section content</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Fixture-only copy for an authenticated editor check.</p><!-- /wp:paragraph -->', template: 'section-hub', status: 'draft',
  });
  const reopenedSection = await reopenAndRead(page, section.id);
  expect(reopenedSection.template).toBe('section-hub');
  expect(reopenedSection.content).toContain('Editable section content');
  const sectionPreview = await page.evaluate(() => window.wp.data.select('core/editor').getEditedPostPreviewLink());
  await validFrontend(page, sectionPreview);
  await expect(page.locator('main h1')).toHaveText(`${qaPrefix} Section Hub Draft`);

  const guidePosts = {};
  for (const name of ['Kienyeji', 'Layers', 'Broilers', 'Hatching']) {
    const saved = await createEditorPost(page, 'post', {
      title: `${qaPrefix} ${name} Guide`, slug: `editor-qa-${name.toLowerCase()}-guide`, excerpt: `Synthetic ${name} excerpt for category relationship testing.`, content: `<!-- wp:paragraph --><p><strong>Synthetic editor fixture:</strong> ${name} relationship content.</p><!-- /wp:paragraph -->`, categories: [categoryIds[name]], status: 'publish',
    });
    expect(saved.status).toBe('publish');
    guidePosts[name] = saved;
  }

  const draftGuide = await createEditorPost(page, 'post', {
    title: `${qaPrefix} Kienyeji Draft Hidden`, slug: 'editor-qa-kienyeji-draft-hidden', excerpt: 'This draft must not appear publicly.', content: '<!-- wp:paragraph --><p>Draft relationship fixture.</p><!-- /wp:paragraph -->', categories: [categoryIds.Kienyeji], status: 'draft',
  });
  const uncategorized = await createEditorPost(page, 'post', {
    title: `${qaPrefix} Uncategorized Only`, slug: 'editor-qa-uncategorized-only', excerpt: 'This post must not become a topic-related guide.', content: '<!-- wp:paragraph --><p>Uncategorized-only relationship fixture.</p><!-- /wp:paragraph -->', categories: [categoryIds.Uncategorized], status: 'publish',
  });

  const emptyHub = await createEditorPost(page, 'page', {
    title: `${qaPrefix} Empty Query Draft`, slug: 'editor-qa-empty-query', excerpt: 'Synthetic empty-query fixture.', content: queryLoop(emptyCategory.id, 'Editor QA intended empty state.'), template: 'poultry-hub', status: 'draft',
  });
  const reopenedEmptyHub = await reopenAndRead(page, emptyHub.id);
  expect(reopenedEmptyHub.content).toContain(String(emptyCategory.id));
  const emptyPreview = await page.evaluate(() => window.wp.data.select('core/editor').getEditedPostPreviewLink());
  await validFrontend(page, emptyPreview);
  await expect(page.locator('main')).toContainText('Editor QA intended empty state.');

  const patternResponse = await page.request.get('/wp-json/wp/v2/block-patterns/patterns?context=edit', {
    headers: { 'X-WP-Nonce': nonce }, failOnStatusCode: false,
  });
  expect(patternResponse.ok(), 'authenticated pattern endpoint should be available').toBe(true);
  const allPatterns = await patternResponse.json();
  const poultryPatterns = allPatterns.filter(pattern => pattern.name?.startsWith('poultryfarmer/'));
  expect(poultryPatterns).toHaveLength(21);
  const requiredPatterns = [
    'poultryfarmer/farming-considerations', 'poultryfarmer/cost-breakdown', 'poultryfarmer/practical-checklist',
    'poultryfarmer/common-mistakes', 'poultryfarmer/example-calculation', 'poultryfarmer/sources-methodology',
  ];
  for (const name of requiredPatterns) expect(poultryPatterns.some(pattern => pattern.name === name), `${name} should be registered`).toBe(true);
  const insertedPatternContent = requiredPatterns.map(name => poultryPatterns.find(pattern => pattern.name === name).content).join('\n')
    .replaceAll('Explain the flock, production cycle and management approach this guide covers.', 'Replacement text saved through the authenticated block editor.')
    .replaceAll('Add the source title, publisher, data period and link.', 'Synthetic source: Poultry Farmer local editor workflow, 19 November 2026.');
  const patternPost = await createEditorPost(page, 'post', {
    title: `${qaPrefix} Pattern Draft`, slug: 'editor-qa-pattern-draft', excerpt: 'Synthetic pattern insertion and editing fixture.', content: insertedPatternContent, categories: [categoryIds.Records || categoryIds.Kienyeji], status: 'draft',
  });
  const reopenedPatternPost = await reopenAndRead(page, patternPost.id);
  expect(reopenedPatternPost.content).toContain('Replacement text saved through the authenticated block editor.');
  expect(reopenedPatternPost.content).toContain('Synthetic source: Poultry Farmer local editor workflow');
  for (const marker of ['farming-considerations', 'cost-breakdown', 'practical-checklist', 'common-mistakes', 'example-calculation', 'sources-methodology']) {
    expect(reopenedPatternPost.content, `${marker} blocks should persist`).toContain(marker);
  }
  const patternPreview = await page.evaluate(() => window.wp.data.select('core/editor').getEditedPostPreviewLink());
  await validFrontend(page, patternPreview);
  await expect(page.locator('main')).toContainText('Replacement text saved through the authenticated block editor.');

  const mediaPath = path.resolve('..', 'theme', 'poultryfarmer', 'assets', 'images', 'brand', '03_Emblem_and_Wordmark', 'Emblem_FullColor_Transparent.png');
  const mediaBuffer = await fs.readFile(mediaPath);
  const mediaResponse = await page.request.post('/wp-json/wp/v2/media', {
    headers: { 'X-WP-Nonce': nonce, 'Content-Disposition': 'attachment; filename="editor-qa-poultry-farmer-emblem.png"', 'Content-Type': 'image/png' },
    data: mediaBuffer, failOnStatusCode: false,
  });
  expect(mediaResponse.ok(), 'local approved logo artwork should upload as a fixture featured image').toBe(true);
  const media = await mediaResponse.json();
  const guideContent = `<!-- wp:paragraph {"backgroundColor":"brand-soft"} --><p class="has-brand-soft-background-color has-background"><strong>Synthetic local fixture:</strong> This guide tests the authenticated publishing workflow only.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2 class="wp-block-heading">Compare planning questions</h2><!-- /wp:heading -->
<!-- wp:table --><figure class="wp-block-table"><table><thead><tr><th>Question</th><th>Local fixture answer</th><th>Evidence status</th></tr></thead><tbody><tr><th scope="row">Housing</th><td>Confirm the selected system.</td><td>Synthetic workflow copy</td></tr><tr><th scope="row">Feeding</th><td>Document assumptions before use.</td><td>No market claim</td></tr></tbody></table><figcaption>Responsive comparison-table fixture; not farming advice.</figcaption></figure><!-- /wp:table -->
${poultryPatterns.find(pattern => pattern.name === 'poultryfarmer/sources-methodology').content.replaceAll('Add the source title, publisher, data period and link.', 'Synthetic source: local authenticated editor test dated 19 November 2026.')}`;
  const publishedGuide = await createEditorPost(page, 'post', {
    title: `${qaPrefix} Published Workflow Guide`, slug: 'editor-qa-published-workflow-guide', excerpt: 'Synthetic published guide used only to verify the complete editorial workflow.', content: guideContent, categories: [categoryIds.Kienyeji], featured_media: media.id, status: 'publish',
  });
  expect(publishedGuide.status).toBe('publish');
  await page.waitForTimeout(2200);
  const reopenedPublished = await reopenAndRead(page, publishedGuide.id);
  expect(reopenedPublished.featuredMedia).toBe(media.id);
  expect(reopenedPublished.categories).toContain(categoryIds.Kienyeji);
  const updatedContent = reopenedPublished.content.replace('Compare planning questions', 'Compare planning questions after editorial update');
  const updatedGuide = await saveInEditor(page, { content: updatedContent });
  expect(updatedGuide.content).toContain('after editorial update');
  const reopenedUpdated = await reopenAndRead(page, publishedGuide.id);
  expect(reopenedUpdated.content).toContain('after editorial update');

  await page.goto('/wp-admin/options-reading.php', { waitUntil: 'domcontentloaded' });
  const readingSettings = {
    showOnFront: await page.locator('input[name="show_on_front"]:checked').inputValue(),
    frontPage: await page.locator('#page_on_front').inputValue(),
    postsPage: await page.locator('#page_for_posts').inputValue(),
  };
  expect(readingSettings.showOnFront).toBe('page');
  expect(Number(readingSettings.frontPage)).toBeGreaterThan(0);
  expect(Number(readingSettings.postsPage)).toBeGreaterThan(0);
  await page.locator('#submit').click();
  await expect(page.locator('#setting-error-settings_updated')).toContainText('Settings saved');

  await page.goto('/wp-admin/options-permalink.php', { waitUntil: 'domcontentloaded' });
  const permalinkStructure = await page.locator('#permalink_structure').inputValue();
  expect(permalinkStructure).toBe('/guides/%postname%/');
  await page.locator('#submit').click();
  await expect(page.locator('#setting-error-settings_updated')).toContainText('Permalink structure updated');

  const relationshipChecks = {};
  for (const [name, route] of Object.entries({ Kienyeji: '/farming/kienyeji/', Layers: '/farming/layers/', Broilers: '/farming/broilers/', Hatching: '/farming/hatching/' })) {
    const text = await validFrontend(page, route);
    relationshipChecks[name] = {
      includesMatching: text.includes(`${qaPrefix} ${name} Guide`),
      excludesOtherCategories: ['Kienyeji', 'Layers', 'Broilers', 'Hatching'].filter(other => other !== name).every(other => !text.includes(`${qaPrefix} ${other} Guide`)),
      excludesDraft: !text.includes(`${qaPrefix} Kienyeji Draft Hidden`),
      excludesUncategorized: !text.includes(`${qaPrefix} Uncategorized Only`),
    };
    expect(relationshipChecks[name]).toEqual({ includesMatching: true, excludesOtherCategories: true, excludesDraft: true, excludesUncategorized: true });
  }

  const guideUrl = `/guides/${publishedGuide.slug}/`;
  await validFrontend(page, guideUrl);
  await expect(page.locator('main h1')).toHaveText(`${qaPrefix} Published Workflow Guide`);
  await expect(page.locator('.article-meta')).toContainText('Fixture Editorial Team');
  await expect(page.locator('.article-updated-meta')).toContainText('Updated');
  await expect(page.locator('.article-author-profile')).toBeVisible();
  await expect(page.locator('.article-featured-image img')).toBeVisible();
  await expect(page.locator('.wp-block-table')).toBeVisible();
  await expect(page.locator('.related-analysis-results')).toContainText(`${qaPrefix} Kienyeji Guide`);
  await expect(page.locator('nav[aria-label="Breadcrumb"]')).toContainText('Guides');
  const postNavigationText = await page.locator('.wp-block-post-navigation-link').allTextContents();
  expect(postNavigationText.some(text => /Previous guide|Next guide/.test(text))).toBe(true);
  expect(await page.locator('body').innerText()).not.toMatch(/reviewed by/i);
  await page.screenshot({ path: path.join(screenshotRoot, 'desktop-1440', 'published-workflow-guide.png'), fullPage: true, animations: 'disabled' });

  await validFrontend(page, '/guides/');
  await expect(page.locator('main')).toContainText(`${qaPrefix} Published Workflow Guide`);
  await validFrontend(page, '/guides/page/2/');
  const searchText = await validFrontend(page, `/?s=${encodeURIComponent(qaPrefix + ' Published')}`);
  expect(searchText).toContain(`${qaPrefix} Published Workflow Guide`);
  const emptySearchText = await validFrontend(page, '/?s=editor-qa-no-such-result-zz99');
  expect(emptySearchText).toMatch(/no results|no matching guides|nothing found|couldn.t find/i);
  const permalinkText = await validFrontend(page, guideUrl);
  expect(permalinkText).toContain(`${qaPrefix} Published Workflow Guide`);

  const diagnostics = {
    generatedAt: new Date().toISOString(), dashboard, siteEditor, templates: templateValues, patternCount: poultryPatterns.length,
    patternNames: poultryPatterns.map(pattern => pattern.name).sort(), categoryIds, emptyCategoryId: emptyCategory.id,
    content: {
      hub: { id: hub.id, title: hub.title, template: reopenedHub.template },
      section: { id: section.id, title: section.title, template: reopenedSection.template },
      guidePosts: Object.fromEntries(Object.entries(guidePosts).map(([name, value]) => [name, { id: value.id, slug: value.slug, status: value.status }])),
      draftGuide: { id: draftGuide.id, slug: draftGuide.slug, status: draftGuide.status }, uncategorized: { id: uncategorized.id, slug: uncategorized.slug },
      emptyHub: { id: emptyHub.id }, patternPost: { id: patternPost.id },
      publishedGuide: { id: publishedGuide.id, slug: publishedGuide.slug, url: guideUrl, mediaId: media.id },
    }, readingSettings, permalinkStructure, relationshipChecks, consoleIssues, requestFailures,
  };
  await fs.writeFile(path.join(evidenceRoot, 'workflow-diagnostics.json'), JSON.stringify(diagnostics, null, 2));
  expect(consoleIssues.filter(issue => ['error', 'pageerror'].includes(issue.type)), 'no blocking editor or frontend browser errors').toEqual([]);
  const blockingRequestFailures = requestFailures.filter(failure => failure.error !== 'net::ERR_ABORTED' && !failure.url.startsWith('blob:'));
  expect(blockingRequestFailures, 'authenticated workflow browser requests should not have non-navigation failures').toEqual([]);
});

test('edited guide is responsive at the configured viewport', async ({ page }, testInfo) => {
  test.setTimeout(240_000);
  const diagnosticsPath = path.join(evidenceRoot, 'workflow-diagnostics.json');
  try {
    await fs.access(diagnosticsPath);
  } catch {
    test.skip(true, 'The mutation pass did not produce diagnostics.');
  }
  const diagnostics = JSON.parse(await fs.readFile(diagnosticsPath, 'utf8'));
  const outputDir = path.join(screenshotRoot, testInfo.project.name);
  await fs.mkdir(outputDir, { recursive: true });
  const browserIssues = [];
  page.on('console', message => { if (message.type() === 'error') browserIssues.push(message.text()); });
  page.on('pageerror', error => browserIssues.push(error.message));
  await validFrontend(page, diagnostics.content.publishedGuide.url);
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
  const tableOverflow = await page.locator('.wp-block-table').evaluate(element => getComputedStyle(element).overflowX);
  const headings = await page.locator('main h1, main h2, main h3').evaluateAll(nodes => nodes.map(node => ({ level: Number(node.tagName.slice(1)), text: node.textContent.trim() })));
  expect(await page.locator('main h1').count()).toBe(1);
  expect(overflow).toBeLessThanOrEqual(1);
  expect(['auto', 'scroll']).toContain(tableOverflow);
  expect(browserIssues).toEqual([]);
  await page.screenshot({ path: path.join(outputDir, 'published-workflow-guide.png'), fullPage: true, animations: 'disabled' });
  await fs.writeFile(path.join(outputDir, 'diagnostics.json'), JSON.stringify({ project: testInfo.project.name, overflow, tableOverflow, headings, browserIssues }, null, 2));
});
