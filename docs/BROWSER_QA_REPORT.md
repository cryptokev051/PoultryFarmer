# Browser QA Report

Date: 2026-11-18 (Africa/Nairobi)

## Outcome

Playwright with project-local Chromium is installed and configured for 1440px desktop, 768px tablet, and 390px mobile. The route capture harness ran against the current-version Playground port, but the runtime entered maintenance/error state during the matrix. The browser environment is reproducible; full visual sign-off is **BLOCKED** by WooCommerce/Playground runtime startup and storage failures. The stable Profile B result confirms the theme can serve without WooCommerce.

## Captured artifacts

Screenshots are under `tests/screenshots/`:

- `desktop-1440/`: homepage, article, Kienyeji page, shop, product, cart, checkout.
- `tablet-768/`: homepage, article, Kienyeji page, shop, product, cart, checkout, account, search, 404, plus `diagnostics.json`.
- `mobile-390/`: homepage, article, Kienyeji page, shop, product, cart, checkout.

The complete 10-route × 3-viewport matrix did not finish as a valid PASS because later navigations hit 503/connection aborts and screenshot timeouts. Existing files are retained as diagnostic artifacts, not visual approval.

## Test matrix

| Area | Result | Evidence/notes |
|---|---|---|
| Chromium installation | PASS | Playwright-managed Chromium 159.0.8154.6 |
| Desktop 1440 screenshot harness | BLOCKED | Runtime response became maintenance/aborted |
| Tablet 768 screenshot harness | BLOCKED | Artifacts captured; most routes were HTTP 503 |
| Mobile 390 screenshot harness | BLOCKED | Screenshot/navigation timeout after runtime failure |
| Homepage | PASS for captured HTTP 200 | Tablet diagnostic captured 200 before maintenance state |
| Editorial article | BLOCKED | Captured 503 maintenance response |
| Kienyeji farming page | BLOCKED | Captured 503 maintenance response |
| Shop archive | BLOCKED | Captured 503 maintenance response |
| Single product | BLOCKED | Product discovery returned maintenance HTML |
| Cart | BLOCKED | HTTP 503; no valid cart session |
| Checkout visibility | BLOCKED | Direct request timed out in current profile |
| 404 rendering | BLOCKED | Maintenance fatal path, not a theme 404 result |
| Search results | BLOCKED | Maintenance response/connection abort |
| Account page | BLOCKED | Maintenance response/connection abort |
| Mobile navigation | NOT TESTED | No valid runtime state; harness recorded `NOT TESTED` |
| Keyboard accessibility | NOT TESTED | No valid runtime state |
| Search interaction | NOT TESTED | Route probe only; no valid form interaction |
| Cart quantity/persistence | BLOCKED | Product/session unavailable |
| Notices | BLOCKED | No valid WooCommerce cart response |
| Horizontal overflow | NOT SIGN-OFF | Diagnostic measured 980/980 in an error-state page |
| Console errors | FAIL in runtime | Repeated 503 resource errors; not attributed to theme |
| Missing assets | NOT TESTED | Runtime failure prevents trustworthy asset audit |
| Site Editor authenticated session | NOT TESTED | `--login=false`; no authenticated browser session was created |

Profile A and B smoke tests are recorded under `tests/artifacts/stability-profile-A/result.json` and `tests/artifacts/stability-profile-B/result.json`. Profile C is blocked before a trustworthy browser session can be established.

## Reproduction

```powershell
Set-Location D:\Projects\PoultryFarmer\tests
npm.cmd install --no-fund --no-audit
npx.cmd playwright install chromium
$env:PF_BASE_URL='http://127.0.0.1:9431'
npx.cmd playwright test --config=playwright.config.mjs --workers=1
```

The test harness is `tests/browser/runtime.spec.mjs`; the focused cart probe is `tests/browser/cart.spec.mjs`.

## Browser/runtime finding

The browser captured a PHP fatal path requiring `/wordpress/.maintenance` and HTTP 503 responses with `Retry-After: 600`. Since the default-theme control also returned 502 before a valid site response, this is classified as a Playground/storage/runtime blocker. It is not a confirmed Poultry Farmer header, template, CSS, or WooCommerce callback defect.

No design, calculator, production, payment, order, or deployment work was performed.
