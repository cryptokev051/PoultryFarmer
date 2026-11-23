# Poultry Farmer Internal Linking Rules

Date: 19 November 2026

## Principles

Internal links should help a reader choose the next useful action. They must use descriptive text, point to a valid canonical route and avoid creating competing paths for the same topic.

## Link flow

| From | Link to | Purpose |
|---|---|---|
| Homepage | Farming overview, management, prices, calculators, Guides and Start Here | Introduce the major decisions without duplicating specialist content. |
| Farming overview | Four poultry-type hubs | Help a reader choose a production model. |
| Poultry-type hub | Category-relevant guides | Move from overview to specific tasks and decisions. |
| Poultry-type hub | Feeding, housing, health and records | Connect production choices to farm-management systems. |
| Poultry-type hub | Other poultry-type hubs | Support genuine model comparison. |
| Guide | Parent subject hub and relevant management topic | Provide context and a practical next step. |
| Guide | Related guides sharing a category | Continue the same topic; never fill space with random Posts. |
| Empty related state | Guides directory | Offer a truthful alternative when no related Post exists. |
| Archive/search card | The matching guide | Keep discovery direct and predictable. |

## Anchor text

Use text that identifies the destination, for example:

- “Kienyeji chicken farming guides”
- “Poultry housing guidance”
- “Browse all poultry farming guides”
- “Compare other farming models”

Avoid generic repeated anchors such as “click here,” ambiguous “learn more,” or keyword-heavy text that does not read naturally.

## Canonical route rules

- Link Kienyeji information to `/farming/kienyeji/`, not the drafted flat fixture.
- Link layers to `/farming/layers/`.
- Link broilers to `/farming/broilers/`.
- Link hatching to `/farming/hatching/`.
- Link management topics below `/management/`.
- Link ordinary Posts below `/guides/`.
- Do not create a Page below `/guides/` for an ordinary article.
- Do not link to draft, proposed or nonexistent routes.
- Do not add redirects without reviewing the actual migration and indexed-URL evidence.

## Related-content rules

The current related-guides callback selects published Posts that share at least one category with the current Post and excludes the current Post. It does not use a random or recent-Post fallback. When no eligible Post exists, the page states that no additional guides are published in the topic and links to `/guides/`.

Editors should assign only categories that genuinely describe the guide. Inflating categories to force related cards damages discovery quality.

## Archive duplication controls

- Permanent Page hubs own broad topic introductions.
- Standard Posts answer specific editorial questions.
- Categories classify Posts; they do not replace the permanent hub Page.
- Archive excerpts should summarize and link, not reproduce the full article.
- The homepage and Farming overview should introduce specialist topics rather than repeat full guide copy.

## Validation

The Phase 2B2 desktop crawl checked 21 unique internal fixture links. All returned a status below 400 and no maintenance, critical-error, database-error or fatal-error page. Tablet and mobile reused the same routes and separately validated navigation/search behavior.

Before production publication, rerun link validation after real content, SEO-plugin settings and any migration redirects are in place.

