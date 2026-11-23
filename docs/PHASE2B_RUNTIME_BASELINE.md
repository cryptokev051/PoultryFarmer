# Phase 2B Runtime Baseline

## Readiness assessment

Status: **BLOCKED FOR FULL VISUAL QA AND INDEPENDENT REVIEW BEFORE PHASE 2B**.

The Poultry Farmer block theme activates and renders in an isolated WordPress Playground environment with PHP 8.3 and WordPress 6.7. Static metadata, templates, patterns, CSS, production assets, and the release ZIP are validated. WooCommerce 9.9.7 activates and its main routes render with a synthetic product.

Full visual and interaction sign-off remains blocked by PHP-WASM engine mismatch on the host Node/npm versions, WooCommerce-triggered Node/WASM memory failure, current-version 502/503 maintenance errors, the Playground CLI's shared storage collision across launches, unavailable standalone PHP, and the unresolved browser cart probe.

See [RUNTIME_COMPATIBILITY_REPORT.md](RUNTIME_COMPATIBILITY_REPORT.md) and [BROWSER_QA_REPORT.md](BROWSER_QA_REPORT.md) for evidence and the exact current-version profile.

## Fixed baseline command

```powershell
npm.cmd exec --yes --package=@wp-playground/cli -- wp-playground-cli start `
  --path='D:\Projects\PoultryFarmer\theme\poultryfarmer' `
  --wp=6.7 --php=8.3 --port=9426 --skip-browser --login=false `
  --site-url=http://127.0.0.1:9426 `
  --blueprint='D:\Projects\PoultryFarmer\tests\playground-blueprint.json' --reset
```

Blueprint fixture: [tests/playground-blueprint.json](D:\Projects\PoultryFarmer\tests\playground-blueprint.json).

## Baseline evidence

- Homepage, representative pages, search, shop, product, cart, checkout, and account routes rendered HTTP 200 in the clean single-instance run.
- `theme.json` parsed successfully and reports version 3.
- 13 production brand assets and the complete 47-file source package are present.
- PHP standalone lint is BLOCKED in this machine; the earlier Phase 2A report records a successful 23-file lint.
- No browser screenshots exist for this run.
- 404, Site Editor, navigation interaction, keyboard focus, notices, and viewport checks remain untested or blocked.
- Add-to-cart did not place the synthetic product in the cart through the non-browser probe.

## Required before visual sign-off

1. Repair Playground profile storage/isolation and the supported Node/WASM runtime, then rerun the 1440px, 768px, and 390px browser matrix.
2. Run the Site Editor with an authenticated local admin session and inspect pattern registration/navigation.
3. Re-test classic and Store API add-to-cart with a browser-managed session and inspect WooCommerce notices.
4. Install standalone PHP 8.3+ or provide an approved project-local binary, then lint all 23 PHP files.
5. Keep the runtime on a project-specific local host/database; never reuse AgriRadar, BusinessPlans.co.ke, or production Poultry Farmer state.

Phase 2B has not been started. No runtime claim should be upgraded to PASS until the listed checks are completed.
