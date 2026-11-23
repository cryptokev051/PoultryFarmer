<?php
/**
 * Phase 2B2 local Profile B content fixture.
 *
 * This file is development-only and excluded from the release archive.
 * All content is synthetic and clearly labelled for layout testing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Insert or update a fixture page.
 */
function pf_fixture_page( $slug, $title, $excerpt, $content, $parent = 0, $template = '' ) {
	$existing = get_page_by_path( $slug, OBJECT, 'page' );
	$data     = array(
		'post_title'   => $title,
		'post_name'    => basename( $slug ),
		'post_excerpt' => $excerpt,
		'post_content' => $content,
		'post_parent'  => (int) $parent,
		'post_status'  => 'publish',
		'post_type'    => 'page',
	);

	if ( $existing ) {
		$data['ID'] = $existing->ID;
		$page_id    = wp_update_post( $data );
	} else {
		$page_id = wp_insert_post( $data );
	}

	if ( $template && ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', $template );
	}

	return (int) $page_id;
}

/**
 * Insert or update a fixture guide.
 */
function pf_fixture_post( $slug, $title, $excerpt, $content, $categories ) {
	$existing = get_page_by_path( $slug, OBJECT, 'post' );
	$data     = array(
		'post_title'    => $title,
		'post_name'     => $slug,
		'post_excerpt'  => $excerpt,
		'post_content'  => $content,
		'post_author'   => 1,
		'post_status'   => 'publish',
		'post_type'     => 'post',
		'post_category' => array_map( 'intval', $categories ),
	);

	if ( $existing ) {
		$data['ID'] = $existing->ID;
		return (int) wp_update_post( $data );
	}

	return (int) wp_insert_post( $data );
}

/**
 * Build an editor-managed poultry hub body.
 */
