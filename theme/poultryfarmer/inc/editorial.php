<?php
/**
 * Editorial presentation helpers.
 *
 * @package Poultry Farmer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render native breadcrumbs into an editor-visible marker block.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Parsed block data.
 * @return string
 */
function poultryfarmer_render_breadcrumbs( $block_content, $block ) {
	$class_name = $block['attrs']['className'] ?? '';

	if ( false === strpos( $class_name, 'poultryfarmer-breadcrumbs' ) || is_front_page() ) {
		return $block_content;
	}

	$items = array(
		array(
			'label' => __( 'Home', 'poultryfarmer' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( is_singular( 'post' ) ) {
		$posts_page_id = (int) get_option( 'page_for_posts' );
		$items[]       = array(
			'label' => $posts_page_id ? get_the_title( $posts_page_id ) : __( 'Guides', 'poultryfarmer' ),
			'url'   => $posts_page_id ? get_permalink( $posts_page_id ) : home_url( '/guides/' ),
		);
		$categories = get_the_category();
		if ( ! empty( $categories ) ) {
			$category_link = get_category_link( $categories[0] );
			if ( ! is_wp_error( $category_link ) ) {
				$items[] = array( 'label' => $categories[0]->name, 'url' => $category_link );
			}
		}
		$items[] = array( 'label' => get_the_title(), 'url' => '' );
	} elseif ( is_page() ) {
		$page_id   = get_queried_object_id();
		$ancestors = array_reverse( get_post_ancestors( $page_id ) );
		foreach ( $ancestors as $ancestor_id ) {
			$items[] = array( 'label' => get_the_title( $ancestor_id ), 'url' => get_permalink( $ancestor_id ) );
		}
		$items[] = array( 'label' => get_the_title( $page_id ), 'url' => '' );
	}

	if ( count( $items ) < 2 ) {
		return $block_content;
	}

	$parts = array();
	foreach ( $items as $index => $item ) {
		if ( '' !== $item['url'] ) {
			$parts[] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $item['url'] ), esc_html( $item['label'] ) );
		} else {
			$parts[] = sprintf( '<span aria-current="page">%s</span>', esc_html( $item['label'] ) );
		}
	}

	return '<nav class="poultryfarmer-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'poultryfarmer' ) . '">' . implode( '<span aria-hidden="true">/</span>', $parts ) . '</nav>';
}
add_filter( 'render_block_core/group', 'poultryfarmer_render_breadcrumbs', 9, 2 );

/**
 * Render an author profile only when the author supplied a useful biography.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Parsed block data.
 * @return string
 */
function poultryfarmer_render_author_profile( $block_content, $block ) {
	$class_name = $block['attrs']['className'] ?? '';

	if ( ! is_singular( 'post' ) || false === strpos( $class_name, 'article-author-profile' ) ) {
		return $block_content;
	}

	$author_id = (int) get_post_field( 'post_author', get_queried_object_id() );
	$bio       = trim( (string) get_the_author_meta( 'description', $author_id ) );

	if ( '' === $bio ) {
		return '';
	}

	return sprintf(
		'<aside class="article-author-profile" aria-labelledby="article-author-heading"><p class="phase2b1-eyebrow">%1$s</p><h2 id="article-author-heading">%2$s</h2>%3$s</aside>',
		esc_html__( 'About the author', 'poultryfarmer' ),
		esc_html( get_the_author_meta( 'display_name', $author_id ) ),
		wp_kses_post( wpautop( $bio ) )
	);
}
add_filter( 'render_block_core/group', 'poultryfarmer_render_author_profile', 10, 2 );

/**
 * Render an updated date only when it is later than the publication date.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Parsed block data.
 * @return string
 */
function poultryfarmer_render_updated_date( $block_content, $block ) {
	$class_name = $block['attrs']['className'] ?? '';

	if ( ! is_singular( 'post' ) || false === strpos( $class_name, 'article-updated-meta' ) ) {
		return $block_content;
	}

	$post_id   = get_queried_object_id();
	$published = (int) get_post_time( 'U', true, $post_id );
	$modified  = (int) get_post_modified_time( 'U', true, $post_id );

	if ( ! $published || ! $modified || $modified <= $published ) {
		return '';
	}

	return sprintf(
		'<span class="article-updated-meta"><span class="article-meta-separator" aria-hidden="true">·</span><span>%1$s</span><time datetime="%2$s">%3$s</time></span>',
		esc_html__( 'Updated', 'poultryfarmer' ),
		esc_attr( get_post_modified_time( DATE_W3C, true, $post_id ) ),
		esc_html( get_the_modified_date( 'j F Y', $post_id ) )
	);
}
add_filter( 'render_block_core/group', 'poultryfarmer_render_updated_date', 11, 2 );
