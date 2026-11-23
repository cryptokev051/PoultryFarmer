# Local development workflow

## Source of truth

Codex edits the project under `D:\Projects\PoultryFarmer`. The authoritative theme and starter plugin sources are:

- `theme/poultryfarmer/`
- `plugins/poultryfarmer-starter/`

The Local WordPress site is `poultryfarmer.local`, rooted at:

`C:\Users\admin\Local Sites\poultryfarmer\app\public`

The site currently reports WordPress 7.1.5, PHP 8.2.29, MySQL 8.4.0 and nginx.

## Live connection

Two NTFS junctions connect Local to the project:

| Local destination | Junction target |
|---|---|
| `wp-content/themes/poultryfarmer` | `D:\Projects\PoultryFarmer\theme\poultryfarmer` |
| `wp-content/plugins/poultryfarmer-starter` | `D:\Projects\PoultryFarmer\plugins\poultryfarmer-starter` |

The destinations were absent before creation and were not replacements for existing folders. The project remains the source of truth. The project is not linked as a whole, and Local database files, uploads, credentials and runtime data remain outside the project.

## Preview and editing

Open `http://poultryfarmer.local/` to preview the site and `http://poultryfarmer.local/wp-admin/` for administration. Refresh after source edits. The one-time starter setup is administrator-triggered and must not be run on this Local database until its strict WordPress 7.1.4/en_US fixture baseline has been independently updated and verified for WordPress 7.1.5.

The homepage editing boundary is documented in `OWNER_CONTENT_EDITING_GUIDE.md`: Pages > Home stores the introduction, Appearance > Editor controls reusable site parts, and theme templates continue to control the approved hero, layout, queries and H1.

## Browser QA

Playground QA remains available through `playwright.config.mjs`. Local QA is separate:

```powershell
Set-Location D:\Projects\PoultryFarmer\tests
npm.cmd run test:local-smoke
```

The Local profile uses `http://poultryfarmer.local`, three viewports, non-destructive route checks and separate artifacts under `tests/artifacts/local/`. Starter routes that are not installed are reported as NOT READY, not as failures. Do not run Playground reset profiles against Local.

## Release packages

Build from source with:

```powershell
& D:\Projects\PoultryFarmer\theme\poultryfarmer\scripts\build-release.ps1
& D:\Projects\PoultryFarmer\plugins\poultryfarmer-starter\scripts\build-release.ps1
```

The resulting packages are independent of Local and contain no database, credentials, tests, screenshots or junction paths.

## Safe backup and disconnect

Before major Local changes, use Local's site backup/export facility and verify the backup location before editing. To disconnect safely, stop Local or the site, confirm each destination is a junction whose target is the expected project directory, and remove only the junction entry. Never recursively remove the target source directory. If a destination is a normal directory or points elsewhere, stop and inspect it instead of replacing it.

## Current state

The connection is live and the CSS source proof passed and was reverted. The `poultryfarmer` theme and `poultryfarmer-starter` plugin are active through Local's bundled WP-CLI. A recoverable database export was created at `C:\Users\admin\Local Sites\poultryfarmer\backups\before-poultryfarmer-starter-activation-2026-11-23.sql` before plugin activation.

Starter content was not created. Its read-only preview reports the expected 18 Pages, 8 Categories and settings changes, but blocks this site because WordPress is 7.1.5 rather than the verified 7.1.4 fixture and because the site already uses custom `/%postname%/` permalinks. The owner must not bypass that guard.