function pf_fixture_hub_content( $model, $overview, $considerations, $category_id, $management_intro, $other_links ) {
	$query = wp_json_encode(
		array(
			'queryId' => 200 + (int) $category_id,
			'query'   => array(
				'perPage'  => 3,
				'pages'    => 0,
				'offset'   => 0,
				'postType' => 'post',
				'order'    => 'desc',
				'orderBy'  => 'date',
				'taxQuery' => array( 'category' => array( (int) $category_id ) ),
				'inherit'  => false,
			),
		)
	);

	return '<!-- wp:group {"align":"full","className":"phase2b2-hub-section","backgroundColor":"surface","layout":{"type":"constrained"}} --><div class="wp-block-group alignfull phase2b2-hub-section has-surface-background-color has-background"><!-- wp:columns {"align":"wide","className":"phase2b2-overview-grid"} --><div class="wp-block-columns alignwide phase2b2-overview-grid"><!-- wp:column {"width":"58%"} --><div class="wp-block-column" style="flex-basis:58%"><!-- wp:paragraph {"className":"phase2b1-eyebrow"} --><p class="phase2b1-eyebrow">Production model</p><!-- /wp:paragraph --><!-- wp:heading {"level":2} --><h2 class="wp-block-heading">How ' . esc_html( $model ) . ' farming works</h2><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html( $overview ) . '</p><!-- /wp:paragraph --></div><!-- /wp:column --><!-- wp:column {"width":"42%"} --><div class="wp-block-column" style="flex-basis:42%"><!-- wp:group {"className":"phase2b2-considerations","backgroundColor":"brand-soft","layout":{"type":"constrained"}} --><div class="wp-block-group phase2b2-considerations has-brand-soft-background-color has-background"><!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Plan before you start</h2><!-- /wp:heading --><!-- wp:list --><ul class="wp-block-list"><li>' . implode( '</li><li>', array_map( 'esc_html', $considerations ) ) . '</li></ul><!-- /wp:list --></div><!-- /wp:group --></div><!-- /wp:column --></div><!-- /wp:columns --></div><!-- /wp:group -->'
		. '<!-- wp:group {"align":"full","className":"phase2b2-hub-section","backgroundColor":"canvas","layout":{"type":"constrained"}} --><div class="wp-block-group alignfull phase2b2-hub-section has-canvas-background-color has-background"><!-- wp:group {"align":"wide","className":"phase2b1-section-heading","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} --><div class="wp-block-group alignwide phase2b1-section-heading"><!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Related ' . esc_html( $model ) . ' guides</h2><!-- /wp:heading --><!-- wp:paragraph --><p><a href="/guides/">Browse every guide →</a></p><!-- /wp:paragraph --></div><!-- /wp:group --><!-- wp:query ' . $query . ' --><div class="wp-block-query alignwide phase2b2-guide-query"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} --><!-- wp:group {"className":"poultryfarmer-card phase2b2-guide-card","backgroundColor":"surface","layout":{"type":"constrained"}} --><div class="wp-block-group poultryfarmer-card phase2b2-guide-card has-surface-background-color has-background"><!-- wp:post-terms {"term":"category","fontSize":"small"} /--><!-- wp:post-title {"isLink":true,"level":3} /--><!-- wp:post-excerpt {"moreText":"","showMoreOnNewLine":false} /--><!-- wp:post-date {"fontSize":"small"} /--></div><!-- /wp:group --><!-- /wp:post-template --><!-- wp:query-no-results --><!-- wp:paragraph --><p>No ' . esc_html( strtolower( $model ) ) . ' guides are published yet.</p><!-- /wp:paragraph --><!-- /wp:query-no-results --></div><!-- /wp:query --></div><!-- /wp:group -->'
		. '<!-- wp:group {"align":"full","className":"phase2b2-hub-section","backgroundColor":"surface","layout":{"type":"constrained"}} --><div class="wp-block-group alignfull phase2b2-hub-section has-surface-background-color has-background"><!-- wp:columns {"align":"wide","className":"phase2b2-management-grid"} --><div class="wp-block-columns alignwide phase2b2-management-grid"><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Management topics for this flock</h2><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html( $management_intro ) . '</p><!-- /wp:paragraph --><!-- wp:list --><ul class="wp-block-list"><li><a href="/management/feeding/">Feeding and water</a></li><li><a href="/management/housing/">Housing</a></li><li><a href="/management/health/">Flock health</a></li><li><a href="/management/records/">Records and monitoring</a></li></ul><!-- /wp:list --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Plan the finances carefully</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Identify setup costs, recurring inputs, working capital and the assumptions that affect break-even. No fixture value is presented as a verified Kenyan market fact.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><a href="/calculators/">See planned calculators and tools →</a></p><!-- /wp:paragraph --></div><!-- /wp:column --></div><!-- /wp:columns --><!-- wp:group {"align":"wide","className":"phase2b2-other-hubs","layout":{"type":"constrained"}} --><div class="wp-block-group alignwide phase2b2-other-hubs"><!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Compare other farming models</h2><!-- /wp:heading --><!-- wp:list --><ul class="wp-block-list">' . $other_links . '</ul><!-- /wp:list --></div><!-- /wp:group --></div><!-- /wp:group -->';
}

$fixture_categories = array(
	'Kienyeji' => 'Guides for indigenous and improved Kienyeji chicken farming.',
	'Layers'   => 'Guides for commercial egg-production planning and management.',
	'Broilers' => 'Guides for planned broiler batches and meat-bird management.',
	'Hatching' => 'Guides for incubation, hatch preparation and early chick care.',
	'Housing'  => 'Guides for practical poultry housing decisions.',
	'Records'  => 'Guides for flock records and production monitoring.',
);

$category_ids = array();
foreach ( $fixture_categories as $name => $description ) {
	$term = term_exists( $name, 'category' );
	if ( ! $term ) {
		$term = wp_insert_term( $name, 'category', array( 'description' => $description ) );
	}
	if ( ! is_wp_error( $term ) ) {
		$category_ids[ $name ] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
	}
}

