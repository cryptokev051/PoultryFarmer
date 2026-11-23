# PoultryFarmer.co.ke Theme Audit and Adaptation Plan

**Phase:** 1 — audit only  
**Workspace audited:** `D:\Projects\AgriRadar - Copy`  
**Date:** 2026-11-18  
**Scope:** Read-only inspection of the copied workspace. No source files, database, production site, store settings, dependencies, or deployments were changed. This report is the only new file created for this task.

## Executive summary

The workspace is a copy of the AgriRadar project, not the live site. It contains two related theme trees:

- `AgriRadar Theme/`: the current release-oriented native block theme, with WooCommerce templates and the strongest reusable implementation.
- `theme/agriradar/`: an older/parallel source tree retained in the repository; its Git state shows legacy classic files removed and block-theme files added. It should not be treated as a second implementation target without an owner decision.

The current `AgriRadar Theme/` package is a genuine WordPress block theme: it uses HTML templates, HTML template parts, `theme.json`, PHP patterns, and a small PHP bootstrap. It has no theme JavaScript. The visual system is already mobile-aware, uses responsive CSS and reduced-motion handling, and has broad focus-visible styling. The best reuse path is to preserve the block-theme architecture, layout primitives, editorial article/archive/search structures, and WooCommerce presentation while creating an independent Poultry Farmer identity and removing AgriRadar-specific assumptions.

The largest adaptation risks are identity leakage, hardcoded navigation paths and copy, the AgriRadar text domain/function prefix/class names, WooCommerce assumptions built around downloadable digital toolkits, and the fact that the companion plugin is also explicitly named AgriRadar. The theme does not declare a hard dependency on the plugin or WooCommerce, although several templates and features require WooCommerce at runtime. The plugin declares PHP 8.3 while the theme declares PHP 7.4, so the final combined compatibility baseline must be decided before implementation.

No payment credentials, API keys, analytics pixels, advertising tags, or external runtime scripts were found in the inspected theme/plugin source. Local QA scripts reference local test credentials and WooCommerce settings; those scripts are not production theme files and must not be packaged or reused as operational configuration.

### Top five recommendations

1. Use `AgriRadar Theme/` as the only Phase 2 source baseline; quarantine the parallel `theme/agriradar/` tree from the Poultry Farmer release process.
2. Establish a complete identity boundary before visual work: theme directory/name, text domain, PHP prefixes, CSS classes, pattern slugs, asset names, navigation copy, URLs, and plugin-facing metadata.
3. Keep the block-theme layout and editorial components, but rebuild the homepage, navigation, hub information architecture, and product copy for poultry farming rather than mechanically replacing words.
4. Keep all calculators, farm records, community, member, supplier, and integration logic in a future `poultryfarmer-core` plugin; the theme should render portable blocks, templates, and presentation states only.
5. Make WooCommerce compatibility an explicit acceptance gate: support the current block cart/checkout presentation, preserve HPOS-safe CRUD usage, and test the chosen product/download/payment model on staging before any production connection.

## Existing theme inventory

### Theme metadata and architecture

Source: `AgriRadar Theme/style.css`, `AgriRadar Theme/theme.json`, `AgriRadar Theme/functions.php`.

| Item | Confirmed value |
|---|---|
| Theme name | AgriRadar |
| Version | 0.4.0 |
| Author | Kevin K. |
| Theme URI | `https://agriradar.co.ke/` |
| Text domain | `agriradar` |
| WordPress requirement | 6.6+ |
| Theme PHP requirement | 7.4+ |
| Theme type | Native block theme; HTML templates and parts are present |
| Declared theme dependencies | None in `style.css` |
| Soft runtime dependencies | WooCommerce for WooCommerce blocks/templates and product/account/cart presentation; the AgriRadar Core plugin for its editorial/source/block features, but no explicit theme dependency check exists |

`theme.json` is version 3 and defines layout sizes, colors, spacing, typography, borders, shadows, element styles, and header/footer template parts. `functions.php` loads `inc/setup.php`, `inc/assets.php`, `inc/patterns.php`, `inc/archives.php`, `inc/related-analysis.php`, and `inc/woocommerce.php`.

