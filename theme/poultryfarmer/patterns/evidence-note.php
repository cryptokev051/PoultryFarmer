<?php
/**
 * Title: Evidence Note
 * Slug: poultryfarmer/evidence-note
 * Categories: poultryfarmer
 * Description: A concise editorial note for evidence, assumptions or warnings.
 * Inserter: yes
 */
?>
<!-- wp:group {"className":"evidence-note","backgroundColor":"brand-soft","textColor":"brand-dark","style":{"border":{"radius":"12px"},"spacing":{"padding":{"top":"24px","right":"24px","bottom":"24px","left":"24px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group evidence-note has-brand-dark-color has-brand-soft-background-color has-text-color has-background" style="border-radius:12px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px">
	<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.1rem"}}} -->
	<h3 class="wp-block-heading" style="font-size:1.1rem"><?php esc_html_e( 'EVIDENCE NOTE', 'poultryfarmer' ); ?></h3>
	<!-- /wp:heading -->
	<!-- wp:paragraph -->
	<p><?php esc_html_e( 'State whether this information is official data, observed evidence, an assumption, a calculation, an estimate, a scenario or historical context.', 'poultryfarmer' ); ?></p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