$default_post = get_post( 1 );
if ( $default_post && 'post' === $default_post->post_type && ( 'hello-world' === $default_post->post_name || 'Hello world!' === $default_post->post_title ) ) {
	wp_delete_post( 1, true );
}

$fixture_user = get_user_by( 'id', 1 );
if ( $fixture_user ) {
	wp_update_user(
		array(
			'ID'           => 1,
			'display_name' => 'Fixture Editorial Team',
			'description'  => 'Synthetic local fixture author used only to verify Poultry Farmer article attribution and biography layouts.',
		)
	);
}

$home_id    = pf_fixture_page( 'home', 'Home', 'Local synthetic homepage fixture for visual testing.', '<!-- wp:paragraph --><p>Local homepage fixture.</p><!-- /wp:paragraph -->' );
$guides_id  = pf_fixture_page( 'guides', 'Guides', 'Practical poultry farming guides for Kenyan readers.', '' );
$farming_id = pf_fixture_page( 'farming', 'Choose a Poultry Farming Model', 'Compare the main poultry production models and find the hub that fits your flock, management capacity and intended market.', '<!-- wp:paragraph --><p>Farming directory content is supplied by the page template.</p><!-- /wp:paragraph -->' );

$hub_data = array(
	'kienyeji' => array(
		'Kienyeji Chicken Farming in Kenya',
		'Kienyeji farming may use indigenous or improved birds for meat, eggs or a mixed purpose. The model usually requires planning for a longer production cycle, suitable ranging or confined systems, and buyers who understand the product offered.',
		array( 'Choose indigenous or improved birds for a defined purpose.', 'Plan housing and protection for the management system.', 'Match feed, health routines and market timing to the flock.' ),
		'Kienyeji',
		'Kienyeji flocks still need consistent feeding, safe housing, flock-health observation and records even when the production system is less intensive.',
	),
	'layers' => array(
		'Layers Farming in Kenya',
		'Layer farming is organized around egg production. Reliable routines for lighting, feeding, water, egg handling, flock records and market collection are central to the production model.',
		array( 'Plan the flock size around housing and daily labour.', 'Prepare consistent feed, water, lighting and egg-handling routines.', 'Identify realistic egg buyers and record production changes.' ),
		'Layers',
		'Layer management depends on consistent daily routines, clean housing, flock-health monitoring and production records that help explain changes in output.',
	),
	'broilers' => array(
		'Broiler Farming in Kenya',
		'Broiler farming uses planned batches of meat birds. Chick quality, brooding, feed, housing conditions, flock uniformity and a clear sale window all influence how the batch is managed.',
		array( 'Prepare brooding and housing before chicks arrive.', 'Plan feed, water and health routines for the full batch.', 'Confirm the intended buyer and sale timing before placement.' ),
		'Broilers',
		'Broiler batches need close attention to brooding, housing conditions, feed and water access, health observations and records for each production cycle.',
	),
	'hatching' => array(
		'Chicken Hatching and Incubation in Kenya',
		'Hatching and chick production begins with suitable fertile eggs and careful incubation. Breeder quality, egg handling, machine checks, hatch hygiene and early chick care are separate responsibilities within this model.',
		array( 'Confirm the source and handling of fertile eggs.', 'Use documented incubation checks and hatch-hygiene routines.', 'Prepare brooding space and a plan for chicks before setting eggs.' ),
		'Hatching',
		'Hatching enterprises need records for eggs set, machine checks and hatch outcomes, plus clean brooding conditions and early chick-health observation.',
	),
);

$hub_ids = array();
foreach ( $hub_data as $slug => $data ) {
	$other_links = '';
	foreach ( $hub_data as $other_slug => $other_data ) {
		if ( $slug !== $other_slug ) {
			$other_links .= '<li><a href="/farming/' . esc_attr( $other_slug ) . '/">' . esc_html( $other_data[0] ) . '</a></li>';
		}
	}
	$content          = pf_fixture_hub_content( $data[3], $data[1], $data[2], $category_ids[ $data[3] ], $data[4], $other_links );
	$hub_ids[ $slug ] = pf_fixture_page( 'farming/' . $slug, $data[0], 'Synthetic local hub fixture. ' . $data[1], $content, $farming_id, 'poultry-hub' );
}

