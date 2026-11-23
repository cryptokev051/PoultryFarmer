# Poultry Farmer Starter Setup Implementation Report

Date: 19 November 2026  
Decision: **PASS — ready for independent source review**

## Outcome

An independent, removable WordPress plugin now provides the missing fresh-install content setup. It uses only standard WordPress APIs, normal Pages/Categories/core blocks, an administrator preview and an explicit nonce-protected action. No production theme file or WooCommerce integration file was changed.

## Files added or changed

Production component:

- `plugins/poultryfarmer-starter/poultryfarmer-starter.php`
- `plugins/poultryfarmer-starter/readme.txt`
- `plugins/poultryfarmer-starter/scripts/build-release.ps1`

Local validation:

- `tests/playground-starter-fresh.json`
- `tests/playground-starter-conflict.json`
- `tests/start-starter-profile.ps1`
- `tests/browser/starter-setup.spec.mjs`
- `tests/browser/starter-persistence.spec.mjs`
- `tests/browser/starter-conflict.spec.mjs`
- `tests/browser/starter-package.spec.mjs`
- `tests/package.json`

Documentation:

- `docs/STARTER_CONTENT_SPECIFICATION.md`
- `docs/STARTER_SETUP_IMPLEMENTATION_REPORT.md`
- `docs/STARTER_SETUP_QA.md`
- `docs/FRESH_INSTALL_CHECKLIST.md`
- small production-setup notes in the existing content-model/editorial documentation.

## Implementation details

`PoultryFarmer_Starter_Setup` registers one Tools screen and one `admin_post` handler. Authorization is checked with `manage_options`; the mutation uses `check_admin_referer`. Definitions are ordered parent-before-child. Categories are created first, resolved by slug, recorded, and then used to generate the Page Query Loops.

The manifest option is non-autoloaded. Setup never searches by slug and adopts a result: untracked matches are conflicts. Tracked Pages and Categories are not updated on subsequent runs. This deliberately favours owner edits over automatic synchronization.

The setup accepts WordPress 7.1.4’s fresh date-based permalink only after verifying that no Posts or Pages exist beyond the untouched core sample objects. This was required because Playground’s fresh installation did not use an empty permalink option. Established custom structures remain blocked.

## Independent-review corrections

- The theme front-page template now renders the WordPress Home Page's `core/post-content` block inside the approved introductory section. The approved theme hero and SEO H1 remain authoritative, and Page Editor content is stored in WordPress rather than theme source.
- The fresh-install preflight now returns specific reasons and rejects additional public editorial/custom content, custom categories, default sample modifications and WooCommerce-style product records. Internal revisions and template metadata are not counted.
- `apply_setup()` now reads back all four settings after writing and does not mark setup complete unless the saved values match.
- Tracked objects are preserved across reruns and partially completed manifests; missing or changed tracked identifiers remain conflicts rather than being adopted or overwritten.
- Poultry hubs and management topic drafts now have distinct, useful outlines. No prices, profits, disease guidance, statistics or credentials are invented.

## Approved and draft copy

The four poultry hubs contain distinct, short production-model overviews and planning considerations. Management topics contain clearly labelled draft scaffolds. Prices contains no figures and requires dated, sourced verification. Calculators remains explicitly non-functional. Start Here, About, Editorial Policy and Contact use a visible “Draft starter page” prompt and remain drafts.

The Home Page stores editable introductory copy while the theme's `front-page` template remains authoritative for presentation and the single approved H1. Farming and Management templates render each Page's `core/post-content`, with Home, Category, and template presentation kept in the theme.

## Source isolation

- Current `theme/poultryfarmer/inc/woocommerce.php` SHA-256: `993905a98d6ecc30749cd68a1327c79189f44b519d8a37be6ec3656b0106ec4c` (matches the Phase 2B2 unchanged-source baseline).
- Theme ZIP remains `releases/poultryfarmer.zip`; it was not rebuilt.
- No AgriRadar, WooCommerce, payment, customer or credential logic appears in the starter component.
- The component defines no custom post type, rewrite rule, SEO metadata system or calculator formula.

## Installable package

Validated release: `releases/poultryfarmer-starter.zip`

- Size: 11,824 bytes
- SHA-256: `6362e27871e4034f446d3b18ec790e50228990a5810a8293ff72b6618e7d4b67`
- Entries:
  - `poultryfarmer-starter/poultryfarmer-starter.php`
  - `poultryfarmer-starter/readme.txt`

The build uses explicit forward-slash ZIP entry names. A Windows `Compress-Archive` build was rejected during QA because its backslash entries failed WordPress’s copy stage; that packaging defect was corrected before this release hash was produced. The final ZIP installed and activated through **Plugins > Add Plugin > Upload Plugin** in a bare isolated WordPress profile.

## Git status and proposed initial commit

The repository still has no initial commit. Nothing was staged, committed or pushed. Top-level source remains untracked; generated/runtime paths remain ignored.

Proposed owner-approved initial commit contents:

- include `.gitignore`, `README.md`, `docs/`, `plugins/poultryfarmer-starter/`, project test scripts/configuration and `theme/poultryfarmer/`;
- include required production logo exports under the theme;
- exclude `brand-assets/`, `releases/`, `tests/.runtime-*`, `tests/artifacts/`, `tests/screenshots/`, `tests/node_modules/`, `tests/tools/`, portable binaries, local databases, credentials and generated `dist/` directories;
- review explicit paths before staging; do not use an unreviewed `git add -A`.

Suggested commit subject: `feat: add safe Poultry Farmer starter site setup`

## Recommendation

Approve the component for independent code review and controlled fresh-install use. Do not run it on an established site until the preview reports no conflicts and the proposed Home/Posts/permalink changes have been approved. Do not deploy or begin another phase as part of this work.

## Final preflight correction (23 November 2026)

The setup now distinguishes three states: initial setup (no manifest), verified partial setup (manifest exists but settings are not marked complete), and completed setup. Initial setup requires positive checks for the approved theme, default front/Posts assignments, exact supported core fixtures, no untracked editorial/product records or Categories, and a supported default permalink. Any custom permalink, including `/%postname%/`, is a conflict on an untracked installation; an empty reason is never treated as approval.

Partial setup resumes only when every manifest identifier still carries its starter key, no unknown public records or Categories appeared, and settings are either still at the supported default or already equal to the exact starter settings. Completed setup preserves owner-edited content and rejects changed global settings rather than resetting them.

The initial branch independently requires `show_on_front=posts`, `page_on_front=0`, `page_for_posts=0`, and an explicitly supported default permalink before it can proceed. This closes the regression where valid fresh sample content plus a custom Reading assignment could pass preflight.

Hello World and Sample Page are checked with exact fingerprints covering identity, status and the full normalized content. The generated Privacy Policy is checked with its exact normalized content fingerprint after replacing only the site URL with a constant token. Volatile/generated fields are excluded. An edited sample or Privacy Policy fails even when it retains the original title and slug. Initial setup is intentionally restricted to the documented en_US WordPress 7.1.4 fixture and fails closed for other locale/version baselines.

The controlled interruption seam is `poultryfarmer_starter_checkpoint`, invoked after each Category, after each Page, and before settings writes. A test callback may return `WP_Error`; production has no callback and normal behavior is unchanged. The remaining narrow interruption window is between a successful settings write/read-back and the completion-manifest write; a rerun sees the target settings plus a consistent partial manifest and resumes safely.

## Final release recommendation

Stop for independent review. Do not deploy, push or commit. The approved theme, WooCommerce, payment behavior and production installations remain outside this change.
