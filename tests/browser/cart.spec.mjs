import { test, expect } from '@playwright/test';

test('preserved browser session classic and Store API cart diagnostics', async ({ page }) => {
  test.setTimeout(30_000);
  await page.goto('/', { waitUntil: 'domcontentloaded', timeout: 15_000 });
  const before = await page.evaluate(async () => (await (await fetch('/wp-json/wc/store/v1/cart')).json()));
  const product = await page.evaluate(async () => (await (await fetch('/wp-json/wc/store/v1/products?search=Local%20Sample')).json())[0] || null);
  const storeAdd = product?.id ? await page.evaluate(async id => {
    const r = await fetch(`/wp-json/wc/store/v1/cart/add-item?id=${id}&quantity=1`, { method: 'POST', credentials: 'include' });
    return { status: r.status, headers: Object.fromEntries(r.headers.entries()), body: await r.text() };
  }, product.id) : { status: null, body: 'product-not-found' };
  const afterStore = await page.evaluate(async () => (await (await fetch('/wp-json/wc/store/v1/cart')).json()));
  const classic = product?.id ? await page.goto(`/?add-to-cart=${product.id}`, { waitUntil: 'domcontentloaded', timeout: 15_000 }) : null;
  const cookieNames = (await page.context().cookies()).map(cookie => ({ name: cookie.name, domain: cookie.domain, path: cookie.path, sameSite: cookie.sameSite }));
  const cartResponse = await page.goto('/cart/', { waitUntil: 'domcontentloaded', timeout: 15_000 });
  const cartText = await page.locator('body').innerText();
  const result = { product: product && { id: product.id, name: product.name, is_purchasable: product.is_purchasable, is_in_stock: product.is_in_stock }, before: { items_count: before.items_count, totals: before.totals }, storeAdd: { status: storeAdd.status, headers: storeAdd.headers, body: storeAdd.body.slice(0, 2000) }, afterStore: { items_count: afterStore.items_count, items: afterStore.items?.map(item => ({ id: item.id, quantity: item.quantity, name: item.name })) }, classicStatus: classic?.status() ?? null, cartStatus: cartResponse?.status() ?? null, cartContainsProduct: /Local Sample Virtual Download/i.test(cartText), cookieNames };
  console.log(JSON.stringify(result));
  expect(product).not.toBeNull();
});
