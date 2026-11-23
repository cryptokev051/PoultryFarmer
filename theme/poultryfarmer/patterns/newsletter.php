<?php
/**
 * Title: Get Poultry Farmer Updates
 * Slug: poultryfarmer/newsletter
 * Categories: poultryfarmer, call-to-action
 * Description: A provider-neutral newsletter signup area for prominent page placement.
 * Inserter: yes
 */
?>
<!-- wp:group {"anchor":"poultryfarmer-updates","className":"newsletter","backgroundColor":"brand-dark","textColor":"surface","style":{"border":{"radius":"22px"},"spacing":{"padding":{"top":"48px","right":"clamp(24px,5vw,64px)","bottom":"48px","left":"clamp(24px,5vw,64px)"}}},"layout":{"type":"constrained"}} -->
<div id="poultryfarmer-updates" class="wp-block-group newsletter has-surface-color has-brand-dark-background-color has-text-color has-background" style="border-radius:22px;padding-top:48px;padding-right:clamp(24px,5vw,64px);padding-bottom:48px;padding-left:clamp(24px,5vw,64px)">
	<!-- wp:paragraph {"textColor":"brand","className":"poultryfarmer-eyebrow newsletter-eyebrow","style":{"typography":{"fontSize":"0.78rem","fontWeight":"750"}}} -->
	<p class="poultryfarmer-eyebrow newsletter-eyebrow has-brand-color has-text-color" style="font-size:0.78rem;font-weight:750"><?php esc_html_e( 'POULTRY FARMER', 'poultryfarmer' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:heading {"textColor":"surface","fontSize":"x-large","className":"newsletter-heading"} -->
	<h2 class="wp-block-heading newsletter-heading has-surface-color has-text-color has-x-large-font-size"><?php esc_html_e( 'Get Poultry Farmer Updates', 'poultryfarmer' ); ?></h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"textColor":"surface","fontSize":"large","className":"newsletter-copy"} -->
	<p class="newsletter-copy has-surface-color has-text-color has-large-font-size"><?php esc_html_e( "One useful briefing on Kenya's agricultural markets, data and business opportunities.", 'poultryfarmer' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:group {"className":"newsletter-form-slot","layout":{"type":"constrained"}} -->
	<div class="wp-block-group newsletter-form-slot"></div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
