import { test, expect } from '@playwright/test';
import path from 'node:path';

const productPath = '/product/poultry-farmer-local-synthetic-download/';

test('reusable digital product page works at the target viewport', async ({ page }, testInfo) => {
  const consoleErrors = [];
  page.on('console', message => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
  page.on('pageerror', error => consoleErrors.push(error.message));

  await page.goto(productPath, { waitUntil: 'networkidle' });
  await expect(page.locator('main.poultryfarmer-single-product')).toBeVisible();
  await expect(page.locator('h1.product_title')).toHaveCount(1);
  await expect(page.locator('.single_add_to_cart_button')).toBeVisible();
  await expect(page.locator('.poultryfarmer-product-delivery')).toBeVisible();

  const layout = await page.locator('div.product').evaluate(element => {
    const rect = element.getBoundingClientRect();
    const images = element.querySelector('div.images')?.getBoundingClientRect();
    const summary = element.querySelector('div.summary')?.getBoundingClientRect();
    return {
      overflow: document.documentElement.scrollWidth - window.innerWidth,
      productWidth: rect.width,
      imagesWidth: images?.width ?? 0,
      summaryWidth: summary?.width ?? 0,
      display: getComputedStyle(element).display,
    };
  });

  expect(layout.overflow).toBeLessThanOrEqual(1);
  expect(layout.imagesWidth).toBeGreaterThan(0);
  expect(layout.summaryWidth).toBeGreaterThan(0);
  if (testInfo.project.name === 'desktop-1440') {
    expect(layout.display).toBe('grid');
  }

  const addToCart = page.locator('.single_add_to_cart_button');
  await addToCart.focus();
  await expect(addToCart).toBeFocused();
  await page.screenshot({
    path: path.join('artifacts/product-page', testInfo.project.name, 'product.png'),
    fullPage: true,
  });

  await addToCart.click();
  await page.waitForTimeout(1500);
  await page.goto('/cart/', { waitUntil: 'networkidle' });
  await expect(page.locator('body')).toContainText('Poultry Farmer Local Synthetic Download');
  expect(consoleErrors).toEqual([]);
});
