# Poultry Farmer Phase 2B Readiness

Phase 2A established an isolated, identity-migrated block-theme copy without redesigning the homepage or implementing future functionality. Static closeout verification is complete. Phase 2B can focus on approved visual and information-architecture work only after the missing logo package and runtime QA prerequisites below are resolved.

## Required inputs before visual work

- The approved Poultry Farmer logo files: light/dark horizontal variants, icon/favicon, and any required transparent PNG/SVG forms. No `brand-assets/` package was present during closeout; logo integration is therefore BLOCKED.
- Final forest-green/harvest-gold contrast decisions and typography direction.
- Final page/taxonomy URL model and content inventory.
- A safe local/staging WordPress + WooCommerce environment for rendering checks.
- Independent review of [PHASE2A_FINAL_VERIFICATION.md](PHASE2A_FINAL_VERIFICATION.md), including the 69-file source snapshot and release inspection evidence.

## Specific templates and parts for visual adaptation

### Global identity

- `poultryfarmer/style.css`: final metadata and author/license presentation.
- `poultryfarmer/theme.json`: palette names/values, shadow, typography, layout widths, button/link defaults, and site-title/navigation styles.
- `poultryfarmer/parts/header.html`: supplied logo, final navigation hierarchy, search/cart/account treatment, and CTA.
- `poultryfarmer/parts/footer.html`: final footer columns, legal links, newsletter/support treatment, and copyright copy.

### Homepage and content discovery

- `poultryfarmer/templates/front-page.html`: Poultry Farmer value proposition, Start Here, Kienyeji/Layers/Broilers/Hatching entry points, Farm Management links, Prices & Markets, Calculators, latest guides, and Shop CTA. Preserve current block/query structure while changing content hierarchy deliberately.
- `poultryfarmer/templates/home.html`, `index.html`, `archive.html`, `category.html`: guide/archive labels, hub taxonomy presentation, pagination, empty states, and editorial metadata.
- `poultryfarmer/templates/search.html`: search wording, content types, result labels, and no-results guidance.
- `poultryfarmer/templates/404.html`: poultry-specific recovery copy and safe links.

### Editorial and reusable patterns

- `poultryfarmer/templates/single.html`: article/guide terminology, source/methodology placement, author box, related guides, and navigation.
- `poultryfarmer/patterns/related-analysis.php`: likely rename presentation terminology to “related guides” consistently while retaining the portable filter boundary.
- `poultryfarmer/patterns/assumptions.php`, `key-numbers.php`, `sources-methodology.php`, `data-limitations.php`, `risk-radar.php`, `compare-scenarios.php`, `sensitivity-analysis.php`, `next-decision.php`: adapt examples and labels to poultry farming; keep calculator/source behavior plugin-owned.
- `poultryfarmer/patterns/calculator-bridge.php`: convert to a future plugin integration slot only after calculator contracts are approved.
- `poultryfarmer/patterns/toolkit-cta.php`, `poultryfarmer/patterns/newsletter.php`, `poultryfarmer/patterns/poultryfarmer-updates.php`: final digital-product/newsletter copy and provider block/shortcode insertion.

### WooCommerce presentation

- `poultryfarmer/templates/archive-product.html`: final `/shop/` intro and digital-product merchandising.
- `poultryfarmer/templates/single-product.html`: product detail hierarchy, downloads, reviews/metadata decision, and product content presentation.
- `poultryfarmer/templates/page-cart.html`, `page-checkout.html`, `page-my-account.html`: final trust/legal/support copy and block styling.
- `poultryfarmer/inc/woocommerce.php`: deliberate review of removed meta/tabs/upsells/related products, digital download wording, order-access messaging, and classic checkout fallback.

## CSS sections for Phase 2B

`poultryfarmer/assets/css/theme.css` sections to review include:

- header/site-brand/navigation/search/mobile overlay rules near the beginning and responsive menu rules around the 1024px breakpoint;
- hero, card, grid, archive, search, article, author, pagination, and 404 sections;
- editorial patterns: verdict, key numbers, evidence, risk, scenario, methodology, and CTA sections;
- newsletter/update surfaces and form-slot styling;
- WooCommerce archive/product/account/download/order-access rules near the commerce section;
- checkout field, validation, order summary, focus, notice, and responsive rules;
- breakpoints at 1024px, 900px, 782px, 600px, and 390px;
- `prefers-reduced-motion` and global `:focus-visible` rules.

Phase 2B should preserve responsive behavior, keyboard focus, reduced-motion handling, table overflow behavior, and WooCommerce block selectors until browser QA demonstrates an equivalent replacement.

## Readiness status

- Identity isolation: READY for review.
- Logo integration: BLOCKED pending supplied assets; `poultryfarmer/assets/images/brand/` is prepared but empty.
- Full palette/typography redesign: RESERVED for Phase 2B.
- URL/navigation content: PENDING owner-approved page/taxonomy model.
- Calculator UI/business logic: OUT OF SCOPE; future `poultryfarmer-core` plugin.
- PHP syntax/static/package validation: PASS at closeout.
- WordPress/WooCommerce runtime validation: NOT TESTED; required before visual sign-off.
- Phase 2B implementation: NOT STARTED; this report only identifies future adaptation targets.