The older repository tree also contains `theme/agriradar/`. The root `README.md` describes that tree as the classic PHP theme, while `ARCHITECTURE.md` says the classic/hybrid 0.1.0 architecture is superseded by the native block theme. The current `AgriRadar Theme/` package is the coherent release source because it contains the complete commerce templates, current README, release builder, and publishing documentation.

### PHP bootstrap and rendering responsibilities

- `AgriRadar Theme/inc/setup.php`
  - `agriradar_setup()` adds `wp-block-styles` support and registers `assets/css/theme.css` as the editor stylesheet.
- `AgriRadar Theme/inc/assets.php`
  - `agriradar_enqueue_assets()` enqueues one production stylesheet, versioned from the theme version.
- `AgriRadar Theme/inc/patterns.php`
  - `agriradar_register_pattern_categories()` registers the AgriRadar pattern category.
- `AgriRadar Theme/inc/archives.php`
  - `agriradar_render_general_archive_description()` supplies author/general archive descriptions through a block render filter.
  - `agriradar_excerpt_length()` sets front-end excerpts to 26 words.
  - `agriradar_blog_posts_per_page()` sets the main blog query to 9 posts.
  - `agriradar_render_card_image_fallback()` supplies a neutral SVG placeholder for selected card classes when no featured image exists.
- `AgriRadar Theme/inc/related-analysis.php`
  - `agriradar_render_related_analysis_card()` renders related article cards.
  - `agriradar_render_related_analysis()` queries up to three same-category posts, falls back to recent posts, and exposes the `agriradar_related_analysis_posts` filter for plugin-controlled relationships.
- `AgriRadar Theme/inc/woocommerce.php`
  - Declares WooCommerce support.
  - Redirects former `/business-tools` URLs to `/agribusiness-kits`.
  - Changes single-product hooks, replaces product details with `agriradar_woocommerce_product_details()`, changes add-to-cart labels, adds archive summaries, orders account endpoints, renders a thank-you/order-access panel, and changes the classic checkout phone label/type.

### Templates and parts

`AgriRadar Theme/templates/` contains:

- `front-page.html`: hero, entry points, latest analysis query, product/toolkit query, search section, newsletter pattern.
- `home.html`, `index.html`: article/blog and general archive grids with pagination.
- `category.html`, `archive.html`, `search.html`: category, general archive, and search result layouts.
- `single.html`: article header, standfirst, author box, related-analysis pattern, post navigation.
- `page.html`, `404.html`: standard page and not-found layouts.
- `archive-product.html`, `single-product.html`: WooCommerce product archive and product detail presentation; both use WooCommerce legacy-template blocks inside block-theme shells.
- `page-cart.html`, `page-checkout.html`, `page-my-account.html`: commerce page wrappers. Cart/account use page content; checkout uses a dedicated header/footer and page content, allowing WooCommerce blocks to be placed in the assigned page.

`AgriRadar Theme/parts/header.html` provides site logo/title, native responsive Navigation block with mobile overlay, search, WooCommerce mini-cart, and a “Get The Radar” CTA. `parts/footer.html` provides four-column editorial/business/customer/legal navigation, brand copy, and legal links.

### Patterns and reusable components

`AgriRadar Theme/patterns/` includes reusable, editor-insertable patterns for:

- editorial evidence: `assumptions.php`, `data-limitations.php`, `sources-methodology.php`, `evidence-note.php`;
- analysis structure: `agriradar-verdict.php`, `key-numbers.php`, `compare-scenarios.php`, `sensitivity-analysis.php`, `risk-radar.php`, `next-decision.php`, `chart-container.php`;
- conversion/continuation: `calculator-bridge.php`, `related-analysis.php`, `toolkit-cta.php`, `get-the-radar.php`, `newsletter.php`.

These are mostly static block compositions. Dynamic related articles are inserted by `inc/related-analysis.php`; source-backed blocks are supplied by the companion plugin.

### CSS, JavaScript, assets, and build tools

