<?php
/**
 * Block pattern registration.
 *
 * @package Poultry Farmer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Poultry Farmer pattern category.
 */
function poultryfarmer_register_pattern_categories() {
	register_block_pattern_category(
		'poultryfarmer',
		array(
			'label' => __( 'Poultry Farmer', 'poultryfarmer' ),
		)
	);
}
add_action( 'init', 'poultryfarmer_register_pattern_categories' );
