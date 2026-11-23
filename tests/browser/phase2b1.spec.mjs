import { test, expect } from '@playwright/test';
import fs from 'node:fs/promises';
import path from 'node:path';

const captureSet = process.env.PF_CAPTURE_SET || 'approved';
const screenshotRoot = path.resolve('screenshots', 'phase2b1', captureSet);
const routes = [
  ['homepage', '/'],
  ['article', '/guides/how-to-start-poultry-farming-kenya/'],
  ['farming-hub', '/farming/'],
];

const invalidPagePattern = /maintenance|critical error|error establishing a database connection|fatal error/i;
const approvedH1 = 'Poultry Farming in Kenya, Made Practical.';
const primaryKeywordPattern = /poultry farming in kenya/gi;

test('Phase 2B1 visual and interaction QA', async ({ page }, testInfo) => {
  const outputDir = path.join(screenshotRoot, testInfo.project.name);
  await fs.mkdir(outputDir, { recursive: true });

  const consoleIssues = [];
  page.on('console', message => {
    if (['error', 'warning'].includes(message.type())) {
      consoleIssues.push({ type: message.type(), text: message.text() });
    }
  });
  page.on('pageerror', error => consoleIssues.push({ type: 'pageerror', text: error.message }));

  const results = [];
  const failedRequests = [];
  page.on('requestfailed', request => failedRequests.push({ url: request.url(), error: request.failure()?.errorText || 'unknown' }));

  for (const [name, route] of routes) {
    const response = await page.goto(route, { waitUntil: 'networkidle' });
    const bodyText = await page.locator('body').innerText();
    const status = response?.status() ?? null;
    const valid = status === 200 && !invalidPagePattern.test(bodyText);

    await page.screenshot({
      path: path.join(outputDir, `${name}.png`),
      fullPage: true,
      animations: 'disabled',
    });

    results.push({ name, route, status, valid, title: await page.title() });
    expect(valid, `${name} must be a valid application page`).toBe(true);
  }

  await page.goto('/', { waitUntil: 'networkidle' });
  const visibleH1 = page.locator('main h1:visible');
  const h1Texts = await visibleH1.allTextContents();
  const headings = await page.locator('main h1:visible, main h2:visible, main h3:visible').evaluateAll(nodes => nodes.map(node => ({
    level: Number(node.tagName.slice(1)),
    text: node.textContent?.trim() || '',
  })));
  const headingJumps = headings.slice(1).flatMap((heading, index) => (
    heading.level > headings[index].level + 1
      ? [{ from: headings[index], to: heading }]
      : []
  ));
  const homepageText = await page.locator('main').innerText();
  const editorialText = await page.locator('main p:visible, main li:visible').allTextContents();
  const editorialWordCount = editorialText.join(' ').trim().split(/\s+/).filter(Boolean).length;
  const seoSemantics = {
    visibleH1Count: h1Texts.length,
    h1Texts,
    approvedH1,
    primaryKeywordOccurrences: (homepageText.match(primaryKeywordPattern) || []).length,
    editorialWordCount,
    headings,
    headingJumps,
  };

  expect(h1Texts, 'homepage must contain one approved visible H1').toEqual([approvedH1]);
  expect(seoSemantics.primaryKeywordOccurrences, 'primary keyword must appear naturally in visible content').toBeGreaterThanOrEqual(2);
  expect(headingJumps, 'homepage heading levels must not skip').toEqual([]);

  const header = page.locator('.site-header-wrapper');
  const footer = page.locator('.site-footer');
  await expect(header).toBeVisible();
  await expect(footer).toBeVisible();
  await header.screenshot({ path: path.join(outputDir, 'header.png'), animations: 'disabled' });
  await footer.screenshot({
    path: path.join(outputDir, 'footer.png'),
    animations: 'disabled',
    style: '.site-header-wrapper { display: none !important; }',
  });

  const logo = await page.locator('.site-brand-logo img').evaluate(image => ({
    complete: image.complete,
    naturalWidth: image.naturalWidth,
    naturalHeight: image.naturalHeight,
    renderedWidth: image.getBoundingClientRect().width,
    renderedHeight: image.getBoundingClientRect().height,
  }));

  const missingImages = await page.locator('img').evaluateAll(images => images
    .filter(image => !image.complete || image.naturalWidth === 0)
    .map(image => ({ src: image.currentSrc || image.src, alt: image.alt })));

  const overflow = await page.evaluate(() => ({
    scrollWidth: document.documentElement.scrollWidth,
    clientWidth: document.documentElement.clientWidth,
    overflowPixels: document.documentElement.scrollWidth - document.documentElement.clientWidth,
  }));

  let menuInteraction = 'DESKTOP_NOT_APPLICABLE';
  const menuButton = page.locator('.wp-block-navigation__responsive-container-open');
  if (await menuButton.isVisible()) {
    await menuButton.click();
    await expect(page.locator('.wp-block-navigation__responsive-container.is-menu-open')).toBeVisible();
    await page.locator('.wp-block-navigation__responsive-container-close').click();
    await expect(page.locator('.wp-block-navigation__responsive-container.is-menu-open')).toHaveCount(0);
    menuInteraction = 'PASS';
  }
  if (testInfo.project.name !== 'desktop-1440') {
    expect(menuInteraction, 'tablet and mobile menu must open and close').toBe('PASS');
  }

  await page.keyboard.press('Tab');
  const keyboardFocus = await page.evaluate(() => ({
    tag: document.activeElement?.tagName || null,
    text: document.activeElement?.textContent?.trim().slice(0, 80) || null,
    ariaLabel: document.activeElement?.getAttribute('aria-label') || null,
    visibleOutline: getComputedStyle(document.activeElement).outlineStyle !== 'none'
      || getComputedStyle(document.activeElement).boxShadow !== 'none',
  }));

  const searchInput = page.locator('.site-header-search input[type="search"]');
  await searchInput.fill('local');
  await searchInput.press('Enter');
  await page.waitForLoadState('networkidle');
  const searchText = await page.locator('body').innerText();
  const searchInteraction = {
    url: page.url(),
    valid: !invalidPagePattern.test(searchText) && /search|local sample/i.test(searchText),
  };

  await page.goto('/', { waitUntil: 'networkidle' });
  const internalHrefs = await page.locator('main a[href], footer a[href], header a[href]').evaluateAll(links => [
    ...new Set(links.map(link => link.getAttribute('href')).filter(href => href && href.startsWith('/') && !href.startsWith('/#'))),
  ]);
  const linkChecks = [];
  for (const href of internalHrefs) {
    const response = await page.request.get(new URL(href, page.url()).toString(), { failOnStatusCode: false });
    linkChecks.push({ href, status: response.status(), pass: response.status() < 400 });
  }

  await fs.writeFile(
    path.join(outputDir, 'diagnostics.json'),
    JSON.stringify({
      captureSet,
      project: testInfo.project.name,
      results,
      seoSemantics,
      logo,
      missingImages,
      overflow,
      menuInteraction,
      keyboardFocus,
      searchInteraction,
      linkChecks,
      consoleIssues,
      failedRequests,
    }, null, 2),
  );

  expect(logo.complete && logo.naturalWidth > 0, 'approved logo must load').toBe(true);
  expect(missingImages, 'all images must load').toEqual([]);
  expect(overflow.overflowPixels, 'page must not overflow horizontally').toBeLessThanOrEqual(1);
  expect(keyboardFocus.tag, 'Tab must move focus to an interactive element').toMatch(/^(A|BUTTON|INPUT)$/);
  expect(keyboardFocus.visibleOutline, 'keyboard focus must be visibly indicated').toBe(true);
  expect(searchInteraction.valid, 'search must return a valid local results page').toBe(true);
  expect(linkChecks.filter(check => !check.pass), 'local fixture links must resolve').toEqual([]);
  expect(consoleIssues.filter(issue => ['error', 'pageerror'].includes(issue.type)), 'no blocking browser console errors').toEqual([]);
  expect(failedRequests, 'browser requests must not fail').toEqual([]);
});
