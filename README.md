# Poultry Farmer

Independent WordPress development project for [Poultry Farmer](https://poultryfarmer.co.ke/), a Kenyan poultry farming publication and digital tools platform.

Poultry Farmer operates independently from AgriRadar and BusinessPlans.co.ke. The original AgriRadar theme is retained only as the historical source foundation; it is not part of this project and is not modified by this project.

## Structure

- `theme/poultryfarmer/` - installable WordPress block theme source.
- `plugins/` - reserved for the future `poultryfarmer-core` plugin; no plugin is included yet.
- `brand-assets/` - complete, preserved 47-file supplied Poultry Farmer artwork package.
- `docs/` - project audit, implementation, QA, readiness, relocation and source-manifest reports.
- `tests/` - reserved for project-specific tests.
- `releases/` - reproducible release ZIP output (ignored by Git).

## Local development

Use an isolated local WordPress/WooCommerce environment for runtime checks. PHP is required for syntax validation. Do not connect this project to production or modify a live database during development. See `docs/LOCAL_WORDPRESS_TESTING_PLAN.md` for detected capabilities and the recommended setup.

## Package the theme

From `theme/poultryfarmer/`, run:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\build-release.ps1
```

The reproducible installable ZIP is written to `releases/poultryfarmer.zip` and contains exactly one `poultryfarmer/` root directory. The theme `dist/` path is ignored and is not a release destination.

## Status

Phase 2A identity/isolation and relocation are complete pending independent review. Phase 2B visual redesign, calculators, farm records and community functionality have not started.
