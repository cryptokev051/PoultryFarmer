# Poultry Farmer Phase 2B2 Editor Workflow QA

Date: 19 November 2026  
Environment: isolated Profile B, WordPress 7.1.4, PHP-WASM 8.3, no WooCommerce  
Result: **PASS with one documented non-blocking WordPress editor warning**

## Outcome

Authenticated Site Editor, Page editor and Post editor operations were completed in the actual WordPress block editor. Synthetic Pages and Posts were saved, reopened, previewed and, where required, published only in the isolated local fixture. A normal Playground stop/start using the same generated site directory preserved the content.

No homepage redesign, WooCommerce change, calculator, deployment or Git commit was performed.

## Authentication and reproducible access

The Playground CLI `--login` option did not transfer an authenticated session to a separately launched project-local Chromium context. The test therefore used the standard temporary local Playground administrator through `/wp-login.php`. Credentials were supplied as process-scoped environment values and were not written to project files, reports or Git candidates.

Start the preserved profile from the project root:

```powershell
.\tests\start-profile-b.ps1 -Port 9450
```

In a second PowerShell session, supply temporary local credentials and run the editor suite:

```powershell
Set-Location .\tests
$env:PF_BASE_URL = 'http://127.0.0.1:9450'
$env:PF_WP_USER = Read-Host 'Temporary local WordPress user'
$env:PF_WP_PASSWORD = Read-Host 'Temporary local WordPress password'
npm.cmd run test:phase2b2-editor
Remove-Item Env:PF_WP_USER, Env:PF_WP_PASSWORD, Env:PF_BASE_URL
```

For persistence, stop Profile B normally, restart the same command without `-Reset`, then run `npm.cmd run test:phase2b2-persistence` with the same process-scoped variables. Do not reset the profile for this check.

## Test matrix

| Area | Result | Verified evidence |
|---|---|---|
| Administrator login | PASS | Normal login form reached Dashboard with authenticated admin bar. |
| Site Editor | PASS | `/wp-admin/site-editor.php` loaded Design, Patterns and Templates. |
| Page and Post editors | PASS | The real block editor completed save/reopen operations. |
| Poultry Type Hub | PASS | Listed in editor; draft retained title, excerpt, Query Loop and `poultry-hub`. |
| Editorial Section Hub | PASS | Listed in editor; draft retained content and `section-hub`. |
| Hub preview | PASS | Valid Page with global header/footer, one H1 and theme typography. |
| Query Loop persistence | PASS | Kienyeji category filter remained after reopen. |
| Query No Results | PASS | Empty category rendered the intended truthful empty state. |
| Category relationships | PASS | Four hubs showed only matching published synthetic guides. |
| Draft visibility | PASS | Kienyeji draft was excluded from public hub results. |
| Uncategorized isolation | PASS | Uncategorized-only Post did not become a topic-related guide. |
| Pattern registration | PASS | Authenticated endpoint returned 21 `poultryfarmer/*` patterns. |
| Five new patterns | PASS | Inserted, edited, saved, reopened and rendered. |
| Sources & Methodology pattern | PASS | Inserted, edited, saved, reopened and rendered. |
| Guide publishing workflow | PASS | Title, slug, excerpt, category, author, featured image, source pattern and table persisted. |
| Published/updated date | PASS | Updated appeared only after a later saved edit. |
| Author/reviewer behavior | PASS | Named synthetic author and biography rendered; no empty author box or reviewer claim. |
| Related and previous/next guides | PASS | Category-related cards and native chronological navigation rendered. |
| Guides archive and pagination | PASS | Guide appeared at `/guides/`; `/guides/page/2/` was valid. |
| Search | PASS | Matching and empty searches returned truthful application pages. |
| Reading settings | PASS | Admin confirmed static front page and Guides Posts page. |
| Permalinks | PASS | Admin confirmed and saved `/guides/%postname%/`. |
| Restart persistence | PASS | Hub, pattern draft and guide reopened after a normal no-reset restart. |

## Browser and responsive results

| Viewport | Result | Document overflow | Table overflow | Blocking console errors |
|---|---|---:|---|---:|
| 1440 × 1000 | PASS | 0px | `auto` | 0 |
| 768 × 1024 | PASS | 0px | `auto` | 0 |
| 390 × 844 | PASS | 0px | `auto` | 0 |

Each viewport rendered one H1 and a logical H2/H3 hierarchy. The featured image loaded, related cards rendered and no missing image or broken tested route was found.

Evidence directory: `tests/screenshots/phase2b2-editor-workflow/`

- `desktop-1440/poultry-hub-draft-preview.png`
- `desktop-1440/published-workflow-guide.png`
- `tablet-768/published-workflow-guide.png`
- `mobile-390/published-workflow-guide.png`
- `workflow-diagnostics.json`

## Errors and warnings

- PHP fatal/parse/critical errors: **0 found** in the isolated runtime scan.
- Blocking browser console/page errors: **0**.
- Non-aborted request failures: **0**.
- The editor generated expected `net::ERR_ABORTED` events when the test intentionally navigated away while background REST/blob preview requests remained pending. These were separated from real request failures.
- WordPress repeatedly warned that `global-styles-css-custom-properties-inline-css` was added to the iframe incorrectly. It did not affect editing or rendering. The theme does not reference or enqueue that handle, so no speculative production change was made.

## Test harness changes

- Added `tests/browser/editor-workflow.spec.mjs`.
- Added `tests/browser/editor-persistence.spec.mjs`.
- Added `test:phase2b2-editor` and `test:phase2b2-persistence` npm scripts.
- Evidence is stored under ignored `tests/screenshots/phase2b2-editor-workflow/` so it persists between test invocations.

## Limitations

- Synthetic runtime content is not production content and must not be exported as editorial copy.
- The test used one local administrator; Contributor, Author and Editor role permissions were not tested.
- The WordPress iframe warning should be rechecked when the runtime version changes.
- WooCommerce, customer accounts, payments and calculators remain outside Profile B.
- Automated checks are not an accessibility certification.

