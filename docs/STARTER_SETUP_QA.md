# Poultry Farmer Starter Setup QA

Date: 23 November 2026  
Environment: WordPress 7.1.4, PHP 8.3, Playground CLI 3.1.65, Playwright 1.65.1, project Node 24.18.0, Chromium

## Result matrix

| Test | Result | Evidence |
|---|---|---|
| Administrator-only preview and explicit action | PASS | Authenticated Tools screen listed 18 Pages, 8 Categories and settings before mutation. |
| Active-theme guard | PASS | Final release ZIP smoke profile blocked setup under Twenty Twenty-Five. |
| Fresh first run | PASS | 18 unique Pages and 8 unique Categories created. |
| Parent, slug and template assignments | PASS | Four Farming children used `poultry-hub`; Management and its topic children used `section-hub`. |
| Initial publication safety | PASS | Only Home and Guides published; 16 unfinished Pages remained drafts. |
| Static front/Posts page/permalink | PASS | Home ID 6, Guides ID 19, `/guides/%postname%/`. |
| Slug conflict prevention | PASS | Untracked `/farming/` Page disabled mutation before writes. |
| Owner edit preservation | PASS | Edited Kienyeji introduction survived setup rerun and normal restart. |
| Idempotence | PASS | Second run created no duplicate Pages or Categories. |
| Category relationships | PASS | Kienyeji, Layers, Broilers, Hatching and Feeding relationships all rendered correctly. |
| Multi-category Post | PASS | One Kienyeji + Feeding guide appeared in both hubs. |
| Draft/unrelated exclusion | PASS | Kienyeji draft and unrelated published Post were absent from matching hub queries. |
| Empty state | PASS | Health hub displayed the intended management empty message. |
| New Post automation | PASS | Guides created after setup appeared without editing hub Pages. |
| Deactivation | PASS | Content and routes worked after plugin deactivation. |
| Normal restart persistence | PASS | Content, template, category ID and deactivated state survived without reset. |
| Release ZIP upload/activation | PASS | Installed through normal wp-admin in a bare, non-mounted package profile. |
| Home Page editor save | PASS | Authenticated Gutenberg save appeared on `/`, reopened from Pages > Home, and persisted. |
| Farming overview/editor save | PASS | Authenticated Gutenberg save appeared on `/management/`, reopened and persisted. |
| Four poultry hub editability | PASS | Poultry hub templates include `core/post-content`; Kienyeji owner edit appeared, was saved and reopened. |
| Settings read-back | PASS | Browser REST and WordPress permalink checks confirmed static front page, Posts page and permalink values. |
| Desktop 1440 px | PASS | Four representative routes: one H1, no overflow, no missing images. |
| Tablet 768 px | PASS | Four representative routes: one H1, no overflow, no missing images. |
| Mobile 390 px | PASS | Four representative routes: one H1, no overflow, no missing images. |
| Browser console/page errors | PASS | `consoleIssues` was empty in the full mutation run. |
| WooCommerce/payments/customer behavior | NOT TESTED | Explicitly outside scope. |
| Live production migration | NOT TESTED | No live site or database was accessed. |

## Commands

Run one profile at a time from the project root:

```powershell
.\tests\start-starter-profile.ps1 -Port 9460 -Profile Fresh -Reset
```

In another project terminal:

```powershell
Set-Location .\tests
$env:PF_BASE_URL='http://127.0.0.1:9460'
$env:PF_WP_USER='<temporary-local-admin>'
$env:PF_WP_PASSWORD='<temporary-local-password>'
npm.cmd run test:starter-setup
```

Stop the server normally, then restart the same storage without reset or Blueprint activation:

```powershell
.\tests\start-starter-profile.ps1 -Port 9460 -Profile Fresh -NoBlueprint
```

```powershell
Set-Location .\tests
$env:PF_BASE_URL='http://127.0.0.1:9460'
$env:PF_WP_USER='<temporary-local-admin>'
$env:PF_WP_PASSWORD='<temporary-local-password>'
npm.cmd run test:starter-persistence
```

Conflict and release-package checks:

```powershell
.\tests\start-starter-profile.ps1 -Port 9461 -Profile Conflict -Reset
.\tests\start-starter-profile.ps1 -Port 9462 -Profile Package -NoBlueprint -Reset
```

```powershell
Set-Location .\tests
$env:PF_BASE_URL='http://127.0.0.1:9461'; npm.cmd run test:starter-conflict
$env:PF_BASE_URL='http://127.0.0.1:9462'; npm.cmd run test:starter-package
```