- Main stylesheet: `AgriRadar Theme/assets/css/theme.css` (~2,900 lines; global layout, header/menu, cards, article/archive/search, editorial patterns, commerce, checkout, account/downloads, responsive breakpoints, focus states, and reduced motion).
- Theme has no front-end JavaScript file. Mobile behavior relies on the core WordPress Navigation block.
- Brand assets are under `AgriRadar Theme/assets/images/brand/`: AgriRadar SVG/PNG logo variants and favicon. `assets/images/analysis-placeholder.svg` is the card fallback.
- Root build tooling is in `package.json`, `scripts/build-blocks.mjs`, and `node_modules/`; it bundles companion-plugin editor blocks with esbuild. There is no CSS preprocessor or theme JS build pipeline.
- `AgriRadar Theme/scripts/build-release.ps1` packages only production theme files and blocks known development/sensitive paths and credential-shaped text. It writes an AgriRadar-named ZIP and should be adapted only after the identity decision.
- Development tooling includes Node tests, Playwright/WordPress Playground dependencies, PHP lint tooling via Composer, and local smoke/packaging scripts. No dependency installation or tests were run for this audit.

## AgriRadar-specific dependencies and migration risks

### Confirmed identity and branding dependencies

The following require change for an independent Poultry Farmer site:

- `AgriRadar Theme/style.css`: theme name, URI, author/description, text domain, and package identity.
- `AgriRadar Theme/theme.json`: palette names and colors include `Agri Green`, `Radar Dark`, `Signal Lime`; the palette values need a deliberate forest-green/harvest-gold design decision.
- `AgriRadar Theme/functions.php` and all `inc/*.php`: `agriradar_*` function names and package comments.
- `AgriRadar Theme/assets/images/brand/*`: all AgriRadar logo files and favicon.
- `AgriRadar Theme/parts/header.html`, `parts/footer.html`, `templates/*.html`, and patterns: visible AgriRadar copy, “Data. Markets. Opportunities.”, “Get The Radar”, “Agribusiness Kits”, `/blog/`, `/category/market-analysis/`, `/agribusiness-kits/`, and other hardcoded paths.
- `AgriRadar Theme/patterns/*.php`: `agriradar/*` pattern slugs and `agriradar` translation domain.
- CSS selectors and variables throughout `assets/css/theme.css`: `agriradar-*` classes, `--agriradar-header-height`, and commerce classes.
- `AgriRadar Theme/inc/woocommerce.php`: legacy `/business-tools` → `/agribusiness-kits` redirect, toolkit labels, “Toolkit details”, “My downloads”, and hardcoded support URL `/contact/`.
- `AgriRadar Theme/templates/archive-product.html`: “Agribusiness kits” copy and product assumptions.
- `AgriRadar Theme/README.txt`, release scripts, local QA scripts, and documentation: AgriRadar package names and local test URLs/identifiers.
- `AgriRadar Theme/SOURCE_BASELINE.md`: historical source notes reference Freelancian and AgriRadar; this is documentation, not runtime code, but it should not ship as Poultry Farmer documentation.

### Plugin-specific dependencies

`plugins/agriradar-core/agriradar-core.php` declares:

- Plugin name: AgriRadar Core
- Version: 0.2.0
- WordPress: 6.6+
- PHP: 8.3+
- Text domain: `agriradar-core`
- No WooCommerce or payment dependency in the bootstrap comment or loaded modules.

The plugin registers or manages:

- private `agriradar_source` post type in `includes/sources.php`, with protected provenance metadata and source citations;
- relationship metadata for sources, products, calculator pages, and research posts in `includes/relationships.php`;
- editor metaboxes, source lookup REST routes in `includes/editor-lookups.php`, reference indexing in `includes/reference-index.php`, and a migration helper in `includes/migration.php`;
- editor-only/custom blocks registered in `includes/blocks.php`, with built bundles under `build/blocks/`;
- PHP-registered editorial patterns from `patterns/` through `includes/editorial.php`;
- calculator manifest/registry and a demo-only `goat-feedlot@0.1.0-demo` pure module under `calculators/`.

The plugin does not register a poultry post type, taxonomy, calculator UI, farm records, accounts, community, suppliers, or payment integration. Those are future product requirements, not existing functionality.

### Domains, links, services, analytics, and secrets

Confirmed hardcoded domains/links include the theme URI `https://agriradar.co.ke/`, the WordPress schema URLs in `theme.json`/block metadata, and root-relative internal links in templates/parts. Runtime source scans found no analytics pixels, advertising tags, Google/Meta tracking, external font loads, API calls, payment SDKs, webhooks, or email transport code in the theme/plugin.

