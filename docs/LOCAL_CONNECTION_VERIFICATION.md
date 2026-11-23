# Local connection verification

Date: 23 November 2026

## Discovered installation

Local's managed site directory was identified as:

`C:\Users\admin\Local Sites\poultryfarmer`

The WordPress root is `C:\Users\admin\Local Sites\poultryfarmer\app\public`; its `wp-content` is the normal directory under that root. The WordPress core version file reports 7.1.5. The HTTP host `http://poultryfarmer.local/` returned 200.

Other Local sites were present, but no other site was modified.

## Junction verification

Both requested destinations were absent before creation. They are now junctions to the exact project directories:

| Destination | Target | Result |
|---|---|---|
| `.../wp-content/themes/poultryfarmer` | `D:\Projects\PoultryFarmer\theme\poultryfarmer` | PASS |
| `.../wp-content/plugins/poultryfarmer-starter` | `D:\Projects\PoultryFarmer\plugins\poultryfarmer-starter` | PASS |

No existing same-name folder was overwritten and no junction was removed.

## Live source proof

A uniquely marked CSS custom property was added temporarily to the project theme stylesheet. A cache-busting request to `http://poultryfarmer.local/wp-content/themes/poultryfarmer/style.css` returned HTTP 200 and contained the marker. The source line was then reverted; a second cache-busting request returned HTTP 200 without the marker. This proves Local serves the project source through the junction and leaves the approved styling unchanged.

## Local runtime and browser checks

The site currently renders Twenty Twenty-Five. The homepage and login page returned 200; `/wp-admin/` redirected to the login entry. The separate Playwright command was:

```powershell
Set-Location D:\Projects\PoultryFarmer\tests
npm.cmd run test:local-smoke
```

Result: 6 passed across desktop 1440px, tablet 768px and mobile 390px. Screenshots were saved under `tests/artifacts/local/`. `/farming/`, `/farming/kienyeji/` and `/guides/` returned 404 in all viewports and were annotated NOT READY because starter content is not installed.

The smoke test confirmed body visibility, no horizontal overflow, no missing images on ready routes, and reachable WordPress login/admin entry points. It did not perform a database reset or starter mutation.

## Activation and compatibility

The source directories are available at the correct WordPress identifiers. Local's bundled WP-CLI reports `poultryfarmer` as the active template and stylesheet. The `poultryfarmer-starter` plugin is active at version 1.0.1. The active homepage returned HTTP 200 with Poultry Farmer references, header/footer markers and no fatal-error text.

A recoverable database export was created before plugin activation at `C:\Users\admin\Local Sites\poultryfarmer\backups\before-poultryfarmer-starter-activation-2026-11-23.sql`. No starter content was created and no site settings were changed.

The theme declares WordPress 6.6+ and PHP 7.4+. The starter read-only preview identified the expected 18 Pages, 8 Categories and settings plan, then reported two conflicts: WordPress 7.1.5 is outside the verified 7.1.4 en_US fixture, and the existing `/%postname%/` permalink structure is custom/owner-configured. WordPress 7.1.5 is therefore not an approved starter-setup target. The smallest safe correction is to verify the 7.1.5 en_US fresh-install fixture and add explicit fingerprints/version support, then rerun the negative preflight suite; bypassing the guard is not acceptable.

## Release verification

Both packages were rebuilt from project source with portable forward-slash entries. Archive inspection found no Local Sites path, database, credential, test, screenshot or dependency entry.

| Package | Entries | SHA-256 |
|---|---:|---|
| `releases/poultryfarmer.zip` | 65 | `74D1DF179D77C4B698AF14064FF8623C6A315433B0D04E27AFF4D50D0215F524` |
| `releases/poultryfarmer-starter.zip` | 2 | `6362E27871E4034F446D3B18EC790E50228990A5810A8293FF72B6618E7D4B67` |

Native PHP lint passed for all shipped PHP files using Local PHP 8.2.29. The packages pass builder integrity checks and remain independent of Local; no Local path or database file is packaged.

## Git and remaining blockers

The project has no committed baseline; Git reports the existing project directories and files as untracked (`.gitignore`, `README.md`, `docs/`, `plugins/`, `tests/`, `theme/`). No commit, push or deployment was performed.

Remaining blockers are limited to authenticated Local admin verification and the intentional starter-plugin 7.1.5 fixture compatibility gate. No theme redesign, feature work, WooCommerce/payment change or production migration was made.
