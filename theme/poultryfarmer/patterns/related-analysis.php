<?php
/**
 * Title: Related Guides
 * Slug: poultryfarmer/related-analysis
 * Categories: poultryfarmer
 * Description: Dynamic category-based related guides with a useful empty state.
 * Inserter: yes
 */
?>
<!-- wp:group {"align":"wide","className":"related-analysis","style":{"spacing":{"margin":{"top":"64px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide related-analysis" style="margin-top:64px">
	<!-- wp:group {"className":"related-analysis-header","layout":{"type":"constrained","justifyContent":"left"}} --><div class="wp-block-group related-analysis-header"><!-- wp:heading {"level":2,"className":"related-analysis-heading"} --><h2 class="wp-block-heading related-analysis-heading"><?php esc_html_e( 'Related Guides', 'poultryfarmer' ); ?></h2><!-- /wp:heading --><!-- wp:paragraph {"textColor":"muted","className":"related-analysis-description"} --><p class="related-analysis-description has-muted-color has-text-color"><?php esc_html_e( 'Continue with practical guidance relevant to this farming decision.', 'poultryfarmer' ); ?></p><!-- /wp:paragraph --></div><!-- /wp:group -->
	<!-- wp:group {"className":"related-analysis-results","layout":{"type":"default"}} --><div class="wp-block-group related-analysis-results"></div><!-- /wp:group -->
</div>
<!-- /wp:group -->