WooCommerce local scripts inspect M-PESA settings by option key and report only safe presence/flags; they do not embed gateway credentials. `AgriRadar Theme/scripts/woocommerce-post-payment-smoke.cjs` contains a local QA credential literal. Its value is intentionally omitted here; it must remain outside production packaging and should be replaced with environment-provided credentials in future QA work.

No API keys, private keys, live credentials, or secret values were copied into this report. Presence of credentials in a connected WordPress database was not tested, and production was not contacted.

## Theme responsibilities vs future plugin responsibilities

### A. Poultry Farmer theme

Keep the theme responsible for presentation and portable WordPress rendering:

- block-theme templates and template parts;
- global design tokens and CSS;
- header, mobile navigation, search, footer, and legal-link presentation;
- homepage, Start Here, hub, archive, search, article, author, and 404 layouts;
- reusable article/editorial patterns;
- topic hub card/grid presentation;
- WooCommerce archive, product, cart, checkout, order-confirmation, and account styling/presentation;
- empty states, focus states, responsive layout, and accessibility-oriented presentation.

The theme may use standard WordPress/WooCommerce hooks for presentation, but should not own business data, calculations, farm records, community permissions, or payment logic.

### B. Future Poultry Farmer functionality plugin

Place the following in a portable `poultryfarmer-core` (or owner-approved equivalent) plugin:

- financial calculator engines, versioned formulas, validation, methodology and accessible presentation adapters;
- custom data storage and relationships;
- farm record-keeping and management workflows;
- farmer questions/answers, discussions, profiles, supplier information, and moderation/business rules;
- APIs, integrations, notifications, analytics adapters, and any scheduled data processing;
- structured poultry taxonomies/post types only where core WordPress types and taxonomies are insufficient.

The plugin should expose stable blocks, shortcodes only when necessary, REST contracts, and filters that remain usable if the active theme changes.

## Poultry Farmer component structure

Recommended target information architecture:

- **Header:** Poultry Farmer logo, Start Here, Farming Hubs, Farm Management, Prices & Markets, Calculators, Guides, Shop, account/cart, search.
- **Homepage:** clear promise using “Better Decisions. Better Farming.”; four poultry enterprise hubs; practical farm-management entry points; prices/markets; calculator CTA; latest guides; trust/editorial policy; shop CTA.
- **Farming Hubs:** Kienyeji, Layers, Broilers, Hatching. Each hub should use a reusable hub template with intro, featured guides, management links, prices/calculators, and related products.
- **Farm Management:** Feeding, Housing, Health, Records. These should be taxonomy- or page-driven content areas in Phase 2, with future records functionality kept in the plugin.
- **Editorial:** guide archive, article template, category/tag/taxonomy archives, author information, related guides, search, and source/methodology components.
- **Commerce:** independent `/shop/` or owner-approved WooCommerce base, product archive/detail, cart, checkout, order confirmation, account/downloads, support, and legal pages.
- **Future extension points:** calculator block/container, farm-record dashboard shell, Q&A/community templates, supplier cards, and member profile presentation without implementing their business logic in the theme.

## KEEP / MODIFY / REBUILD / REMOVE matrix

