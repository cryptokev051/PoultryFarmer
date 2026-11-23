import { test, expect } from '@playwright/test';
import fs from 'node:fs/promises';
import path from 'node:path';

const screenshotRoot = path.resolve('screenshots', 'phase2b2');
const invalidPagePattern = /maintenance|critical error|error establishing a database connection|fatal error/i;
const routes = [
  ['homepage', '/'],
  ['farming-overview', '/farming/'],
  ['kienyeji-hub', '/farming/kienyeji/'],
  ['layers-hub', '/farming/layers/'],
  ['broilers-hub', '/farming/broilers/'],
  ['hatching-hub', '/farming/hatching/'],
  ['editorial-article', '/guides/how-to-start-poultry-farming-kenya/'],
  ['guides-archive', '/guides/'],
  ['search-results', '/?s=poultry'],
  ['empty-related-state', '/guides/hatching-preparation-checklist/'],
];

function headingJumps(headings) {
  return headings.slice(1).flatMap((heading, index) => (
    heading.level > headings[index].level + 1
      ? [{ from: headings[index], to: heading }]
      : []
  ));
}

test('Phase 2B2 hubs, editorial discovery and responsive QA', async ({ page }, testInfo) => {
  test.setTimeout(testInfo.project.name === 'desktop-1440' ? 360_000 : 240_000);
  const outputDir = path.join(screenshotRoot, testInfo.project.name);
  await fs.mkdir(outputDir, { recursive: true });

  const browserIssues = [];
  const failedRequests = [];
  page.on('console', message => {
    if (['error', 'warning'].includes(message.type())) {
      browserIssues.push({ type: message.type(), text: message.text() });
    }
  });
  page.on('pageerror', error => browserIssues.push({ type: 'pageerror', text: error.message }));
  page.on('requestfailed', request => failedRequests.push({
    url: request.url(),
    error: request.failure()?.errorText || 'unknown',
  }));

  const routeResults = [];
  const discoveredHrefs = new Set();

  for (const [name, route] of routes) {
    const response = await page.goto(route, { waitUntil: 'load' });
    const bodyText = await page.locator('body').innerText();
    const status = response?.status() ?? null;
    const h1Texts = await page.locator('main h1:visible').allTextContents();
    const headings = await page.locator('main h1:visible, main h2:visible, main h3:visible').evaluateAll(nodes => nodes.map(node => ({
      level: Number(node.tagName.slice(1)),
      text: node.textContent?.trim() || '',
    })));
    const overflow = await page.evaluate(() => ({
      scrollWidth: document.documentElement.scrollWidth,
      clientWidth: document.documentElement.clientWidth,
      overflowPixels: document.documentElement.scrollWidth - document.documentElement.clientWidth,
    }));
    await page.locator('img').evaluateAll(async images => {
      images.forEach(image => { image.loading = 'eager'; });
      await Promise.all(images.map(image => (
        image.complete
          ? Promise.resolve()
          : new Promise(resolve => {
            image.addEventListener('load', resolve, { once: true });
            image.addEventListener('error', resolve, { once: true });
            setTimeout(resolve, 3000);
          })
      )));
    });
    const missingImages = await page.locator('img').evaluateAll(images => images
      .filter(image => !image.complete || image.naturalWidth === 0)
      .map(image => ({ src: image.currentSrc || image.src, alt: image.alt })));
    const valid = status === 200 && !invalidPagePattern.test(bodyText);

    for (const href of await page.locator('a[href]').evaluateAll(links => links
      .map(link => link.getAttribute('href'))
      .filter(href => href && href.startsWith('/') && !href.startsWith('/#')))) {
      discoveredHrefs.add(href);
    }

    await page.screenshot({
      path: path.join(outputDir, `${name}.png`),
      fullPage: true,
      animations: 'disabled',
    });

    routeResults.push({ name, route, status, valid, title: await page.title(), h1Texts, headings, overflow, missingImages });
    expect(valid, `${name} must be a valid WordPress application page`).toBe(true);
    expect(h1Texts.length, `${name} must have one visible main H1`).toBe(1);
    expect(headingJumps(headings), `${name} headings must not skip levels`).toEqual([]);
    expect(overflow.overflowPixels, `${name} must not overflow horizontally`).toBeLessThanOrEqual(1);
    expect(missingImages, `${name} images must load`).toEqual([]);
  }

  await page.goto('/', { waitUntil: 'load' });
  await expect(page.locator('main h1')).toHaveText('Poultry Farming in Kenya, Made Practical.');
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
  await searchInput.fill('housing');
  await searchInput.press('Enter');
  await page.waitForLoadState('load');
  const searchText = await page.locator('body').innerText();
  const searchInteraction = {
    url: page.url(),
    valid: !invalidPagePattern.test(searchText) && /search|housing/i.test(searchText),
  };

  await page.goto('/guides/how-to-start-poultry-farming-kenya/', { waitUntil: 'load' });
  const articleWidth = await page.locator('.article-content > :not(.alignwide):not(.alignfull)').first().evaluate(element => element.getBoundingClientRect().width);
  const tableOverflow = await page.locator('.wp-block-table').evaluate(element => getComputedStyle(element).overflowX);
  const articleAttribution = {
    byline: await page.locator('.article-meta').innerText(),
    authorProfileVisible: await page.locator('.article-author-profile').isVisible(),
  };

  await page.goto('/guides/hatching-preparation-checklist/', { waitUntil: 'load' });
  const emptyRelatedText = await page.locator('.related-guides-empty').innerText();

  const linkChecks = [];
  if (testInfo.project.name === 'desktop-1440') {
    for (const href of [...discoveredHrefs].sort()) {
      const response = await page.request.get(new URL(href, page.url()).toString(), { failOnStatusCode: false });
      const responseText = response.headers()['content-type']?.includes('text/html') ? await response.text() : '';
      linkChecks.push({
        href,
        status: response.status(),
        pass: response.status() < 400 && !invalidPagePattern.test(responseText),
      });
    }
  }

  const diagnostics = {
    project: testInfo.project.name,
    routeResults,
    menuInteraction,
    keyboardFocus,
    searchInteraction,
    articleWidth,
    tableOverflow,
    articleAttribution,
    emptyRelatedText,
    linkChecks,
    browserIssues,
    failedRequests,
  };
  await fs.writeFile(path.join(outputDir, 'diagnostics.json'), JSON.stringify(diagnostics, null, 2));

  expect(keyboardFocus.tag, 'Tab must move focus to an interactive element').toMatch(/^(A|BUTTON|INPUT)$/);
  expect(keyboardFocus.visibleOutline, 'keyboard focus must be visibly indicated').toBe(true);
  expect(searchInteraction.valid, 'header search must return a valid local results page').toBe(true);
  expect(articleWidth, 'article reading width must remain constrained').toBeLessThanOrEqual(800);
  expect(['auto', 'scroll'], 'article tables must be horizontally scrollable where necessary').toContain(tableOverflow);
  expect(articleAttribution.byline).toContain('Fixture Editorial Team');
  expect(articleAttribution.authorProfileVisible, 'non-empty author biography must render').toBe(true);
  expect(emptyRelatedText).toMatch(/No additional guides are published in this topic yet/i);
  expect(linkChecks.filter(check => !check.pass), 'all linked local fixture routes must resolve to valid pages').toEqual([]);
  expect(browserIssues.filter(issue => ['error', 'pageerror'].includes(issue.type)), 'no blocking browser console errors').toEqual([]);
  expect(failedRequests, 'browser requests must not fail').toEqual([]);
});
