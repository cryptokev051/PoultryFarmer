# Runtime Compatibility Report

Date: 2026-11-18 (Africa/Nairobi)

## Outcome

The original WordPress 6.7/PHP 8.3/WooCommerce 9.9.7 fixture was previously able to activate the theme and serve routes. The requested current profile was accepted by the installed Playground CLI at startup with PHP 8.3 and WordPress 7.1.4, but HTTP requests did not become a valid WordPress site: the current profile returned 502, and the browser capture observed 503 maintenance responses plus a missing `/wordpress/.maintenance` include. The same 502 occurred with the default-theme control profile. This is a verified Playground/runtime fixture blocker, not evidence of a Poultry Farmer theme defect.

## Exact versions and commands

| Component | Version/result |
|---|---|
| Node.js | 24.16.0 |
| npm | 11.13.0 |
| `@wp-playground/cli` | 3.1.65 published package; `--version` reports `unknown` |
| PHP requested | 8.3; Playground startup banner reported PHP 8.3 |
| Old WordPress profile | 6.7; WooCommerce 9.9.7 |
| Current WordPress profile | 7.1.4; WooCommerce 11.2.2 |
| Playwright | 1.65.1 project-local under `tests/` |
| Browser | Playwright-managed Chromium 159.0.8154.6 |
| PHP-WASM dependency engines | Node `>=24.18.0`, npm `>=11.16.0` |

Old profile command:

```powershell
npm.cmd exec --yes --package=@wp-playground/cli -- wp-playground-cli start --path='D:\Projects\PoultryFarmer\theme\poultryfarmer' --wp=6.7 --php=8.3 --port=9426 --skip-browser --login=false --site-url=http://127.0.0.1:9426 --blueprint='D:\Projects\PoultryFarmer\tests\playground-blueprint.json' --reset
```

Current profile command:

```powershell
npm.cmd exec --yes --package=@wp-playground/cli -- wp-playground-cli start --path='D:\Projects\PoultryFarmer\theme\poultryfarmer' --wp=7.1.4 --php=8.3 --port=9431 --skip-browser --login=false --site-url=http://127.0.0.1:9431 --blueprint='D:\Projects\PoultryFarmer\tests\playground-blueprint-wp714-wc1122.json' --reset
```

The CLI help exposes no per-process storage/site-directory option. Multiple launches reported the same Playground site storage path and reset each other. This is a confirmed isolation hazard. Profiles must be run sequentially and the CLI storage must be reset/repaired before repeating the browser matrix.

## Compatibility finding

The CLI accepts `--wp=7.1.4` and prints a PHP 8.3/WordPress 7.1.4 startup banner. That confirms argument acceptance only. It does not confirm a working site. The current profile returned 502; an isolated default-theme control attempt also returned 502. The requested version combination is therefore **BLOCKED**, not PASS.

No machine-wide Node upgrade was performed. The installed Node 24.16.0/npm 11.13.0 do not satisfy the PHP-WASM engine requirements. A project-local Node 24.18.0/npm 11.16.0 was tested under `tests/tools/`; it removed the engine mismatch but produced Playground `ErrnoError 51` against the shared generated storage. The old WooCommerce control also recorded `FATAL ERROR: Zone Allocation failed - process out of memory` in Node.

The referenced `/wordpress/.maintenance` file was not present inside the Poultry Farmer project tree. The generated Playground path was documented from CLI output, but no outside-project maintenance file was deleted or modified. A generic port-9400 Playground process not referencing this project was left untouched.

## Add-to-cart diagnosis

The original synthetic product ID 16 was not re-used as a validity assumption. The browser fixture attempted to discover the synthetic product through the Store API in the same browser context and preserve cookies. In the captured run, the Store API returned HTML maintenance/error content rather than JSON, no product was discovered, no WooCommerce session cookie was present, and the cart did not contain the product. The cart page response was 503 during that run.

Captured response evidence included:

- `GET /wp-json/wc/store/v1/products?search=Local%20Sample` returned HTTP 200 with HTML error content, not Store API JSON.
- `GET /cart/` returned HTTP 503 with `Retry-After: 600`.
- The page body included a fatal error requiring `/wordpress/.maintenance`.
- Redacted request headers contained no session token or authorization data.

This makes the observed cart failure **unresolved at the product/theme level** and **consistent with the Playground maintenance/storage collision and test fixture state**. Classic add-to-cart versus Store API behavior cannot be validly compared until a clean, serving profile is available. No production theme file was changed.

## PHP/logging

The browser response exposed a PHP fatal path in the maintenance loader, but no standalone PHP binary or usable WooCommerce log endpoint was available in the project. A valid current-version PHP error log comparison remains blocked.

## Project changes

- Added the current-version blueprint: `tests/playground-blueprint-wp714-wc1122.json`.
- Added a default-theme control blueprint: `tests/playground-blueprint-wp714-wc1122-default.json`.
- Added project-local Playwright configuration and browser diagnostics under `tests/`.
- Added project-local Playwright dependency installation and managed Chromium.
- Added generated fixture directories under `tests/fixtures/`; they are ignored by Git.
- No production theme functionality or branding assets were modified.
- No orders, payments, gateways, deployment, or live sites were used.

## Remaining issues

1. Repair or replace the Playground CLI storage isolation and memory/runtime conditions.
2. Re-run current and old profiles sequentially, not concurrently, and capture the site storage path for each run.
3. Re-run classic and Store API cart tests with a discovered product and preserved browser cookies.
4. Run the default-theme control on the same clean runtime.
5. Complete authenticated Site Editor testing and standalone PHP linting.
