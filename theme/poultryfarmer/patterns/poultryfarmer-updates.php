<?php
/**
 * Title: Get Poultry Farmer Updates — Article
 * Slug: poultryfarmer/poultryfarmer-updates
 * Categories: poultryfarmer, call-to-action
 * Description: A compact provider-neutral newsletter signup area for articles.
 * Inserter: yes
 */
?>
<!-- wp:group {"className":"article-newsletter","backgroundColor":"brand-dark","textColor":"surface","style":{"spacing":{"padding":{"top":"32px","right":"32px","bottom":"32px","left":"32px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group article-newsletter has-surface-color has-brand-dark-background-color has-text-color has-background" style="padding-top:32px;padding-right:32px;padding-bottom:32px;padding-left:32px">
	<!-- wp:columns {"className":"article-newsletter-layout","style":{"spacing":{"blockGap":{"left":"32px"}}}} -->
	<div class="wp-block-columns article-newsletter-layout">
		<!-- wp:column {"width":"58%"} -->
		<div class="wp-block-column" style="flex-basis:58%">
			<!-- wp:paragraph {"textColor":"brand","className":"article-newsletter-eyebrow"} -->
			<p class="article-newsletter-eyebrow has-brand-color has-text-color"><?php esc_html_e( 'POULTRY FARMER', 'poultryfarmer' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:heading {"level":2,"textColor":"surface","className":"article-newsletter-heading"} -->
			<h2 class="wp-block-heading article-newsletter-heading has-surface-color has-text-color"><?php esc_html_e( 'Get Poultry Farmer Updates', 'poultryfarmer' ); ?></h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"textColor":"surface","className":"article-newsletter-copy"} -->
			<p class="article-newsletter-copy has-surface-color has-text-color"><?php esc_html_e( "One useful briefing on Kenya's agricultural markets, data and business opportunities.", 'poultryfarmer' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"width":"42%"} -->
		<div class="wp-block-column" style="flex-basis:42%">
			<!-- wp:group {"className":"article-newsletter-form-slot","layout":{"type":"constrained"}} -->
			<div class="wp-block-group article-newsletter-form-slot"></div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