| Component | Decision | Reason and scope |
|---|---|---|
| Native block-theme architecture | KEEP | Best portability and editor compatibility; retain HTML templates/parts plus `theme.json`. |
| Header and responsive Navigation block | MODIFY | Strong base, but replace labels, URLs, logo, CTA, search copy, and shop/account behavior for Poultry Farmer. |
| Mobile menu CSS | KEEP, then QA | Core Navigation overlay plus responsive CSS is reusable; re-test at target widths and with longer poultry labels. |
| Footer layout and legal columns | MODIFY | Reuse structure; replace brand copy, navigation, support/legal destinations, and copyright. |
| `theme.json` token system | MODIFY | Preserve spacing/type/layout primitives; replace palette and identity names with forest green/harvest gold after brand review. |
| Homepage skeleton | MODIFY/REBUILD content | Reuse hero, entry-point cards, latest content, search, newsletter and commerce slots; rebuild content hierarchy around poultry enterprise hubs and calculators. |
| Cards and content grids | KEEP | Reusable border/radius/grid patterns with responsive behavior; rename classes during identity isolation and verify image alt/link semantics. |
| Article template | MODIFY | Strong editorial shell, author box, related content and navigation; adapt terminology, metadata, source block presentation and sidebar decision. |
| Archives and category pages | MODIFY | Reuse query grids, pagination, empty states and fallback image; adapt archive labels and add hub/management taxonomies. |
| Search | KEEP/MODIFY | Native Search/Query blocks and search template are portable; change placeholders/copy and validate relevance across guides, hubs and products. |
| Sidebar | REMOVE as a current requirement | No independent sidebar component is present in the active block theme; use intentional hub/article support blocks or build a new sidebar only if content testing proves necessary. |
| Editorial patterns | KEEP/MODIFY | Assumptions, evidence, methodology, risk, key numbers and scenarios map well to poultry guides; remove AgriRadar language and avoid calculator claims before plugin UI exists. |
| Related-analysis renderer | MODIFY | Query/filter design is reusable; rename hook/function namespace and make it support “related guides”/hub relationships without requiring AgriRadar plugin metadata. |
| WooCommerce archive | MODIFY | Preserve presentation; replace digital-kit copy and decide whether products are physical supplies, digital resources, or both. |
| WooCommerce single product | MODIFY | Current code removes tabs/meta/related products and adds toolkit details; reassess for poultry products, shipping, variations, stock, reviews, and product data. |
| Cart and account wrappers | KEEP/MODIFY | Structural shells are reusable; verify blocks, endpoints, login/account copy, downloads, and physical-order requirements. |
| Checkout presentation | MODIFY | Dedicated checkout header/footer and block styling are reusable; replace hardcoded links/copy and validate payment/shipping/tax/legal requirements. |
| `/business-tools` redirect | REMOVE | It is AgriRadar-specific and has no independent Poultry Farmer migration requirement yet. Add redirects only from approved Poultry Farmer legacy paths. |
| AgriRadar logos/favicon/placeholders | REMOVE/REPLACE | Must be replaced by the supplied approved Poultry Farmer identity. Keep neutral placeholder concept only if it is rebranded and accessible. |
| Theme JavaScript | KEEP ABSENT | No custom JS is needed for current layout/menu; add JS only for a justified presentation need, not for calculators or business logic. |
| AgriRadar Core coupling | REBUILD boundary | Reuse concepts/contracts selectively; do not make Poultry Farmer depend on an AgriRadar-named plugin or metadata namespace. |
| Legacy `theme/agriradar/` tree | REMOVE from release scope, not source now | Do not delete during Phase 1. Treat as historical/parallel source until owner approves repository cleanup. |

## Confirmed issues and unverified risks

### Confirmed from source inspection

1. AgriRadar identity is pervasive in metadata, text domains, PHP prefixes, CSS classes, pattern slugs, visible copy, asset filenames, package scripts, documentation, and hardcoded URLs.
2. The theme declares PHP 7.4 while the companion plugin declares PHP 8.3. A site activating both should use the stricter tested baseline, or the plugin must be versioned/ported deliberately.
3. The theme does not declare a required plugin or WooCommerce dependency. Some templates contain WooCommerce blocks and all commerce behavior is conditional only in selected PHP hooks, so missing WooCommerce can produce incomplete commerce pages rather than a clear dependency message.
4. `inc/woocommerce.php` contains a redirect and copy specifically for AgriRadar’s former `/business-tools` and `/agribusiness-kits` model.
5. Product presentation deliberately removes standard WooCommerce product meta, data tabs, upsells, and related products. That may be wrong for physical poultry supplies or variable products.
6. Several templates and parts use root-relative hardcoded paths such as `/blog/`, `/contact/`, `/privacy-policy/`, `/my-account/`, `/agribusiness-kits/`, and `/category/market-analysis/`. These are not portable across a new information architecture.
7. The active theme has no custom sidebar implementation. A sidebar requirement would need a new block template part/pattern or an intentional one-column alternative.
8. The current calculator manifest is illustrative goat-feedlot logic, not poultry logic, and the contract explicitly says presentation adapters come later. It must not be presented as a Poultry Farmer calculator.
9. Local QA scripts include hardcoded local test assumptions/credentials and local M-PESA option inspection. They are development artifacts, not production configuration.
10. There are existing uncommitted changes and parallel untracked files in the workspace. Phase 2 must establish a clean, agreed source baseline before editing.

### Unverified and requiring runtime/staging testing

