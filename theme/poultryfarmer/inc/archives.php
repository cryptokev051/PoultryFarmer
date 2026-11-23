<?php
/**
 * Archive and index behavior.
 *
 * @package Poultry Farmer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the native author description in the general archive header.
 *
 * The Term Description block handles taxonomy archives but not author archives.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Parsed block data.
 * @return string
 */
function poultryfarmer_render_general_archive_description( $block_content, $block ) {
	$class_name = $block['attrs']['className'] ?? '';

	if ( ! is_archive() || false === strpos( $class_name, 'general-archive-description' ) || '' !== trim( $block_content ) ) {
		return $block_content;
	}

	$description = get_the_archive_description();

	if ( '' === trim( wp_strip_all_tags( $description ) ) ) {
		return '';
	}

	return '<div class="wp-block-term-description general-archive-description has-muted-color has-text-color has-large-font-size">' . wp_kses_post( $description ) . '</div>';
}
add_filter( 'render_block_core/term-description', 'poultryfarmer_render_general_archive_description', 10, 2 );

/**
 * Keep archive card excerpts concise.
 *
 * @param int $length Default excerpt length.
 * @return int
 */
function poultryfarmer_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}

	return 26;
}
add_filter( 'excerpt_length', 'poultryfarmer_excerpt_length', 20 );

/**
 * Display nine posts on the main analysis index without changing other archives.
 *
 * @param WP_Query $query The main WordPress query.
 */
function poultryfarmer_blog_posts_per_page( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_home() ) {
		return;
	}

	$query->set( 'posts_per_page', 9 );
}
add_action( 'pre_get_posts', 'poultryfarmer_blog_posts_per_page' );

/**
 * Give editorial cards a quiet fallback when a post has no featured image.
 *
 * The fallback is deliberately decorative: the linked article title immediately
 * follows it and remains the accessible name for the destination.
 *
 * @param string $block_content Rendered featured-image block.
 * @param array  $block         Parsed block data.
 * @return string
 */
function poultryfarmer_render_card_image_fallback( $block_content, $block ) {
	if ( '' !== trim( $block_content ) || is_admin() ) {
		return $block_content;
	}

	$class_name   = $block['attrs']['className'] ?? '';
	$card_classes = array(
		'blog-card-image',
		'category-card-image',
		'general-archive-card-image',
		'search-result-image',
		'error-404-analysis-image',
		'homepage-kit-image',
	);

	$uses_card_image = false;
	foreach ( $card_classes as $card_class ) {
		if ( false !== strpos( $class_name, $card_class ) ) {
			$uses_card_image = true;
			break;
		}
	}

	if ( ! $uses_card_image ) {
		return $block_content;
	}

	$post_id = isset( $block['context']['postId'] ) ? absint( $block['context']['postId'] ) : get_the_ID();
	if ( ! $post_id ) {
		return $block_content;
	}

	$classes = trim( 'wp-block-post-featured-image poultryfarmer-card-image-placeholder ' . $class_name );

	return sprintf(
		'<figure class="%1$s"><a href="%2$s" aria-hidden="true" tabindex="-1"><img src="%3$s" alt="" loading="lazy" decoding="async"></a></figure>',
		esc_attr( $classes ),
		esc_url( get_permalink( $post_id ) ),
		esc_url( get_theme_file_uri( 'assets/images/analysis-placeholder.svg' ) )
	);
}
add_filter( 'render_block_core/post-featured-image', 'poultryfarmer_render_card_image_fallback', 10, 2 );
