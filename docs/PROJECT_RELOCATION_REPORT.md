# Poultry Farmer Project Relocation Report

**Status:** PASS for copy-first relocation and static verification; runtime checks remain NOT TESTED.  
**Date:** 2026-11-18  
**Original workspace:** `D:\Projects\AgriRadar - Copy`  
**New project:** `D:\Projects\PoultryFarmer`

## 1. Relocation result

The Poultry Farmer project was copied into an independent project directory and verified before removal of the old standalone theme directory. The active theme path is now:

`D:\Projects\PoultryFarmer\theme\poultryfarmer`

The shared AgriRadar workspace remains intact apart from removal of the old standalone `D:\Projects\AgriRadar - Copy\poultryfarmer` directory after verification. AgriRadar Theme, `theme/agriradar/`, `plugins/agriradar-core/`, shared documentation, original Git history, and shared build configuration were not moved or modified.

## 2. Inventory

| Item | Original | New location | Result |
|---|---|---|---|
| Independent theme | `AgriRadar - Copy/poultryfarmer/` | `PoultryFarmer/theme/poultryfarmer/` | Relocated; internal structure preserved |
| Poultry Farmer reports | `AgriRadar - Copy/docs/` | `PoultryFarmer/docs/` | Seven identified reports copied |
| Relocation manifest | Generated before copy | `PoultryFarmer/docs/PROJECT_RELOCATION_MANIFEST.sha256` | Present |
| Approved brand package | Theme `assets/images/brand/` | Theme path plus `PoultryFarmer/brand-assets/` | 47 supplied files copied |
| Release ZIP | Theme `dist/poultryfarmer.zip` | Theme `dist/` and `PoultryFarmer/releases/poultryfarmer.zip` | Rebuilt and copied |
| Project scaffolding | Not present | `plugins/`, `tests/`, `brand-assets/`, `docs/`, `releases/`, `README.md`, `.gitignore` | Created |

The seven relocated reports were: `POULTRYFARMER_THEME_AUDIT.md`, `PHASE2A_IMPLEMENTATION_REPORT.md`, `IDENTITY_MIGRATION_MAP.md`, `PHASE2A_QA_REPORT.md`, `PHASE2B_READINESS.md`, `PHASE2A_SOURCE_SNAPSHOT.sha256`, and `PHASE2A_FINAL_VERIFICATION.md`. No unrelated AgriRadar report was copied.

The relocated theme contained 92 files at copy time, including its existing distribution artifact. The non-distribution theme source contains 91 files. The top-level project brand directory contains 47 supplied artwork/support files, including PNG, JPG, WEBP, ICO, PDF, JSON, CSV and TXT assets. No replacement artwork was generated.

## 3. Hash verification

The pre-relocation manifest is stored at `docs/PROJECT_RELOCATION_MANIFEST.sha256`. It covers 99 original files: 92 theme files and seven reports.

- Pre-removal verification: 99 manifest files, 0 missing, 0 mismatches.
- Non-distribution theme comparison after copy: 91 source files, 91 destination files, 0 missing, 0 extra, 0 SHA-256 mismatches.
- Original AgriRadar source snapshot: 69 files, 0 mismatches.
- The destination ZIP was deliberately rebuilt after the pre-removal manifest check to package the relocated supplied brand assets; the generated ZIP is therefore a new release artifact rather than a byte-for-byte copy of the older source ZIP.

## 4. Files deliberately left behind

The following were not moved: `AgriRadar Theme/`, `theme/agriradar/`, `plugins/agriradar-core/`, existing AgriRadar documentation, shared scripts/build tools, unrelated reports, research fixtures, local test files, and original Git metadata. The old standalone Poultry Farmer theme directory was the only source directory removed, and only after copy verification passed.

## 5. Updated paths and portability

The theme packaging script remains at `theme/poultryfarmer/scripts/build-release.ps1`. Its path resolution is relative to `$PSScriptRoot`, so it now packages from the independent project location without absolute production paths. Running it from the relocated theme produced `theme/poultryfarmer/dist/poultryfarmer.zip`.

The README documents the independent project structure, theme packaging command, release location, future `poultryfarmer-core` plugin boundary, and Phase 2A status. No absolute Windows path was introduced into production theme code. Historical reports retain the original AgriRadar source path where needed for audit traceability.

## 6. Validation matrix

| Check | Status | Evidence |
|---|---|---|
| Source and destination safety | PASS | Original workspace/theme existed; destination did not exist before relocation. |
| Existing Git status preserved | PASS | Pre-existing modified/deleted/untracked status was recorded; no commit/reset/clean was performed. |
| AgriRadar source snapshot | PASS | `PHASE2A_SOURCE_SNAPSHOT.sha256`: 69/69 matches. |
| Relocation manifest | PASS | 99/99 files matched before source removal. |
| Theme structure | PASS | 91 non-distribution source files match exactly between old and new theme trees. |
| Theme metadata | PASS | Name `Poultry Farmer`, text domain `poultryfarmer`, version `0.1.0`, portable theme URI and PHP/WP requirements retained. |
| PHP syntax | PASS | Existing workspace PHP 8.3.33 linted 23/23 relocated PHP files successfully. |
| `theme.json` | PASS | Parses successfully and remains version 3. |
| Identity scan | PASS | No AgriRadar names, domains, prefixes, old slugs, unrelated legacy terms, or absolute Windows paths in the relocated production tree. |
| Brand assets | PASS | 47 supplied files present in both theme brand path and top-level `brand-assets/`; no AgriRadar logo assets copied. |
| Release packaging | PASS | 90 entries, exactly one `poultryfarmer/` root, 0 bad path entries, 0 hygiene hits; required files present. |
| WordPress activation/rendering | NOT TESTED | No local WordPress runtime was installed or activated. |
| WooCommerce/HPOS/browser runtime | NOT TESTED | No runtime, database, orders, payments, or browser rendering checks were performed. |

## 7. Outstanding issues

- The release now includes the supplied brand package because it is inside the theme asset tree. The package contains both production artwork and supporting brand-guide files; a later packaging review may decide whether non-production support files should be kept only in top-level `brand-assets/`.
- No WordPress or WooCommerce runtime validation was performed.
- Phase 2B design work, calculators, the future core plugin, community functionality, deployment and production configuration remain out of scope.

## 8. Independent opening and recommendation

The new project can be opened independently in VS Code at `D:\Projects\PoultryFarmer`. Open the project root; the WordPress theme is under `theme/poultryfarmer/`.

**Recommendation:** PASS relocation and static verification; await independent review before any Phase 2B work.

