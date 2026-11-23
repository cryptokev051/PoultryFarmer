import { test, expect } from '@playwright/test';

const productPath = '/product/poultry-farmer-local-synthetic-download/';
const productName = 'Poultry Farmer Local Synthetic Download';

async function expectCheckoutContents(page, quantity, total) {
  await expect(page).toHaveURL(/\/checkout\/?$/);
  const body = page.locator('body');
  await expect(body).toContainText(productName);
  await expect(body).toContainText('KSh10.00');
  await expect(body).toContainText(`Total price for ${quantity} ${productName} items`);
  await expect(body).toContainText(new RegExp(`KSh\\s*${total}(?:\\.00)?`));
}

test('Buy Now preserves cart contents and prevents repeat submissions', async ({ page }, testInfo) => {
  await page.goto('/cart/?add-to-cart=31&quantity=2', { waitUntil: 'networkidle' });
  await expect(page.getByRole('spinbutton', { name: /Quantity of Poultry Farmer Local Synthetic Download/ })).toHaveValue('2');
  await page.goto(productPath, { waitUntil: 'networkidle' });

  const form = page.locator('form.cart');
  const buyNow = form.locator('button[name="pf_buy_now"]');
  await expect(buyNow).toBeVisible();
  await expect(form.locator('.single_add_to_cart_button')).toBeVisible();
  await expect(form.locator('input.qty')).toHaveValue('1');
  const layout = await buyNow.evaluate(button => ({
    width: button.getBoundingClientRect().width,
    overflow: document.documentElement.scrollWidth - window.innerWidth,
  }));
  expect(layout.overflow).toBeLessThanOrEqual(1);
  if (testInfo.project.name === 'mobile-390') {
    expect(layout.width).toBeGreaterThan(300);
  }
  await form.locator('input.qty').fill('3');

  const replayData = await form.evaluate(element => ({
    product: element.querySelector('input[name="add-to-cart"]')?.value,
    token: element.querySelector('input[name="pf_buy_now_token"]')?.value,
    nonce: element.querySelector('input[name="pf_buy_now_nonce"]')?.value,
  }));
  expect(replayData.product).toBe('31');
  expect(replayData.token).toBeTruthy();
  expect(replayData.nonce).toBeTruthy();

  if (testInfo.project.name === 'desktop-1440') {
    await buyNow.focus();
    await buyNow.press('Enter');
  } else {
    await buyNow.click();
  }
  await expectCheckoutContents(page, 5, 50);

  page.on('dialog', dialog => dialog.accept());
  await page.goBack({ waitUntil: 'domcontentloaded' });
  await expect(page).toHaveURL(new RegExp(productPath.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '$'));
  const cartAfterBack = await page.evaluate(async () => (await (await fetch('/wp-json/wc/store/v1/cart')).json()).items);
  expect(cartAfterBack).toHaveLength(1);
  expect(cartAfterBack[0].quantity).toBe(5);

  const replay = await page.request.post(new URL(productPath, page.url()).toString(), {
    form: {
      'add-to-cart': replayData.product,
      quantity: '3',
      pf_buy_now: '1',
      pf_buy_now_token: replayData.token,
      pf_buy_now_nonce: replayData.nonce,
    },
    maxRedirects: 0,
  });
  expect(replay.status()).toBe(302);
  expect(replay.headers().location).toMatch(/\/checkout\/?$/);

  await page.goto('/checkout/', { waitUntil: 'networkidle' });
  await expectCheckoutContents(page, 5, 50);
});