- Actual WordPress 6.6+ rendering with the selected WooCommerce version and chosen block/cart/checkout versions.
- Whether all WooCommerce block styles continue to match current markup, especially cart, checkout, order confirmation, account, product variations, shipping, tax, coupons, and payment errors.
- HPOS behavior in the connected WooCommerce installation. Source inspection found no direct `$wpdb` order queries; the theme uses `wc_get_order()` and standard hooks, which is encouraging but not proof of full compatibility.
- Whether the current theme/plugin build artifacts match source after all existing uncommitted changes.
- Mobile menu keyboard focus trapping, Escape behavior, scroll locking, admin-bar offsets, and screen-reader announcements in the target WordPress/WooCommerce versions.
- Contrast and legibility after replacing the lime/green palette with approved forest green and harvest gold.
- SEO output: canonical URLs, structured data, archive titles, pagination, author/date policy, product schema, breadcrumbs, and duplicate content. The theme supplies semantic templates but does not implement an SEO system.
- Performance under real content/media: image sizes, block CSS, WooCommerce assets, product/gallery scripts, font loading, and caching.
- Plugin conflicts involving navigation, SEO, caching, multilingual, membership, form, analytics, payment, or community plugins.

## Compatibility and quality assessment

### WordPress coding practices

The inspected PHP generally uses WordPress escaping, translation functions, hooks, `WP_Query`, `WP_Post` checks, and standard WooCommerce CRUD. The plugin uses nonces, capability checks, sanitized registered metadata, protected source fields, and public-safe citation projection. The theme’s small-file bootstrap is easy to isolate.

Risks include namespace collision from global `agriradar_*` functions, broad global filters such as `excerpt_length`, direct block-render filters keyed by class names, and plugin code using closures/global functions rather than a namespace. Poultry Farmer should use a unique namespace/prefix from the first implementation commit.

### PHP and dependency compatibility

The theme metadata says PHP 7.4, while the companion plugin says PHP 8.3. No typed PHP syntax requiring newer PHP was used as the basis of this conclusion; the declared requirements are the confirmed contract. The root package requires Node 22 for development tooling. No new framework is present or needed.

### WooCommerce and HPOS

The theme supports both native WooCommerce block shells and legacy-template blocks for product/archive output. Cart/checkout/account pages depend on block content assigned to WordPress pages. Theme PHP uses standard WooCommerce hooks and `wc_get_order()` rather than direct order-table SQL. No HPOS-specific assumptions were confirmed in source, but only a staging/runtime matrix can validate the combined WooCommerce version, HPOS, gateway, shipping, tax, and block checkout behavior.

### Performance

Positive: one theme stylesheet, no custom front-end JS, lazy/async card images in PHP fallbacks/related cards, constrained queries with `no_found_rows` in related analysis, and responsive CSS. Risks: the main stylesheet is large and loaded on every front-end page; WooCommerce assets may load broadly depending on WooCommerce; the editor stylesheet is the same large theme CSS; and broad global filters may run on unrelated content. Phase 2 should measure before splitting or optimizing.

### Security

Positive: escaping and capability/nonce checks are visible in the inspected theme/plugin paths; plugin source records are private and excluded from sitemaps; internal notes are not exposed by `agriradar_source_citation()`. Risks: future calculators/community features must not place trust in theme code or client-side values; local QA credentials must stay out of packages; and any payment/integration implementation must remain in the plugin/provider layer with secrets in environment or managed settings.

### Accessibility and mobile

Confirmed positives include native Navigation block mobile overlay, responsive CSS breakpoints at 1024/900/782/600/390px, broad `:focus-visible` outlines, table horizontal scrolling, reduced-motion rules, semantic `main/header/footer` wrappers, image lazy loading, and empty states. Runtime testing is still required for keyboard navigation, focus management, contrast after rebrand, long labels, forms, WooCommerce notices, error focus, and screen-reader output.

### SEO and plugin conflicts

The templates provide headings, dates, author data, category terms, semantic sections, and native search, but no canonical/meta/JSON-LD SEO implementation. An SEO plugin should own those concerns. Hardcoded root-relative links and duplicate archive/template paths are likely to require an information-architecture pass. The AgriRadar-specific global filters and metadata names may conflict conceptually with future Poultry Farmer plugins even if they do not collide technically.

