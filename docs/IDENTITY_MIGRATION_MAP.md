# Poultry Farmer Phase 2A Identity Migration Map

**Scope:** New `poultryfarmer/` copy only. The approved source `AgriRadar Theme/` was not edited.

## Controlled mapping

| Source identity | Poultry Farmer identity | Applied to |
|---|---|---|
| Theme Name: AgriRadar | Theme Name: Poultry Farmer | `poultryfarmer/style.css` |
| `https://agriradar.co.ke/` | `https://poultryfarmer.co.ke/` | `poultryfarmer/style.css` |
| Version `0.4.0` | Version `0.1.0` | `poultryfarmer/style.css` |
| AgriRadar description | Approved independent Poultry Farmer description | `poultryfarmer/style.css` |
| `agriradar` text domain | `poultryfarmer` | PHP translations and pattern headers |
| `agriradar_*` functions | `poultryfarmer_*` functions | `poultryfarmer/functions.php`, `poultryfarmer/inc/*.php` |
| `agriradar-*` CSS classes | `poultryfarmer-*` CSS classes | `poultryfarmer/assets/css/theme.css`, block markup, PHP output |
| `--agriradar-*` custom property | `--poultryfarmer-*` | `poultryfarmer/assets/css/theme.css` |
| `agriradar/...` pattern slugs | `poultryfarmer/...` | pattern headers and template references |
| `Agri Green` / Radar Dark / Signal Lime | Forest Green / Forest Green Dark / Harvest Gold | `poultryfarmer/theme.json`, CSS fallback/token references |
| AgriRadar logo assets | Not copied; supplied Poultry Farmer logo pending | `poultryfarmer/assets/images/brand/` remains empty |
| “Get The Radar” / “THE RADAR” | Poultry Farmer updates / POULTRY FARMER | header and newsletter patterns |
| Agribusiness Kits / toolkit store copy | Poultry Farmer Shop / digital products | templates, WooCommerce strings, patterns |
| `/business-tools` → `/agribusiness-kits` redirect | Removed | `poultryfarmer/inc/woocommerce.php` |

## Explicitly not renamed

WordPress and WooCommerce core hooks, block names, template names, and APIs remain unchanged. Examples include `after_setup_theme`, `wp_enqueue_scripts`, `pre_get_posts`, `render_block_core/group`, `woocommerce_*`, `wc_get_order()`, `WP_Query`, and native `wp:*`/`woocommerce:*` blocks.

The existing editorial concept of “analysis” remains in some class names and copy because this phase preserves layout and does not redesign the publication model. Phase 2B/content implementation can change those labels to “guides” or “articles” consistently after the editorial information architecture is approved.

## File-level migration

- Metadata: `poultryfarmer/style.css`, `poultryfarmer/theme.json`.
- Bootstrap/setup/assets/pattern registration: `poultryfarmer/functions.php`, `poultryfarmer/inc/setup.php`, `inc/assets.php`, `inc/patterns.php`.
- Archive and related-content callbacks: `poultryfarmer/inc/archives.php`, `inc/related-analysis.php`.
- WooCommerce callbacks: `poultryfarmer/inc/woocommerce.php`.
- Block markup and classes: `poultryfarmer/parts/*.html`, `templates/*.html`, `patterns/*.php`.
- Packaging identity and root directory: `poultryfarmer/scripts/build-release.ps1` and the generated `poultryfarmer/dist/poultryfarmer.zip`.
- Pattern file renames: `agriradar-verdict.php` → `poultryfarmer-verdict.php`; `get-the-radar.php` → `poultryfarmer-updates.php`.

## AgriRadar-only material excluded from the new theme

AgriRadar logos/favicon, screenshot, local QA scripts, test fixtures, internal documentation, research JSON, `node_modules`, plugin files, and the old redirect were excluded. No AgriRadar Core files were copied or renamed.
