# Owner Content Editing Guide

This guide separates WordPress Page Editor actions from Site Editor actions.

## Homepage

Open **Pages > Home**. Edit the introductory paragraphs in the WordPress editor and choose **Update**. The approved theme presentation supplies the hero, H1, section cards and navigation. The theme front-page template does not require theme-source edits.

The editing boundary is deliberate: **Pages > Home** edits the Home Page's stored introductory copy only. **Appearance > Editor** edits the reusable header/footer, navigation and other Site Editor template or template-part content. The hero, section-card layout, archive/query presentation and the single H1 still depend on the approved theme template. Do not describe the entire homepage as editable from Pages > Home.

The starter preflight treats the WordPress Privacy Policy as baseline-protected during initial setup. Review or edit it only after setup; an edited Privacy Policy blocks fresh-install setup safely rather than being adopted or overwritten.

Verified in local QA:

1. A browser-authenticated owner edit was saved in the Gutenberg Page Editor.
2. `/` showed the saved text after frontend reload.
3. Reopening **Pages > Home** showed the same saved text.
4. The frontend retained one H1.

## Farming overview

Open **Pages > Farming** and edit or publish its Page content. It is a directory Page for the poultry-type hubs.

## Poultry-type hubs

Open **Pages** and edit one of:

- Kienyeji Chicken Farming in Kenya
- Layers Farming in Kenya
- Broiler Farming in Kenya
- Chicken Hatching and Incubation in Kenya

All four use the **Poultry Type Hub** template. Edit their introduction, planning list and article content through the Page Editor. The template supplies the title area and presentation. Keep each Page distinct and publish only after editorial review.

Verified in local QA: an owner-edited Kienyeji introduction was saved, appeared on `/farming/kienyeji/`, survived a setup rerun and reopened in the editor.

## Management topics

Open **Pages** and edit the **Farm Management** overview or these children:

- Feeding and Water
- Poultry Housing in Kenya
- Flock Health
- Poultry Farm Records

All use the **Editorial Section Hub** template. Edit the starter outline, then add verified editorial detail. The native Category Query Loop below each management topic updates automatically as published Posts are assigned to the matching Category.

## Categories

Open **Posts > Categories**. Use the existing starter Categories: Kienyeji, Layers, Broilers, Hatching, Feeding, Housing, Health and Records. Rename or add descriptions only when intentional; do not change the slug or starter ownership of an existing setup-owned Category without reviewing the setup preview.

## Article listings

Open **Pages > Guides** for the Page/Posts archive relationship. Create ordinary articles under **Posts > Add New** and assign one or more starter Categories. The permalink format is `/guides/<post-slug>/`. Multi-category Posts appear in every matching hub Query Loop. Draft Posts do not appear.

## Header and footer

Header and footer are controlled in **Appearance > Editor** (Site Editor), not through a normal Page:

1. Open **Appearance > Editor > Patterns** or navigate to the header/footer template part.
2. Edit the approved navigation and footer content.
3. Save in the Site Editor.

Do not add a second H1 or duplicate homepage sections in Page content. Use the Page Editor for ordinary Page copy and the Site Editor for reusable header, footer and theme-template presentation.
