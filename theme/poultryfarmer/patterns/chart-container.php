<?php
/**
 * Title: Chart Container
 * Slug: poultryfarmer/chart-container
 * Categories: poultryfarmer
 * Description: Accessible presentation infrastructure for a chart or other visualization.
 * Inserter: yes
 */
?>
<!-- wp:group {"align":"wide","className":"research-section chart-container","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide research-section chart-container">
	<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e( 'Chart title', 'poultryfarmer' ); ?></h2><!-- /wp:heading -->
	<!-- wp:paragraph {"textColor":"muted","className":"chart-subtitle"} --><p class="chart-subtitle has-muted-color has-text-color"><?php esc_html_e( 'Explain the comparison, unit and period shown.', 'poultryfarmer' ); ?></p><!-- /wp:paragraph -->
	<!-- wp:group {"className":"chart-visualization","layout":{"type":"constrained"}} --><div class="wp-block-group chart-visualization"><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><?php esc_html_e( 'Insert an accessible SVG, image, shortcode or chart block here.', 'poultryfarmer' ); ?></p><!-- /wp:paragraph --></div><!-- /wp:group -->
	<!-- wp:group {"className":"chart-accessible-summary","layout":{"type":"constrained"}} --><div class="wp-block-group chart-accessible-summary"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading"><?php esc_html_e( 'What the chart shows', 'poultryfarmer' ); ?></h3><!-- /wp:heading --><!-- wp:paragraph --><p><?php esc_html_e( 'Provide a concise text explanation of the main pattern and material exceptions.', 'poultryfarmer' ); ?></p><!-- /wp:paragraph --></div><!-- /wp:group -->
	<!-- wp:paragraph {"className":"chart-meta"} --><p class="chart-meta"><strong><?php esc_html_e( 'Units:', 'poultryfarmer' ); ?></strong> <?php esc_html_e( 'Add units', 'poultryfarmer' ); ?> · <strong><?php esc_html_e( 'Data period:', 'poultryfarmer' ); ?></strong> <?php esc_html_e( 'Add period', 'poultryfarmer' ); ?> · <strong><?php esc_html_e( 'Source:', 'poultryfarmer' ); ?></strong> <?php esc_html_e( 'Add source and methodology link', 'poultryfarmer' ); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
