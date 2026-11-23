<?php
/**
 * Theme setup.
 *
 * @package Poultry Farmer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme features.
 */
function poultryfarmer_setup() {
	add_theme_support( 'wp-block-styles' );
	add_editor_style( 'assets/css/theme.css' );
}
add_action( 'after_setup_theme', 'poultryfarmer_setup' );
