# Poultry Farmer Content Model and URL Structure

Date: 19 November 2026  
Status: Phase 2B2 authenticated workflow verified

## Selected model

Poultry Farmer uses WordPress core content types and routing. No custom post type or custom rewrite rule is required for the current editorial model.

| Content | WordPress model | Canonical local route | Status |
|---|---|---|---|
| Homepage | Static Page assigned as the front page | `/` | PASS |
| Farming directory | Hierarchical Page | `/farming/` | PASS |
| Poultry-type hubs | Child Pages of Farming, using the Poultry Type Hub template | `/farming/kienyeji/`, `/farming/layers/`, `/farming/broilers/`, `/farming/hatching/` | PASS |
| Guides directory | Page assigned as the WordPress Posts page | `/guides/` | PASS |
| Ordinary guides | Standard Posts | `/guides/%postname%/` | PASS |
| Farm management | Hierarchical Page | `/management/` | PASS |
| Management topics | Child Pages of Management | `/management/feeding/`, `/management/housing/`, `/management/health/`, `/management/records/` | PASS |
| Prices | Page | `/prices/` | PASS |
| Calculators | Page; informational coming-soon state only | `/calculators/` | PASS |
| Products | WooCommerce Product post type | WooCommerce-owned routes | NOT TESTED in Phase 2B2 |

## Why `/guides/` is the Posts page

The guides directory is the native Posts archive because the ordinary editorial library needs dates, authors, categories, search, pagination and Query Loop support. The fixture sets:

- `show_on_front` to a static Page;
- `page_for_posts` to the Guides Page;
- the post permalink structure to `/guides/%postname%/`.

This gives readers one clear editorial directory and keeps ordinary articles in the standard Post editing workflow. A Page with a custom Query Loop would duplicate archive responsibilities, while a custom post type would add no justified data-model benefit at this stage.

Do not create child Pages below `/guides/`; that path is reserved for standard Post permalinks. Core category archives remain classification views rather than competing section homepages. No category is presented as the canonical Kienyeji, layers, broilers or hatching hub.

## Hierarchy and ownership

```text
/
├── farming/
│   ├── kienyeji/
│   ├── layers/
│   ├── broilers/
│   └── hatching/
├── guides/                       # Posts page
│   └── {post-name}/              # Standard Post permalink
├── management/
│   ├── feeding/
│   ├── housing/
│   ├── health/
│   └── records/
├── prices/
└── calculators/
```

The former local flat fixtures (`/kienyeji-farming/`, `/layers-farming/`, `/broilers-farming/`, `/hatching/` and `/farm-management/`) are drafted by the Phase 2B2 fixture. They are not linked and must not become parallel production routes. Redirect decisions belong to a later migration plan if any of those paths have ever been indexed on a real site.

## Taxonomy model

Core Categories classify ordinary Posts by editorial topic. The local fixture demonstrates Kienyeji, Layers, Broilers, Hatching, Housing and Records. Editors may add a category only when it groups multiple useful Posts; a category must not be created merely to duplicate a permanent hub Page.

Tags may describe cross-cutting details when a real discovery need exists. They should not be generated automatically, and near-duplicate tags should be avoided. No custom taxonomy is justified in Phase 2B2.

## Editing workflow

1. Create permanent section and poultry-type hubs as Pages.
2. Set the correct parent Page so WordPress owns the hierarchy.
3. Assign **Poultry Type Hub** to poultry-type pages or **Editorial Section Hub** to other permanent directories.
4. Build the Page body with native blocks and the limited Poultry Farmer pattern library.
5. Publish ordinary guides as Posts, assign only relevant categories, add a useful excerpt, named author and featured image where available.
6. Use the Guides Posts page for archive discovery; do not manually maintain a second article index.
7. Configure SEO titles, descriptions, canonicals and schema in the eventual SEO plugin, not in theme source.

