# Poultry Farmer Phase 2B2 Visual QA

Date: 19 November 2026  
Environment: stable Profile B, WordPress 7.1.4, PHP 8.3, no WooCommerce  
Result: **PASS with documented limitations**

## Test run

The final Playwright command ran all viewport projects sequentially with one worker:

```powershell
Set-Location D:\Projects\PoultryFarmer\tests
$env:PF_BASE_URL = 'http://127.0.0.1:9450'
npm.cmd run test:phase2b2
```

Final result: `3 passed (3.6m)`.

Before browser QA, ten sequential HTTP requests across the homepage, article, Farming overview and poultry-type hubs returned valid HTTP 200 WordPress pages. No maintenance file existed after initialization. The local debug log contained only the expected automatic-update-disabled confirmation.

## Viewports

| Project | CSS viewport | Browser result | Menu |
|---|---:|---|---|
| desktop-1440 | 1440 × 1000 | PASS | Desktop navigation rendered |
| tablet-768 | 768 × 1024 | PASS | Open/close PASS |
| mobile-390 | 390 × 844 | PASS | Open/close PASS |

Chromium: 159.0.8154.6 via Playwright 1.65.1.

## Page matrix

Every row passed at all three viewport widths.

| Page/state | Route | Desktop | Tablet | Mobile |
|---|---|---|---|---|
| Homepage regression | `/` | PASS | PASS | PASS |
| Farming overview | `/farming/` | PASS | PASS | PASS |
| Kienyeji hub | `/farming/kienyeji/` | PASS | PASS | PASS |
| Layers hub | `/farming/layers/` | PASS | PASS | PASS |
| Broilers hub | `/farming/broilers/` | PASS | PASS | PASS |
| Hatching hub | `/farming/hatching/` | PASS | PASS | PASS |
| Editorial article | `/guides/how-to-start-poultry-farming-kenya/` | PASS | PASS | PASS |
| Guides archive | `/guides/` | PASS | PASS | PASS |
| Search results | `/?s=poultry` | PASS | PASS | PASS |
| Empty related state | `/guides/hatching-preparation-checklist/` | PASS | PASS | PASS |

Each tested page returned one visible main H1, no skipped H1–H3 heading level, no maintenance/error signature and no horizontal document overflow.

## Interaction and rendering results

| Check | Result | Notes |
|---|---|---|
| Homepage visual regression | PASS | Approved Phase 2B1 identity and section architecture retained; links point to the new hierarchy. |
| Desktop navigation | PASS | Canonical Farming, Management, Prices, Guides, Calculators and Shop routes render. |
| Tablet/mobile menu | PASS | Native block navigation opens and closes. |
| Search | PASS | Header search returns a valid local result page. |
| Keyboard focus | PASS | Tab moves to an interactive control with a visible focus treatment. |
| Internal links | PASS | 21 unique desktop fixture links checked; 0 broken/error pages. |
| Card responsiveness | PASS | Hub and archive cards stack without clipping. |
| Reading width | PASS | Article content measured 760px desktop, 721.94px tablet and 354px mobile. |
| Comparison table | PASS | Horizontal scrolling enabled; mobile swipe prompt visible. |
| Author attribution | PASS | Named synthetic author and non-empty biography render; no empty profile. |
| Related guides | PASS | Category-relevant cards only. |
| Empty related content | PASS | Truthful message and Guides link render. |
| Images | PASS | 0 missing images after explicit lazy-image readiness check. |
| Horizontal overflow | PASS | 0 routes exceeded the viewport by more than 1px. |
| Browser console | PASS | 0 errors/page errors. |
| Browser requests | PASS | 0 failed requests. |
| PHP/runtime errors | PASS | No PHP warning/fatal in the isolated debug log. |

The mobile lazy-image check initially ran before a below-the-fold SVG had loaded. The harness now changes lazy test images to eager and waits for load/error before classification; the rerun passed. This was a test timing correction, not an asset replacement.

## Screenshot inventory

Directory: `tests/screenshots/phase2b2/`

- Desktop: 12 PNG files, 2,990,963 bytes.
- Tablet: 12 PNG files, 2,280,844 bytes.
- Mobile: 12 PNG files, 10,076,144 bytes (device-scale-factor output).
- Total: 36 PNG files, 15,347,951 bytes.
- One `diagnostics.json` file is stored beside each viewport set.

Each viewport directory contains:

- `homepage.png`
- `farming-overview.png`
- `kienyeji-hub.png`
- `layers-hub.png`
- `broilers-hub.png`
- `hatching-hub.png`
- `editorial-article.png`
- `guides-archive.png`
- `search-results.png`
- `empty-related-state.png`
- `header.png`
- `footer.png`

Representative files:

- `tests/screenshots/phase2b2/desktop-1440/homepage.png`
- `tests/screenshots/phase2b2/desktop-1440/editorial-article.png`
- `tests/screenshots/phase2b2/tablet-768/farming-overview.png`
- `tests/screenshots/phase2b2/mobile-390/kienyeji-hub.png`
- `tests/screenshots/phase2b2/mobile-390/editorial-article.png`

## Visual review findings

- The approved forest-green, harvest-gold, warm-white and charcoal system remains consistent.
- The logo remains sharp and proportionate at all widths.
- Farming overview and poultry-type hubs are visually distinct from the homepage and reuse the approved publication language.
- Long hub headings wrap without clipping.
- Article breadcrumbs, standfirst, source notes, author profile and related cards retain a coherent reading hierarchy.
- Wide article tables remain usable on mobile through an explicit scroll container.
- Header and footer remain consistent across Pages, archives, search and Posts.
- No maintenance, proxy, critical-error or WordPress error page was accepted as a screenshot.

## Accessibility scope

Automated assertions covered one H1, heading progression, keyboard focus visibility, menu controls, image loading, responsive overflow and table scrolling. Manual screenshot review covered readable hierarchy and obvious clipping. This is not WCAG certification and does not replace screen-reader, zoom, high-contrast or broader assistive-technology testing.

## NOT TESTED / out of scope

- Authenticated Site Editor and protected pattern endpoint: PASS in the subsequent final workflow pass; see `docs/PHASE2B2_EDITOR_WORKFLOW_QA.md`.
- WooCommerce pages, products, cart, checkout, account and payments: NOT TESTED in Profile B.
- Calculator behavior: NOT IMPLEMENTED.
- Production content accuracy, redirects, SEO-plugin output and schema: NOT TESTED.
- Real featured-image editorial workflow and image licensing: NOT TESTED with production assets.

## Recommendation

**PASS for independent visual review.** The Phase 2B2 hubs and editorial system render consistently at 1440px, 768px and 390px without a homepage regression. Approval should cover structure and presentation only; fixture copy is not production content.

