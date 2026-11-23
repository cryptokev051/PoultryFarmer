# Poultry Farmer Fresh Install Checklist

Date: 19 November 2026

## Before setup

1. Back up the WordPress database and files if the installation is not disposable.
2. Install and activate the approved Poultry Farmer theme.
3. Upload `poultryfarmer-starter.zip` through **Plugins > Add Plugin > Upload Plugin** and activate it.
4. Sign in as an Administrator; do not share or commit credentials.
5. Open **Tools > Poultry Farmer Setup**.
6. Review all 18 Pages, 8 Categories and the proposed Home, Posts-page and permalink settings.
7. Stop if any conflict appears. Do not rename or delete existing content merely to force setup; resolve ownership and URL decisions first.
8. Confirm the preview does not report additional published or draft editorial content, custom products, modified core sample objects or custom Categories. These are reasons the strict preflight may preserve permalink settings and block setup.

## Apply once

1. Press **Create or verify starter content** only after approving the preview.
2. Confirm the success notice.
3. Verify **Settings > Reading** uses Home as the static front page and Guides as the Posts page.
4. Verify **Settings > Permalinks** is `/guides/%postname%/`.
5. Confirm Home and Guides are published and all unfinished editorial Pages remain drafts.

## Editorial review

1. Review each draft in the Block Editor; remove every “Draft starter page” prompt before publication.
2. Confirm parent Pages and templates:
   - Kienyeji, Layers, Broilers, Hatching: parent Farming; template Poultry Type Hub.
   - Feeding, Housing, Health, Records: parent Management; template Editorial Section Hub.
3. Review Prices without adding unsourced or undated figures.
4. Keep Calculators in its coming-soon state until the future core plugin provides real logic.
5. Replace About, Editorial Policy, Contact and Start Here outlines with approved information.
6. Review WordPress’s default Hello World Post, Sample Page and draft Privacy Policy. Remove or retain them deliberately; starter setup does not delete them.
7. Add real authors, sources, image rights, dates and reviewer information only when verified.

## Publishing workflow

1. Publish ordinary guides as standard Posts.
2. Assign the relevant poultry-type and/or management Category.
3. Confirm the guide URL is `/guides/<post-slug>/`.
4. Confirm the guide appears automatically on every matching hub and not on unrelated hubs.
5. Confirm draft Posts remain absent and empty Categories show the intended message.

## Verification and removal

1. Check `/`, `/farming/`, all four poultry hubs, `/management/` and its four topic hubs, `/prices/`, `/calculators/`, `/guides/`, `/start-here/`, `/about/`, `/editorial-policy/` and `/contact/`.
2. Test desktop, tablet and mobile navigation, images, one H1, overflow and internal links.
3. Edit one starter Page and save it.
4. Run the setup preview a second time; confirm it says existing tracked content will be preserved and reports no duplicates or conflicts.
5. Deactivate the starter plugin.
6. Confirm the edited Page, hub listings and site settings remain intact.
7. Remove the plugin only after retaining a database backup and recording that setup completed. Its content is not deleted on deactivation or uninstall.

## Do not do

- Do not run setup on an established site without reviewing the conflict report and URL impact.
- Do not publish fixture or synthetic QA content.
- Do not use this plugin for WooCommerce, payments, customer accounts, calculators, SEO metadata or production migration.
- Do not deploy generated runtime directories, local databases, screenshots, credentials, Node dependencies or test binaries.