$management_content = '<!-- wp:group {"align":"wide","className":"phase2b2-directory-grid","layout":{"type":"constrained"}} --><div class="wp-block-group alignwide phase2b2-directory-grid"><!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Build dependable daily systems</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Use these local fixture hubs to organize practical feeding, housing, health, record-keeping and production-monitoring guidance.</p><!-- /wp:paragraph --><!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading"><a href="/management/feeding/">Feeding</a></h3><!-- /wp:heading --><p>Plan feed and water routines around the flock and production stage.</p></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading"><a href="/management/housing/">Housing</a></h3><!-- /wp:heading --><p>Organize space, ventilation, litter, security and cleaning.</p></div><!-- /wp:column --></div><!-- /wp:columns --><!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading"><a href="/management/health/">Flock health</a></h3><!-- /wp:heading --><p>Observe the flock and document health routines without inventing veterinary claims.</p></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading"><a href="/management/records/">Records</a></h3><!-- /wp:heading --><p>Track inputs, observations and production consistently.</p></div><!-- /wp:column --></div><!-- /wp:columns --></div><!-- /wp:group -->';
$management_id      = pf_fixture_page( 'management', 'Farm Management', 'Practical systems for people already operating poultry farms.', $management_content, 0, 'section-hub' );
foreach ( array( 'feeding' => 'Feeding and Water', 'housing' => 'Poultry Housing in Kenya', 'health' => 'Flock Health', 'records' => 'Poultry Farm Records' ) as $slug => $title ) {
	pf_fixture_page( 'management/' . $slug, $title, 'Synthetic local management-topic fixture.', '<!-- wp:paragraph --><p>This synthetic fixture demonstrates the editorial route. Reviewed production guidance will replace it before publication.</p><!-- /wp:paragraph --><!-- wp:heading {"level":2} --><h2 class="wp-block-heading">What this topic will cover</h2><!-- /wp:heading --><!-- wp:list --><ul class="wp-block-list"><li>Planning decisions</li><li>Daily routines</li><li>Records and source notes</li></ul><!-- /wp:list -->', $management_id );
}

pf_fixture_page( 'prices', 'Poultry Prices and Markets', 'A future home for verified, dated poultry market context.', '<!-- wp:paragraph --><p>No current price is published in this local fixture. A documented reporting process is required before market figures appear.</p><!-- /wp:paragraph -->', 0, 'section-hub' );
pf_fixture_page( 'calculators', 'Poultry Calculators', 'Financial calculators remain planned and are not active in this phase.', '<!-- wp:paragraph --><p>No calculator or financial result is active. Calculator logic belongs in the future poultryfarmer-core plugin.</p><!-- /wp:paragraph -->', 0, 'section-hub' );