Credentials above are local Playground defaults only and are not stored in production files.

## Relationship fixture

The ignored QA database created published synthetic Posts for Kienyeji + Feeding, Layers, Broilers and Hatching; one Kienyeji draft; and one unrelated published Post. Recorded relationship results were true for all five tested hubs. No fixture Post is included in the plugin ZIP.

## Screenshot inventory

Artifacts are intentionally ignored under `tests/screenshots/starter-setup/`:

- `desktop-1440/setup-preview.png`
- `desktop-1440/{homepage,kienyeji-hub,feeding-hub,guide}.png`
- `tablet-768/{homepage,kienyeji-hub,feeding-hub,guide}.png`
- `mobile-390/{homepage,kienyeji-hub,feeding-hub,guide}.png`
- `diagnostics.json` and one `viewport-diagnostics.json` per viewport

All 12 frontend captures came from valid rendered WordPress pages after plugin deactivation. Each diagnostic reported `overflow: 0`, `h1Count: 1`, and an empty `missingImages` array.

## Findings and corrections during QA

1. WordPress 7.1.4 and 7.1.5 supported fresh installs use verified sample fixtures. The preflight accepts the standard date permalink or `/%postname%/` only with the exact core and Privacy Policy signatures.
2. The editor test now updates Gutenberg through the core block-editor store and verifies the saved REST representation.
3. Plugin state checks use accessible action names instead of an unavailable `data-slug` selector.
4. Playground Package runs execute from an empty isolated temp directory because the CLI otherwise auto-mounts the working directory.
5. Windows backslash ZIP entries caused a verified WordPress installation failure. The builder now writes portable forward-slash entries; the rebuilt ZIP passed upload and activation.
6. Independent review found that the theme front-page template did not render Home Page content. `core/post-content` was added to the approved introduction section, preserving the theme H1 and eliminating a public placeholder message.
7. The preflight was tightened to reject additional custom/editorial records and modified default samples with specific reasons. It continues to ignore ordinary WordPress internal records.
8. Site settings are now read back and setup is only marked complete after every expected value matches.
9. Initial setup now blocks non-default Reading settings (`show_on_front`, `page_on_front`, `page_for_posts`) before any mutation.
10. The Privacy Policy is verified with a URL-normalized exact fixture fingerprint for WordPress 7.1.4/7.1.5 en_US; modified content blocks setup.

No confirmed production theme defect was found.

## Remaining limitations

- The component intentionally leaves WordPress’s untouched Hello World, Sample Page and draft Privacy Policy for owner review; remove or retain them deliberately before launch.
- The Page copy is starter structure, not a substitute for editorial approval.
- Production redirect/migration requirements were not assessed because no live site was accessed.
- Automated browser checks are not an accessibility certification.

## Final preflight correction and release

The final source separates initial, verified-partial and completed setup. Untracked custom permalinks are conflicts regardless of the textual rejection reason. Core sample validation uses exact supported fixture content fingerprints rather than substring presence. The owner editing boundary is documented in `docs/OWNER_CONTENT_EDITING_GUIDE.md`: Pages > Home stores introductory copy; Appearance > Editor controls reusable site parts; theme templates still control the approved hero, layout, queries and H1.

The source exposes controlled local checkpoints after Category creation, after Page creation and before settings completion. A consistent manifest resumes safely; unknown records are never adopted. The remaining narrow interruption window is after settings read-back and before the completion-manifest write.

Final package:

- File: `releases/poultryfarmer-starter.zip`
- Size: 11,824 bytes
- SHA-256: `6362e27871e4034f446d3b18ec790e50228990a5810a8293ff72b6618e7d4b67`
- Entries: `poultryfarmer-starter/poultryfarmer-starter.php`, `poultryfarmer-starter/readme.txt`

The ZIP was rebuilt with explicit forward-slash paths. The local WordPress 7.1.4 / PHP 8.3 runtime loaded and executed the patched plugin successfully. Native `php -l` was unavailable because no `php.exe` was installed on the review host. The previous login hang was resolved by using separate ports and clean isolated profiles; the focused preflight, Reading/Privacy, positive setup and package upload tests all completed successfully.

## Final release recommendation

Stop for independent review. Do not deploy, push or commit. Existing positive functional, package, editor, persistence, deactivation and responsive checks remain retained; WooCommerce/payment behavior and live production migration remain outside scope.
