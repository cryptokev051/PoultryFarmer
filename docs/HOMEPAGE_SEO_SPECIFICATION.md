# Poultry Farmer Homepage SEO Specification

Date: 18 November 2026  
Target page: `/`  
Primary intent: practical introductory guidance for researching, starting or managing poultry farming in Kenya  
Primary keyword: `poultry farming in Kenya`

## Approved search presentation

- SEO title: `Poultry Farming in Kenya: Guides, Costs & Tools | Poultry Farmer`
- H1: `Poultry Farming in Kenya, Made Practical.`
- Meta description: `Learn poultry farming in Kenya with practical guides on Kienyeji, layers, broilers, feeding, housing, costs and profitability. Get useful tools and resources.`

The H1 is implemented in the block-theme homepage template. The SEO title and meta description are approved settings for the eventual SEO plugin or WordPress SEO configuration. They are not hardcoded in the theme. The theme does not add a competing description, canonical tag or second SEO system.

## Keyword-to-page map

| Page | Main keyword | Routing status |
|---|---|---|
| `/` | poultry farming in Kenya | Implemented homepage intent |
| `/guides/how-to-start-poultry-farming-kenya/` | how to start poultry farming in Kenya | Implemented in the Phase 2B2 local fixture; production copy remains pending |
| `/farming/kienyeji/` | Kienyeji chicken farming in Kenya | Implemented hierarchical hub route |
| `/farming/layers/` | layers farming in Kenya | Implemented hierarchical hub route |
| `/farming/broilers/` | broiler farming in Kenya | Implemented hierarchical hub route |
| `/management/housing/` | poultry housing in Kenya | Implemented hierarchical management route |

These specialist URLs own their narrower intent in the Phase 2B2 content model. The homepage introduces those subjects without becoming the detailed startup, breed, production or housing guide. The isolated fixture now uses `/farming/` with child hubs, `/management/` with child topics, and `/guides/` as the WordPress Posts page; the former flat fixture routes are drafted to prevent duplicate local content.

## Implemented homepage content

The rendered homepage contains one H1, followed by descriptive H2 sections and H3 card titles. The browser run measured 597 visible words in homepage paragraphs and list items.

### Hero

H1: **Poultry Farming in Kenya, Made Practical.**

Supporting copy: “Starting or managing a poultry farm? Find practical Kenyan guides on choosing chickens, controlling feed costs, managing production and understanding your farm finances.”

Primary action: **Start Here** → `/start-here/`  
Secondary action: **Explore Farming Guides** → `/farming/`

Right-hand entry point:

- H2: **Planning a Poultry Farm?**
- Choose your farming model.
- Estimate startup and operating costs.
- Understand housing, feeding and management requirements.

### Introduction

H2: **Poultry farming in Kenya: what to plan first**

The introduction defines poultry farming, identifies improved Kienyeji, layers, broilers and hatching as distinct models, and explains that timelines, housing, feeding and routes to market vary. It then asks readers to plan their customers, manageable flock size, operating finance, housing, feed, health, labour, records and market access.

### Types of poultry farming

H2: **Types of poultry farming**

- H3 **Kienyeji** — indigenous or improved Kienyeji birds for meat, eggs or both, with management suited to slower-growing adaptable birds.
- H3 **Layers** — hens managed for egg production, with emphasis on feed, lighting, records and egg markets.
- H3 **Broilers** — planned meat-bird batches where chick quality, feed conversion, housing and sale timing require control.
- H3 **Hatching & chick production** — fertile eggs, breeder quality, incubation, hatch hygiene and early brooding.

### Starting and financing the farm

H2: **Plan the farm before buying birds**

The section introduces the search intent “how to start poultry farming in Kenya” and covers the production model, target customer, site, manageable flock size, housing, water, feed storage, brooding and health routines. It links to the local Start Here fixture rather than duplicating a future detailed guide.

H2: **Understand the full cost of production**

The section covers initial investment, housing, feed, chicks or breeding stock, flock health, labour, management expenses, selling prices, market access, working capital and break-even. It contains no startup-cost figure, earning claim or guaranteed-profit statement.

### Supporting sections

- H2 **Financial calculators & tools** — explicitly Coming Soon; no form or result is active.
- H2 **Featured guides** — uses the native WordPress post query.
- H2 **Manage an existing poultry farm better** — feeding, housing, flock health, record-keeping and production monitoring.
- H2 **Prices & poultry markets** — describes planned verified context for feed, eggs, equipment and markets; does not claim current prices.
- H2 **Useful resources, built for practical work.** — future original templates, record sheets and farm-management resources; purchasing is unavailable pending independent shop-runtime verification.
- H2 **Useful poultry guidance, without the noise.** — newsletter invitation marked Coming Soon; no information is collected.

## Semantic and editorial rules

- Keep exactly one visible H1 on `/`.
- Use the primary phrase in the H1 and introductory content, not as repeated card boilerplate.
- Keep specialist guide copy more detailed than homepage summaries.
- Do not publish unverified costs, profits, live prices, statistics or testimonials.
- Do not activate calculator, newsletter or shop claims until each feature has a working consent or transaction flow.
- Configure the approved title and meta description once, in the selected SEO plugin or WordPress SEO layer.
