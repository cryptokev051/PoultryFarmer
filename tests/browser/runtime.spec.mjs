import { test, expect } from '@playwright/test';
import fs from 'node:fs/promises';
import path from 'node:path';

const slugs = [
  ['homepage', '/'],
  ['article', '/local-sample-editorial-article/'],
  ['kienyeji-farming', '/kienyeji-farming/'],
  ['shop-archive', '/shop/'],
  ['single-product', '/product/local-sample-virtual-download/'],
  ['cart', '/cart/'],
  ['checkout', '/checkout/'],
  ['account', '/my-account/'],
  ['search', '/?s=local'],
  ['404', '/this-route-does-not-exist/'],
];

const redact = (value = '') => value.replace(/(cookie|authorization|nonce|token|session|wp_woocommerce_session)[^\r\n]*/gi, '$1=[REDACTED]');

test('Poultry Farmer runtime screenshots and interaction diagnostics', async ({ page }, testInfo) => {
  const errors = [];
  const requests = [];
  const responses = [];
  page.on('console', msg => { if (msg.type() === 'error' || msg.type() === 'warning') errors.push({ type: msg.type(), text: msg.text() }); });
  page.on('pageerror', error => errors.push({ type: 'pageerror', text: error.message }));
  page.on('request', request => { if (/cart|add-to-cart|wc-ajax|store\/v1/i.test(request.url())) requests.push({ method: request.method(), url: redact(request.url()), headers: redact(JSON.stringify(request.headers())) }); });
  page.on('response', async response => { if (/cart|add-to-cart|wc-ajax|store\/v1/i.test(response.url())) responses.push({ status: response.status(), url: redact(response.url()), headers: redact(JSON.stringify(response.headers())) }); });

  const screenshotDir = path.resolve('screenshots', testInfo.project.name);
  await fs.mkdir(screenshotDir, { recursive: true });
  const results = [];
  for (const [name, url] of slugs) {
    const response = await page.goto(url, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(400);
    await page.screenshot({ path: path.join(screenshotDir, `${name}.png`), fullPage: false, animations: 'disabled' });
    results.push({ name, url, status: response?.status() ?? null, title: await page.title(), bodyText: (await page.locator('body').innerText()).slice(0, 300) });
  }

  await page.goto('/');
  const navButtons = page.locator('button, [role="button"]');
  const navCount = await navButtons.count();
  let navInteraction = 'NOT TESTED';
  for (let i = 0; i < navCount; i++) {
    const label = (await navButtons.nth(i).getAttribute('aria-label')) || (await navButtons.nth(i).innerText().catch(() => ''));
    if (/menu|navigation/i.test(label)) {
      await navButtons.nth(i).click();
      navInteraction = 'PASS';
      break;
    }
  }
  const overflow = await page.evaluate(() => ({ scrollWidth: document.documentElement.scrollWidth, clientWidth: document.documentElement.clientWidth }));

  let product = null;
  let storeCartBefore = null;
  let storeCartAfter = null;
  try {
    product = await page.evaluate(async () => (await (await fetch('/wp-json/wc/store/v1/products?search=Local%20Sample')).json())[0] || null);
    storeCartBefore = await page.evaluate(async () => (await (await fetch('/wp-json/wc/store/v1/cart')).json()));
    if (product?.id) {
      const add = await page.evaluate(async id => {
        const response = await fetch(`/wp-json/wc/store/v1/cart/add-item?id=${id}&quantity=1`, { method: 'POST', credentials: 'include' });
        return { status: response.status, headers: Object.fromEntries(response.headers.entries()), body: await response.text() };
      }, product.id);
      storeCartAfter = { add: { status: add.status, headers: redact(JSON.stringify(add.headers)), body: redact(add.body.slice(0, 1500)) }, cart: await page.evaluate(async () => (await (await fetch('/wp-json/wc/store/v1/cart')).json())) };
    }
  } catch (error) {
    storeCartAfter = { error: error.message };
  }

  const classic = product?.id ? await page.goto(`/?add-to-cart=${product.id}`, { waitUntil: 'domcontentloaded' }) : null;
  await page.goto('/cart/', { waitUntil: 'domcontentloaded' });
  const cartText = await page.locator('body').innerText();
  await fs.writeFile(path.join(screenshotDir, 'diagnostics.json'), JSON.stringify({ project: testInfo.project.name, results, navInteraction, overflow, product, storeCartBefore, storeCartAfter, classicStatus: classic?.status() ?? null, cartContainsProduct: /Local Sample Virtual Download/i.test(cartText), errors, requests, responses }, null, 2));

  expect(results.length).toBe(slugs.length);
});