## Recommended development order

1. Freeze this audit and obtain owner decisions on source baseline, identity namespace, logo, WooCommerce product model, information architecture, and minimum supported PHP/WP/WooCommerce versions.
2. Create an isolated Poultry Farmer theme/package boundary without deleting the existing AgriRadar source; use a new directory/package identity and a new text domain/prefix.
3. Port the design tokens, logo slots, header/footer, navigation, and neutral assets first.
4. Port the homepage, Start Here, hub templates, farm-management entry points, archives, search, article, and 404 layouts.
5. Port and adapt editorial patterns, related content, evidence/methodology presentation, and source contracts.
6. Adapt WooCommerce presentation only after the physical/digital product and payment/shipping decisions are settled.
7. Design the plugin boundary and calculator integration contract; implement no future community/farm-record functionality in the theme.
8. Run static checks, build/package hygiene checks, WordPress editor checks, responsive/accessibility checks, WooCommerce block/HPOS staging checks, and SEO/performance QA.

## Phase 2 implementation plan

### Phase 1 — Theme isolation and independent identity

**Likely affected:** `AgriRadar Theme/style.css`, `theme.json`, `functions.php`, `inc/*.php`, all pattern PHP files, all templates/parts, `assets/css/theme.css`, package/release scripts, companion plugin naming if reused.  
**New files likely needed:** new Poultry Farmer theme directory/package metadata, approved logo/favicon assets, a migration/identity map, and possibly a compatibility/readme file.  
**Dependencies:** approved logo, owner-approved package/text-domain/prefix, PHP/WP/WooCommerce baseline.  
**Risks:** mixed AgriRadar/Poultry Farmer namespaces, stale pattern slugs, accidental legacy-tree edits, broken editor content references.  
**Acceptance:** no AgriRadar visible identity, text domain, function/CSS prefix, asset filename, or hardcoded domain remains in the release source except explicitly documented migration compatibility; existing AgriRadar source remains untouched.

### Phase 2 — Brand design system

**Likely affected:** `theme.json`, `assets/css/theme.css`, header/footer, logo assets.  
**New files likely needed:** Poultry Farmer logo variants supplied by owner, favicon, and perhaps a small token documentation file.  
**Dependencies:** approved poultry/growth logo and final forest-green/harvest-gold values.  
**Risks:** insufficient contrast, overuse of gold, poor dark-surface contrast, brand mismatch between site logo and WooCommerce checkout.  
**Acceptance:** tokens represent the approved palette and type system; contrast is tested; all key templates render the supplied identity at target widths.

### Phase 3 — Global header, footer, and shared components

**Likely affected:** `parts/header.html`, `parts/footer.html`, CSS header/menu/footer rules, navigation-related theme JSON settings.  
**New files likely needed:** optional shared patterns for hub navigation, trust/editorial links, and shop/account actions.  
**Dependencies:** final site map and page slugs.  
**Risks:** long menu labels, too many top-level destinations, mobile overlay usability, broken root-relative links.  
**Acceptance:** all links resolve in a staging site, mobile menu is keyboard/screen-reader usable, logo/search/cart/account remain available without overflow.

### Phase 4 — Homepage and content hubs

**Likely affected:** `templates/front-page.html`, `templates/category.html`, `templates/archive.html`, `templates/index.html`, `theme.json`, patterns and CSS.  
**New files likely needed:** reusable hub template/patterns for Kienyeji, Layers, Broilers, Hatching and Farm Management sections.  
**Dependencies:** taxonomy/page model, editorial content plan, image/illustration assets.  
**Risks:** forcing hub pages into generic categories, unclear distinction between guides, prices, calculators, and products, thin empty states.  
**Acceptance:** each required site area has a deliberate entry point, query/card states work with zero and populated content, and hub templates remain usable with ordinary WordPress content.

### Phase 5 — Editorial templates and search

**Likely affected:** `templates/single.html`, `home.html`, `search.html`, `page.html`, `404.html`, `inc/archives.php`, `inc/related-analysis.php`, editorial patterns.  
**New files likely needed:** optional author/sidebar/support patterns and a Poultry Farmer related-content contract.  
**Dependencies:** editorial policy, author bio policy, taxonomy model, future source/citation plugin decision.  
**Risks:** overloading the theme with source/business logic, weak search relevance, duplicate archive pages, inaccessible tables/forms.  
**Acceptance:** guide/article/archive/search/404 templates have correct headings, metadata, empty states, related links, focus behavior, and content-width behavior on mobile/desktop.

