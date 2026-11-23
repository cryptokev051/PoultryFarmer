# Poultry Farmer Starter Content Specification

Date: 19 November 2026  
Component: `poultryfarmer-starter` 1.0.0

## Purpose and ownership

The starter component is an administrator-triggered installation utility, not theme activation logic. It creates the approved WordPress content structure once, records the objects it created, and may be deactivated or removed after verification. All created content remains standard Pages, Posts-page settings, Categories and editable core blocks.

The setup screen is under **Tools > Poultry Farmer Setup**. It requires `manage_options`, displays every intended change before writing, and requires a nonce-protected explicit action. The Poultry Farmer theme must be active.

## Pages

| Key | Route | Parent | Template | Initial status | Content classification |
|---|---|---|---|---|---|
| Home | `/` (`home` Page) | — | default/front-page | Published | Approved theme homepage remains authoritative; Page copy is a short approved introduction. |
| Farming | `/farming/` | — | default | Draft | Approved directory introduction; owner review required. |
| Kienyeji | `/farming/kienyeji/` | Farming | `poultry-hub` | Draft | Specific starter overview, planning points and automatic guide listing. |
| Layers | `/farming/layers/` | Farming | `poultry-hub` | Draft | Specific starter overview, planning points and automatic guide listing. |
| Broilers | `/farming/broilers/` | Farming | `poultry-hub` | Draft | Specific starter overview, planning points and automatic guide listing. |
| Hatching | `/farming/hatching/` | Farming | `poultry-hub` | Draft | Specific starter overview, planning points and automatic guide listing. |
| Management | `/management/` | — | `section-hub` | Draft | Approved management directory outline. |
| Feeding | `/management/feeding/` | Management | `section-hub` | Draft | Clearly labelled editorial starter prompt plus automatic guide listing. |
| Housing | `/management/housing/` | Management | `section-hub` | Draft | Clearly labelled editorial starter prompt plus automatic guide listing. |
| Health | `/management/health/` | Management | `section-hub` | Draft | Clearly labelled editorial starter prompt plus automatic guide listing. |
| Records | `/management/records/` | Management | `section-hub` | Draft | Clearly labelled editorial starter prompt plus automatic guide listing. |
| Prices | `/prices/` | — | `section-hub` | Draft | Warning that verified, dated market data is required; no prices supplied. |
| Calculators | `/calculators/` | — | `section-hub` | Draft | Explicit coming-soon copy; no calculator logic. |
| Guides | `/guides/` | — | default/Posts page | Published | Structural Posts page; no Page body is presented as editorial copy. |
| Start Here | `/start-here/` | — | default | Draft | Clearly labelled editorial outline. |
| About | `/about/` | — | default | Draft | Clearly labelled editorial outline. |
| Editorial Policy | `/editorial-policy/` | — | default | Draft | Clearly labelled editorial outline. |
| Contact | `/contact/` | — | default | Draft | Clearly labelled editorial outline. |

Only Home and Guides are initially published because WordPress needs them for the static front page and Posts page. Every unfinished editorial or informational Page remains a draft.

## Categories

The setup creates only these eight Categories and resolves them by stable slug:

| Group | Names and slugs |
|---|---|
| Poultry types | Kienyeji (`kienyeji`), Layers (`layers`), Broilers (`broilers`), Hatching (`hatching`) |
| Management topics | Feeding (`feeding`), Housing (`housing`), Health (`health`), Records (`records`) |

It does not create Page-shaped Categories, custom post types or duplicate article copies.

## Automatic guide listings

Each poultry-type and management-topic Page receives a native, non-inherited Query Loop. During setup, the component finds the Category by slug and writes the resulting local term ID into the block query. IDs are never copied from development fixtures.

The Query Loop requests published standard Posts, six per page, newest first. A Post assigned to both Kienyeji and Feeding appears in both hubs. Drafts, unrelated Categories and Uncategorized-only Posts are excluded. Empty loops show either “No matching guides are published yet” or “No matching management guides are published yet.” New matching Posts appear automatically after publication without editing the Page.

## Site settings

After conflict checks and explicit approval, setup assigns:

- static front page: starter Home;
- Posts page: starter Guides;
- Post permalink structure: `/guides/%postname%/`.

The preview blocks execution if an established front page, Posts page or unsupported permalink is detected. The WordPress 7.1.4 fresh-install date permalink is accepted only when the database contains no content beyond the exact core sample Post, Sample Page and draft Privacy Policy. The component does not delete those WordPress sample objects; the owner must review them before launch.

## Preservation rules

- Created Pages and Categories receive a stable `_poultryfarmer_starter_key` marker and are also recorded in `poultryfarmer_starter_manifest_v1`.
- A later run verifies those exact IDs and markers, then preserves content, status, title, excerpt, template, parent and Categories unchanged.
- A matching slug without the setup marker is treated as owner content and blocks setup; it is never adopted.
- Missing or altered tracked objects block setup for review.
- Changed front-page, Posts-page or permalink settings after setup block a later run; they are not reset.
- Deactivation or uninstall does not delete content or options.

## Out of scope

No WooCommerce, payment, customer, calculator, community, SEO metadata or AgriRadar functionality is included. The setup creates no real articles, prices, financial claims, credentials or live data.
