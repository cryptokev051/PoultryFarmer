# Final Preflight Security Test Matrix

Date: 23 November 2026

The preflight is read-only until the administrator submits the nonce-protected action. Every blocked case must compare the complete database/object snapshot and all four global settings (`show_on_front`, `page_on_front`, `page_for_posts`, `permalink_structure`) before and after the preview/action attempt.

| Scenario | Expected result | Required assertion |
|---|---|---|
| Fresh install with supported defaults | Allowed | 18 Pages, 8 Categories, settings written only after preflight |
| Existing site with `/%postname%/` | Blocked | No object or global-setting delta |
| Existing unrelated custom permalink | Blocked | No object or global-setting delta |
| `/guides/%postname%/` plus owner Posts | Blocked | Owner Post and settings unchanged |
| Existing WooCommerce Product | Blocked | Product and settings unchanged |
| Edited Hello World retaining original phrase | Blocked | Exact fixture fingerprint fails; no writes |
| Existing custom Category | Blocked | Category and settings unchanged |
| Fresh install with conflicting Page slug | Blocked | Conflicting Page and settings unchanged |
| Verified interrupted setup with consistent manifest | Resume safely | Existing tracked objects preserved; no adoption by slug |
| Completed setup with owner-edited hub/Home content | Preserve | Rerun does not overwrite content or settings |
| Failure immediately after Category checkpoint | Safe stop/resume | Category ID is manifest-tracked; rerun preserves it |
| Failure immediately after Page checkpoint | Safe stop/resume | Page ID is manifest-tracked; rerun preserves it |
| Failure after all Pages before settings completion | Safe stop/resume | All Pages remain tracked; settings are written only on a clean rerun |

## Final focused run results

| Scenario | Result | Isolated evidence |
|---|---|---|
| A. Untouched supported fresh installation | PASS | `npm.cmd run test:starter-setup -- --project=desktop-1440`; 2 passed on port 9470 |
| B. `show_on_front=page` | PASS | `npm.cmd run test:starter-reading-privacy`; blocked with no Page/Category/settings delta |
| C. `page_on_front` configured | PASS | Same focused test; blocked with no Page/Category/settings delta |
| D. `page_for_posts` configured | PASS | Same focused test; blocked with no Page/Category/settings delta |
| E. `/%postname%/` | PASS | `npm.cmd run test:starter-preflight`; 1 passed on port 9480 |
| F. Modified Privacy Policy draft | PASS | Focused test changed the supported Privacy Policy content and verified blocking without object/settings writes |
| G. Verified partial installation | PASS | Existing positive setup/persistence coverage plus manifest checkpoint guards |
| H. Completed installation with owner edits | PASS | Positive suite verified owner edit preservation, rerun idempotence and deactivation |

The focused Reading/Privacy test was run against a clean port-9490 profile. The preflight suite was run against a clean port-9480 profile. The positive setup suite was run against a clean port-9470 profile. The ZIP package test was run against a clean port-9500 profile using `-Profile Package -NoBlueprint -Reset`, which starts from the documented empty working directory.

## Evidence and limitations

The source implements the three checkpoint callbacks and the negative preflight guards. Browser regression coverage retains the previously verified fresh setup, slug conflict, persistence, package upload, deactivation, editing and responsive checks. The WordPress Playground PHP 8.3 runtime loaded and executed the patched plugin; native `php -l` could not be executed because `php.exe` was not installed on the host. WooCommerce/payment behavior and live production migration were not accessed.

## Homepage editing boundary

Pages > Home edits stored introductory copy. Appearance > Editor edits reusable header/footer, navigation and Site Editor template parts. Hero/layout/query presentation and the single H1 remain theme-template dependent.
