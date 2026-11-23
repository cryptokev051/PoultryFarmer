<?php
/**
 * Related-analysis rendering.
 *
 * @package Poultry Farmer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render one related-analysis card without changing the global post state.
 *
 * @param WP_Post $post Related post object.
 * @return string
 */
function poultryfarmer_render_related_analysis_card( $post ) {
	$post_id       = (int) $post->ID;
	$title         = get_the_title( $post_id );
	$permalink     = get_permalink( $post_id );
	$excerpt       = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post ) ), 24, '...' );
	$date          = get_the_date( 'j F Y', $post_id );
	$datetime      = get_the_date( DATE_W3C, $post_id );
	$categories    = get_the_category( $post_id );
	$category_html = '';

	if ( ! empty( $categories ) ) {
		$category_link = get_category_link( $categories[0]->term_id );

		if ( ! is_wp_error( $category_link ) ) {
			$category_html = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $category_link ),
				esc_html( $categories[0]->name )
			);
		}
	}

	$thumbnail_id = get_post_thumbnail_id( $post_id );

	if ( $thumbnail_id ) {
		$image_html = wp_get_attachment_image(
			$thumbnail_id,
			'medium_large',
			false,
			array(
				'alt'      => '',
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);
	} else {
		$image_html = sprintf(
			'<img src="%1$s" alt="" loading="lazy" decoding="async" width="1280" height="720">',
			esc_url( get_theme_file_uri( 'assets/images/analysis-placeholder.svg' ) )
		);
	}

	return sprintf(
		'<article class="poultryfarmer-card related-analysis-card">' .
			'<a class="related-analysis-image" href="%1$s">%2$s</a>' .
			'<div class="related-analysis-card-content">' .
				'<div class="related-analysis-category">%3$s</div>' .
				'<h3 class="related-analysis-title"><a href="%1$s">%4$s</a></h3>' .
				'<p class="related-analysis-excerpt">%5$s</p>' .
				'<time class="related-analysis-date" datetime="%6$s">%7$s</time>' .
			'</div>' .
		'</article>',
		esc_url( $permalink ),
		wp_kses_post( $image_html ),
		$category_html,
		esc_html( $title ),
		esc_html( $excerpt ),
		esc_attr( $datetime ),
		esc_html( $date )
	);
}

/**
 * Replace the Related Guides marker with category-related posts only.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Parsed block data.
 * @return string
 */
function poultryfarmer_render_related_analysis( $block_content, $block ) {
	$class_name = isset( $block['attrs']['className'] ) ? $block['attrs']['className'] : '';
	$classes    = preg_split( '/\s+/', trim( $class_name ) );

	if ( ! is_singular( 'post' ) || ! is_array( $classes ) || ! in_array( 'related-analysis-results', $classes, true ) ) {
		return $block_content;
	}

	$current_post_id = (int) get_queried_object_id();

	if ( ! $current_post_id ) {
		return $block_content;
	}

	$category_ids = wp_get_post_categories( $current_post_id, array( 'fields' => 'ids' ) );

	if ( is_wp_error( $category_ids ) ) {
		$category_ids = array();
	}

	$query_args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 3,
		'post__not_in'        => array( $current_post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
	);

	if ( ! empty( $category_ids ) ) {
		$query_args['category__in'] = array_map( 'intval', $category_ids );
	}

	$related_query  = new WP_Query( $query_args );
	$selected_posts = ! empty( $category_ids ) ? $related_query->posts : array();

	/**
	 * Filters related posts before rendering.
	 *
	 * A companion plugin can replace the category-based results with explicit
	 * relationships by returning an array of published WP_Post objects.
	 *
	 * @param WP_Post[] $selected_posts  Related posts.
	 * @param int       $current_post_id Current post ID.
	 * @param int       $maximum_posts   Maximum number of cards rendered.
	 */
	$selected_posts = apply_filters( 'poultryfarmer_related_analysis_posts', $selected_posts, $current_post_id, 3 );

	if ( ! is_array( $selected_posts ) ) {
		return $block_content;
	}

	$selected_posts = array_values(
		array_filter(
			$selected_posts,
			static function ( $related_post ) use ( $current_post_id ) {
				return $related_post instanceof WP_Post
					&& $current_post_id !== (int) $related_post->ID
					&& 'post' === $related_post->post_type
					&& 'publish' === $related_post->post_status;
			}
		)
	);

	if ( empty( $selected_posts ) ) {
		return '<div class="wp-block-group related-analysis-results related-guides-empty"><p>' . esc_html__( 'No additional guides are published in this topic yet.', 'poultryfarmer' ) . '</p><p><a href="' . esc_url( home_url( '/guides/' ) ) . '">' . esc_html__( 'Browse all guides', 'poultryfarmer' ) . '</a></p></div>';
	}

	$cards = array();

	foreach ( array_slice( $selected_posts, 0, 3 ) as $related_post ) {
		$cards[] = poultryfarmer_render_related_analysis_card( $related_post );
	}

	return '<div class="wp-block-group related-analysis-results"><div class="related-analysis-grid">' . implode( '', $cards ) . '</div></div>';
}
add_filter( 'render_block_core/group', 'poultryfarmer_render_related_analysis', 10, 2 );
