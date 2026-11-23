import { test } from '@playwright/test';

test('diagnose settled commerce UI', async ({ page }) => {
  await page.goto('/product/poultry-farmer-local-synthetic-download/', { waitUntil: 'networkidle' });
  await page.locator('.single_add_to_cart_button').click();
  await page.goto('/cart/', { waitUntil: 'networkidle' });
  await page.waitForTimeout(8000);
  await page.screenshot({ path: `artifacts/storefront-settled/${test.info().project.name}/cart.png`, fullPage: true });
  console.log('cart', await page.evaluate(() => ({ body: document.body.innerText, skeletons: document.querySelectorAll('[class*="skeleton"], [class*="placeholder"]').length })));
  await page.goto('/checkout/', { waitUntil: 'networkidle' });
  await page.waitForTimeout(8000);
  await page.screenshot({ path: `artifacts/storefront-settled/${test.info().project.name}/checkout.png`, fullPage: true });
  console.log('checkout', await page.evaluate(() => ({ body: document.body.innerText, skeletons: document.querySelectorAll('[class*="skeleton"], [class*="placeholder"]').length })));
  await page.goto('/shop/', { waitUntil: 'networkidle' });
  console.log('shop-controls', await page.evaluate(() => ({ count: document.querySelector('.woocommerce-result-count')?.textContent.trim(), countDisplay: getComputedStyle(document.querySelector('.woocommerce-result-count')).display, orderingDisplay: getComputedStyle(document.querySelector('.woocommerce-ordering')).display })));
  await page.goto('/product/poultry-farmer-local-synthetic-download/', { waitUntil: 'networkidle' });
  console.log('header', await page.evaluate(() => { const e=document.querySelector('.site-header-search'); const r=e.getBoundingClientRect(); return {width:r.width, right:r.right, viewport:innerWidth, input:e.querySelector('input')?.getBoundingClientRect().toJSON()}; }));
});
