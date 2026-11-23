# Starter Setup Independent Review Closeout

Date: 23 November 2026

## Review scope

This closeout records corrections made after independent source review. It excludes live production sites, databases and WooCommerce/payment behavior.

## Corrections

1. **Homepage editability.** The approved theme front-page template now includes `core/post-content` in the introductory section. The Home Page stores editable copy; the theme retains presentation and the one SEO H1.
2. **Strict preflight.** The setup now reports specific reasons for additional editorial/custom Posts, Pages or products, custom Categories, materially modified core samples and changed default sample identifiers/status.
3. **Settings verification.** `apply_setup()` rereads `show_on_front`, `page_on_front`, `page_for_posts` and `permalink_structure`; setup records completion only after exact read-back matches.
4. **Recovery.** A manifest and database can resume past an interrupted category/Page creation pass. Tracked objects are preserved and missing or changed tracked identifiers block reruns.
5. **Starter drafts.** The four poultry hubs have distinct introductions and planning lists. Management topics have distinct useful outlines. No unsupported prices, profits, disease guidance, statistics or credentials were added.
6. **Final preflight correction.** Initial setup, verified partial setup and completed setup are now separate states. Custom permalinks cannot pass an untracked-install preflight merely because another rejection reason is empty. Core samples use exact fixture fingerprints and fail closed when the supported baseline cannot be established.

## 18-Page readiness

| Page | Initial status | Template | Editable content | Query Loop | Readiness/action |
|---|---|---|---|---|---|
| Home | Publish | Default/front-page | Yes: core/post-content | No | Ready; owner reviewed |
| Farming | Draft | Default page | Yes | No | Outline review before publication |
| Kienyeji | Draft | poultry-hub | Yes, distinct | Kienyeji | Edit and editorially approve |
| Layers | Draft | poultry-hub | Yes, distinct | Layers | Edit and editorially approve |
| Broilers | Draft | poultry-hub | Yes, distinct | Broilers | Edit and editorially approve |
| Hatching | Draft | poultry-hub | Yes, distinct | Hatching | Edit and editorially approve |
| Management | Draft | section-hub | Yes | No | Review overview and links |
| Feeding and Water | Draft | section-hub | Yes, distinct outline | Feeding | Expand and approve |
| Poultry Housing | Draft | section-hub | Yes, distinct outline | Housing | Expand and approve |
| Flock Health | Draft | section-hub | Yes, distinct outline | Health | Expand and approve |
| Poultry Farm Records | Draft | section-hub | Yes, distinct outline | Records | Expand and approve |
| Prices and Markets | Draft | section-hub | Yes; no figures | No | Require documented, dated, sourced market process |
| Poultry Calculators | Draft | section-hub | Yes; coming-soon copy | No | Keep unpublished until real core functionality exists |
| Guides | Publish | Default page | Yes | Guide Posts page | Structural publishing page |
| Start Here | Draft | Default page | Yes outline | No | Replace prompt and approve |
| About | Draft | Default page | Yes outline | No | Replace with approved organization content |
| Editorial Policy | Draft | Default page | Yes outline | No | Replace with approved policy |
| Contact | Draft | Default page | Yes outline | No | Replace with approved contact details |

## Exercised validation

- Fresh setup and second setup
- Administrator preview and nonce-protected action
- Theme guard
- Slug conflict
- Owner edit preservation
- Home and Management overview editor persistence
- Category filtering and multi-category Posts
- Draft/unrelated Post exclusion
- Restart persistence and plugin deactivation
- ZIP upload and activation
- Desktop, tablet and mobile frontend rendering
- Static front/Posts/permalink read-back

Not exercised: live production migration, production databases, WooCommerce/payment behavior and automated accessibility certification. Controlled interruption checkpoints are present in the source and the local test matrix; native PHP lint was unavailable on the review host because no PHP executable was installed.

## Packages

The revised plugin and theme sources are reflected in the project release ZIPs. No theme, WooCommerce, payment or production behavior was deployed or committed.
