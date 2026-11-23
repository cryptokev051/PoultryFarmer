<?php
/**
 * Poultry Farmer theme bootstrap.
 *
 * @package Poultry Farmer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$poultryfarmer_includes = array(
	'inc/setup.php',
	'inc/assets.php',
	'inc/patterns.php',
	'inc/archives.php',
	'inc/related-analysis.php',
	'inc/editorial.php',
	'inc/woocommerce.php',
);

foreach ( $poultryfarmer_includes as $poultryfarmer_include ) {
	require_once get_theme_file_path( $poultryfarmer_include );
}
