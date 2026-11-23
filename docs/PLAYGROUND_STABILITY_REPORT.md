# WordPress Playground Stability Report

Date: 2026-11-18 (Africa/Nairobi)

## Final status

**BLOCKED for Phase 2B runtime sign-off.** The WordPress-only and Poultry Farmer theme-only profiles are stable when run one at a time. WooCommerce profiles are not stable on this host/runtime combination. No production theme code was changed.

## Confirmed causes

1. **Host engine mismatch.** Pinned Playground CLI 3.1.65 depends on PHP-WASM packages 3.1.65 requiring Node `>=24.18.0` and npm `>=11.16.0`. The machine has Node 24.16.0 and npm 11.13.0. npm emitted `EBADENGINE` warnings.
2. **WooCommerce/WASM resource failure.** The WordPress 6.7/WooCommerce 9.9.7 control reproduced `FATAL ERROR: Zone Allocation failed - process out of memory` in Node. The host reported approximately 8 GB visible RAM and approximately 1.4 GB free.
3. **Shared generated storage.** Every CLI launch reported the same generated site directory: `C:\Users\admin\.wordpress-playground\sites\a896503845e2b903db1b01997c0b8ea7889284851142c9a2c189c21dfb221079`. CLI 3.1.65 exposes no storage-directory option. Concurrent launches reset or contaminated one another, producing the observed maintenance responses and `/wordpress/.maintenance` fatal path.

The `.maintenance` response is a secondary interrupted-bootstrap symptom. It is not established as the initial root cause, and no maintenance file was deleted by this task.

At final inspection, one generic Playground listener remained on port 9400 with a command that did not reference the Poultry Farmer project. It was left untouched to avoid affecting another local runtime. The Poultry Farmer profile processes and ports used by this task were stopped.

## Profile matrix

| Profile | Stack | Result | Evidence |
|---|---|---|---|
| A | WP 7.1.4 + PHP 8.3, default theme, no WooCommerce | PASS | Sequential homepage/article/search HTTP 200; no maintenance content; empty stderr |
| B | WP 7.1.4 + PHP 8.3 + Poultry Farmer, no WooCommerce | PASS | Sequential homepage/article/search HTTP 200; no maintenance content; empty stderr |
| C | WP 7.1.4 + PHP 8.3 + Poultry Farmer + WC 11.2.2 | BLOCKED | Port sometimes listened without HTTP; clean run did not reach a stable health response |
| D | WP 7.1.4 + PHP 8.3 + default theme + WC 11.2.2 | BLOCKED | Concurrent attempt invalidated by shared storage; later control served only partial routes |
| E | WP 6.7 + PHP 8.3 + Poultry Farmer + WC 9.9.7 | BLOCKED | Reproduced Node/WASM out-of-memory fatal during bootstrap |

Profiles A–D are defined in `tests/playground-profile-*.json`; Profile E is the old WooCommerce control. `tests/run-playground-smoke.ps1` runs one profile at a time, polls for a clean HTTP response, records requests, and stops matching Playground child processes.

## Reproducible commands

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\tests\run-playground-smoke.ps1 -Profile A -Port 9440
```

A project-local supported runtime is installed under `tests/tools/` and ignored by Git:

```powershell
tests\tools\node-v24.18.0-win-x64\npm.cmd --prefix tests exec --offline -- wp-playground-cli --version
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\tests\run-playground-smoke.ps1 -Profile A -Port 9440
```

It reports Node 24.18.0 and npm 11.16.0, but the Playground process still produced `ErrnoError { name: 'ErrnoError', errno: 51 }` against the shared generated storage. It is not a successful stabilization.

## Browser impact

The screenshot matrix was correctly blocked after the runtime entered 503/maintenance state. Existing screenshots remain preserved as failed diagnostics. No maintenance screenshot is treated as valid visual QA. A complete valid matrix cannot be captured until Profile C remains healthy through sequential route and browser tests.

## Recommendation

Do not begin Phase 2B. Obtain a clean supported Playground storage strategy or an approved isolated local WordPress runtime with sufficient memory. Then rerun Profile D and C sequentially, verify WooCommerce routes and Store API cart state, and only then rerun screenshots.