$article_content = '<!-- wp:paragraph {"backgroundColor":"brand-soft","className":"fixture-notice"} --><p class="fixture-notice has-brand-soft-background-color has-background"><strong>Synthetic local fixture:</strong> This article exists only to test the Poultry Farmer editorial system. It contains no verified market prices, production statistics, interviews or income claims.</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Decide what the farm is meant to produce</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Start with a defined production model and intended customer. The choice affects housing, daily routines, working-capital needs and the type of records the farm should keep.</p><!-- /wp:paragraph --><!-- wp:table --><figure class="wp-block-table"><table><thead><tr><th>Model</th><th>Primary output</th><th>Planning focus</th></tr></thead><tbody><tr><th scope="row">Kienyeji</th><td>Meat, eggs or both</td><td>Bird type, system and market fit</td></tr><tr><th scope="row">Layers</th><td>Eggs</td><td>Daily consistency and records</td></tr><tr><th scope="row">Broilers</th><td>Meat birds</td><td>Batch planning and sale timing</td></tr><tr><th scope="row">Hatching</th><td>Chicks</td><td>Egg quality, incubation and brooding</td></tr></tbody></table><figcaption>Editorial layout example only; it contains no performance or profitability claims.</figcaption></figure><!-- /wp:table --><!-- wp:heading --><h2 class="wp-block-heading">Prepare the operating system before birds arrive</h2><!-- /wp:heading --><!-- wp:list --><ul class="wp-block-list"><li>Confirm housing, water and feed-storage capacity.</li><li>Define health-observation and record-keeping routines.</li><li>Document the assumptions that still require a current source.</li></ul><!-- /wp:list --><!-- wp:heading --><h2 class="wp-block-heading">Sources and methodology</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong>Fixture source:</strong> Poultry Farmer local editorial test plan, published 19 November 2026. This source validates layout and attribution only; it is not production farming evidence.</p><!-- /wp:paragraph --><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Limitations</h3><!-- /wp:heading --><!-- wp:paragraph --><p>The fixture deliberately omits costs, prices, stocking rates, veterinary instructions and expected returns. Editors must add dated, attributable sources before publishing factual production guidance.</p><!-- /wp:paragraph -->';
$startup_id       = pf_fixture_post( 'how-to-start-poultry-farming-kenya', 'How to Start Poultry Farming in Kenya: Planning Checklist', 'Synthetic local article fixture for testing a detailed startup guide layout.', $article_content, array( $category_ids['Kienyeji'], $category_ids['Housing'], $category_ids['Records'] ) );

pf_fixture_post( 'kienyeji-housing-basics', 'Kienyeji Housing: Questions to Resolve Before Building', 'Synthetic Kienyeji guide fixture with no dimensions or cost claims.', '<!-- wp:paragraph --><p><strong>Synthetic fixture:</strong> Review shelter, security, ventilation, cleaning and the chosen management system before building.</p><!-- /wp:paragraph -->', array( $category_ids['Kienyeji'], $category_ids['Housing'] ) );
pf_fixture_post( 'layer-records-routine', 'Layer Records: A Simple Daily Review Structure', 'Synthetic layer-management guide fixture.', '<!-- wp:paragraph --><p><strong>Synthetic fixture:</strong> Organize consistent observations without publishing sample performance figures as facts.</p><!-- /wp:paragraph -->', array( $category_ids['Layers'], $category_ids['Records'] ) );
pf_fixture_post( 'broiler-batch-planning', 'Broiler Batch Planning: What to Prepare Before Placement', 'Synthetic broiler guide fixture.', '<!-- wp:paragraph --><p><strong>Synthetic fixture:</strong> Prepare brooding, feed, water, routines and a route to market before chicks arrive.</p><!-- /wp:paragraph -->', array( $category_ids['Broilers'] ) );
pf_fixture_post( 'hatching-preparation-checklist', 'Hatching Preparation: An Editor-Test Checklist', 'Synthetic hatching guide fixture.', '<!-- wp:paragraph --><p><strong>Synthetic fixture:</strong> Confirm egg handling, machine checks, hatch hygiene and brooding preparation.</p><!-- /wp:paragraph -->', array( $category_ids['Hatching'] ) );

$legacy_article = get_page_by_path( 'local-sample-editorial-article', OBJECT, 'post' );
if ( $legacy_article ) {
	wp_update_post( array( 'ID' => $legacy_article->ID, 'post_status' => 'draft' ) );
}

foreach ( array( 'kienyeji-farming', 'layers-farming', 'broilers-farming', 'hatching' ) as $legacy_page_slug ) {
	$legacy_page = get_page_by_path( $legacy_page_slug, OBJECT, 'page' );
	if ( $legacy_page ) {
		wp_update_post( array( 'ID' => $legacy_page->ID, 'post_status' => 'draft' ) );
	}
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home_id );
update_option( 'page_for_posts', $guides_id );
update_option( 'permalink_structure', '/guides/%postname%/' );
update_option( 'pf_phase2b2_fixture', '1' );
flush_rewrite_rules();
