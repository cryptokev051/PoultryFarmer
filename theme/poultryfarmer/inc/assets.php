<?php
/**
 * Front-end assets.
 *
 * @package Poultry Farmer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue the production stylesheet.
 */
function poultryfarmer_enqueue_assets() {
	$version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'poultryfarmer-theme',
		get_theme_file_uri( 'assets/css/theme.css' ),
		array(),
		$version
	);
}
add_action( 'wp_enqueue_scripts', 'poultryfarmer_enqueue_assets' );
