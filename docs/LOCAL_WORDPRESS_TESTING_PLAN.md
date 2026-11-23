# Local WordPress Testing Plan

Validation update: the official `@wp-playground/cli` was successfully run with PHP 8.3 and WordPress 6.7 on loopback. See `LOCAL_WORDPRESS_VALIDATION_REPORT.md` and `PHASE2B_RUNTIME_BASELINE.md` for actual runtime results, the pinned WooCommerce 9.9.7 fixture, and current blockers.

## Detection on 18 November 2026

| Capability | Result | Notes |
|---|---|---|
| PHP CLI | BLOCKED | No `php` executable detected; PHP syntax checks cannot run. |
| MySQL/MariaDB | BLOCKED | No `mysql`, `mysqld` or `mariadb` executable detected. |
| Node.js | AVAILABLE | Node.js `v24.16.0`. |
| npm | AVAILABLE | npm `11.13.0` via `npm.cmd`; PowerShell script execution policy blocks `npm.ps1`. |
| Docker | NOT AVAILABLE | No Docker executable detected. |
| WP-CLI | NOT AVAILABLE | No `wp` executable detected. |
| WordPress Playground tooling | NOT DETECTED | No project-local Playground or WP runtime files found. |
| Local WordPress instance | NOT DETECTED | No local `wp-content`, database, or WordPress runtime exists in this project. |
| Browser executable on PATH | NOT DETECTED | No Chrome, Edge or Firefox command was detected. |

## Recommendation

Use an isolated WordPress Playground environment for the first visual smoke test if browser access is available. If a persistent WooCommerce test site is required, use a separate local container or local site with WordPress, PHP 8.2+, MySQL 8/MariaDB 10.6+, WooCommerce, and a browser. Keep the site in a project-specific directory or container/network namespace named for Poultry Farmer only.

The runtime must use a fresh local database and a non-production hostname. Do not reuse AgriRadar, BusinessPlans.co.ke, the live Poultry Farmer domain, a production database, customer records, payment gateways, or real transactions. Import only synthetic content and test products.

## Smoke-test sequence once a runtime exists

1. Install the theme ZIP from `releases/poultryfarmer.zip` into the isolated site.
2. Activate it and confirm the theme metadata, templates, parts, patterns, CSS, WooCommerce templates, and site-logo block load.
3. Check the front page, archive, single post, search, 404, cart, checkout, and account templates at desktop and mobile widths.
4. Confirm the neutral placeholder asset resolves and no AgriRadar strings, paths, logos or favicons appear.
5. Use only synthetic WooCommerce products and stop before any checkout/payment action.

No runtime test was performed in this baseline because no safe local WordPress instance or PHP/database stack is available.