### Phase 6 — WooCommerce store and checkout presentation

**Likely affected:** `inc/woocommerce.php`, `templates/archive-product.html`, `single-product.html`, `page-cart.html`, `page-checkout.html`, `page-my-account.html`, commerce CSS, header/footer commerce links.  
**New files likely needed:** only if required by the chosen product model: product-type-specific patterns, shop support blocks, or order/help presentation.  
**Dependencies:** physical vs digital product decision, shipping/tax/returns/legal policy, chosen gateway, WooCommerce version, HPOS and block checkout policy.  
**Risks:** current digital-toolkit assumptions, removed product tabs/meta/related products, hardcoded contact/privacy paths, gateway-specific checkout markup, accidental production settings changes.  
**Acceptance:** staging tests cover shop/archive, product types, variations if needed, cart, block checkout, payment success/failure, order confirmation, account, refunds/returns messaging, downloads if applicable, and HPOS.

### Phase 7 — Calculator integration

**Likely affected:** future plugin integration and theme patterns such as `patterns/calculator-bridge.php`, plus article/hub templates only for presentation slots.  
**New files likely needed:** future plugin calculator modules, version manifest, block/render adapter, methodology disclosure, tests.  
**Dependencies:** owner-approved poultry models, Kenyan input assumptions/data sources, accessibility and privacy requirements.  
**Risks:** presenting illustrative numbers as advice, formula/version drift, storing sensitive farm data in the theme, client-side-only validation, analytics leakage.  
**Acceptance:** calculator logic is plugin-owned, versioned, tested independently, disclosed, accessible, and still functions if the theme changes.

### Phase 8 — Performance, accessibility, and QA

**Likely affected:** CSS loading strategy, image sizes, templates, release/package scripts, tests and documentation.  
**New files likely needed:** QA matrix, accessibility/performance reports, staging-only fixtures.  
**Dependencies:** representative content, selected plugins, target devices/browsers, staging WordPress/WooCommerce.  
**Risks:** optimizing before content/plugin reality is known, testing only the homepage, mistaking local QA artifacts for production evidence.  
**Acceptance:** static checks pass; responsive/accessibility checks cover required breakpoints and flows; WooCommerce/HPOS matrix passes; no secrets or artifacts enter the release; no claim of live testing is made without a real test.

## Questions requiring owner decisions

1. Should Phase 2 use `AgriRadar Theme/` as the sole source baseline and leave `theme/agriradar/` as historical, or should the repository be reorganized first?
2. What exact PHP, WordPress, WooCommerce, and PHP extension support matrix should Poultry Farmer guarantee, given the current theme/plugin 7.4 vs 8.3 split?
3. Should the store sell physical poultry supplies, digital guides/tools, or both? This determines whether the current downloadable-toolkit product behavior can survive.
4. What are the approved logo files, exact forest-green/harvest-gold values, typography preferences, and favicon variants?
5. What URL/taxonomy model should be canonical for Kienyeji, Layers, Broilers, Hatching, Feeding, Housing, Health, Records, Prices & Markets, Calculators, and Guides?
6. Which WooCommerce payment gateway, shipping/tax rules, returns policy, and customer-support destination are approved for staging? No production configuration should be inferred.
7. Which source/citation and editorial-evidence capabilities from AgriRadar Core should be retained, rewritten into a Poultry Farmer plugin, or removed?
8. What is the owner’s policy for author profiles, farmer questions, community moderation, supplier listings, and member data when those future features are planned?
9. Which SEO plugin and analytics policy, if any, will be used? The theme should not add tracking by assumption.

## Final audit position

The strongest adaptation is a controlled fork of the active native block theme’s layout system and editorial/commerce presentation, with a clean Poultry Farmer identity boundary and a separate functionality plugin boundary. The existing theme is a useful presentation foundation, but it is not currently an independent Poultry Farmer theme: its metadata, copy, URLs, CSS namespace, WooCommerce assumptions, and companion plugin are all AgriRadar-specific. Phase 1 is complete; implementation should wait for owner decisions and approval.