This workflow was verified in an authenticated WordPress 7.1.4 block-editor session. A draft Page retained the `poultry-hub` template, excerpt, native blocks and a category-filtered Query Loop after save/reopen. A second draft retained the `section-hub` template and editor-managed content. Neither template depends on local fixture code.

## Production installation settings

These settings must be configured during production installation or migration; installing the theme alone does not create editorial content:

1. Create a static Home Page and assign it under **Settings > Reading**.
2. Create a Guides Page and assign it as the WordPress Posts page.
3. Set the Post permalink structure to `/guides/%postname%/`, then save the Permalink screen once so WordPress refreshes rewrite rules.
4. Create Farming and Management parent Pages, then create their child Pages with the approved slugs.
5. Assign **Poultry Type Hub** to poultry-type child Pages and **Editorial Section Hub** to permanent section Pages.
6. Create only categories required by published guides and apply the matching category filter to each hub Query Loop.
7. Create real author accounts and biographies before publishing. Reviewer credits require a documented review and are not generated by the theme.

The independently installable `poultryfarmer-starter` plugin can now create items 1–6 on a fresh site after an Administrator reviews its preview and explicitly approves the change. It records only the objects it creates, blocks untracked slug conflicts and never resets owner-edited content on later runs. See `STARTER_CONTENT_SPECIFICATION.md` and `FRESH_INSTALL_CHECKLIST.md`. Established-site migrations still require manual URL and redirect review.

The development fixture in `theme/poultryfarmer/dev/profile-b-content.php` demonstrates these settings but is excluded from the release ZIP. Production does not require it.

## SEO intent separation

| Route | Primary purpose / recommended target |
|---|---|
| `/` | poultry farming in Kenya |
| `/farming/` | directory for choosing and comparing poultry farming models |
| `/farming/kienyeji/` | Kienyeji chicken farming in Kenya |
| `/farming/layers/` | layers farming in Kenya |
| `/farming/broilers/` | broiler farming in Kenya |
| `/farming/hatching/` | chicken hatching and incubation in Kenya |
| `/guides/how-to-start-poultry-farming-kenya/` | how to start poultry farming in Kenya |
| `/management/housing/` | poultry housing in Kenya |

The homepage introduces broad subjects. Hubs organize a production model. Detailed Posts answer narrower questions. Editors should not publish a second page targeting the same main intent without an explicit consolidation or redirect plan.

## Recommended metadata for later SEO-plugin configuration

These are recommendations only; the theme does not output competing metadata.

| Route | Recommended SEO title | Recommended meta description |
|---|---|---|
| `/farming/` | Poultry Farming Types in Kenya | Compare Kienyeji, layers, broilers and hatching, then find practical guides for the poultry farming model you plan to manage. |
| `/farming/kienyeji/` | Kienyeji Chicken Farming in Kenya | Explore practical Kienyeji chicken farming guidance on production choices, housing, feeding, health, records and market planning. |
| `/farming/layers/` | Layers Farming in Kenya | Learn the planning and daily management foundations for layers, including housing, feeding, egg handling, records and market preparation. |
| `/farming/broilers/` | Broiler Farming in Kenya | Explore broiler batch planning, brooding, feeding, housing, flock monitoring and sale preparation for Kenyan farms. |
| `/farming/hatching/` | Chicken Hatching and Incubation in Kenya | Find practical guidance on fertile eggs, incubation checks, hatch hygiene, brooding and early chick care. |
| `/guides/` | Poultry Farming Guides | Browse practical Poultry Farmer guides for starting, managing and improving poultry farms in Kenya. |

## Known limitations

- The Phase 2B2 page and post copy is synthetic local fixture content, not approved production editorial copy.
- Redirect requirements for any future migration have not been assessed against production analytics or indexed URLs.
- SEO metadata is documented but not configured because no SEO plugin is selected.
- WooCommerce routing remains outside Profile B and was not changed.
- The authenticated workflow used synthetic local content and a temporary local administrator only; no production users or content were accessed.

