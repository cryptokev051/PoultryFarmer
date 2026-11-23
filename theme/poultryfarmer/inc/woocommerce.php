<?php
/**
 * WooCommerce integration for Poultry Farmer digital products.
 *
 * @package Poultry Farmer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Declare WooCommerce support. */
function poultryfarmer_woocommerce_setup() {
	add_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'poultryfarmer_woocommerce_setup' );

/** Keep the digital product page focused on the decision and downloadable resource. */
function poultryfarmer_woocommerce_single_product_layout() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	add_action( 'woocommerce_after_single_product_summary', 'poultryfarmer_woocommerce_product_details', 10 );
}
add_action( 'wp', 'poultryfarmer_woocommerce_single_product_layout' );

/** Explain digital delivery without promising access before payment confirmation. */
function poultryfarmer_woocommerce_product_delivery_note() {
	global $product;

	if ( ! $product instanceof WC_Product || ( ! $product->is_virtual() && ! $product->is_downloadable() ) ) {
		return;
	}
	?>
	<section class="poultryfarmer-product-delivery" aria-labelledby="poultryfarmer-product-delivery-title">
		<p class="poultryfarmer-product-delivery__label" id="poultryfarmer-product-delivery-title"><?php esc_html_e( 'Digital delivery', 'poultryfarmer' ); ?></p>
		<p><?php esc_html_e( 'Eligible files become available after WooCommerce confirms successful payment.', 'poultryfarmer' ); ?></p>
	</section>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'poultryfarmer_woocommerce_product_delivery_note', 25 );

/** Render editor-managed product details as a visible decision-support section. */
function poultryfarmer_woocommerce_product_details() {
	global $post;

	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$content = trim( (string) $post->post_content );
	if ( '' === $content ) {
		return;
	}
	?>
	<section class="poultryfarmer-product-details" aria-labelledby="poultryfarmer-product-details-title">
		<h2 id="poultryfarmer-product-details-title"><?php esc_html_e( 'Digital product details', 'poultryfarmer' ); ?></h2>
		<div class="poultryfarmer-product-details__content">
			<?php echo apply_filters( 'the_content', $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</section>
	<?php
}

/** Use decision-oriented copy for a digital product purchase. */
function poultryfarmer_woocommerce_single_add_to_cart_text( $text ) {
	global $product;

	if ( $product instanceof WC_Product && ( $product->is_virtual() || $product->is_downloadable() ) ) {
		return __( 'Get the Digital Product', 'poultryfarmer' );
	}

	return $text;
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'poultryfarmer_woocommerce_single_add_to_cart_text' );

/** Add a checkout shortcut to purchasable virtual or downloadable products. */
function poultryfarmer_woocommerce_buy_now_button() {
	global $product;

	if (
		! $product instanceof WC_Product
		|| ! in_array( $product->get_type(), array( 'simple', 'variable' ), true )
		|| ( ! $product->is_virtual() && ! $product->is_downloadable() )
	) {
		return;
	}

	$token = wp_generate_uuid4();
	?>
	<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">
	<input type="hidden" name="pf_buy_now_token" value="<?php echo esc_attr( $token ); ?>">
	<input type="hidden" name="pf_buy_now_nonce" value="<?php echo esc_attr( wp_create_nonce( 'poultryfarmer_buy_now_' . $token ) ); ?>">
	<button type="submit" name="pf_buy_now" value="1" class="button alt poultryfarmer-buy-now" aria-label="<?php echo esc_attr( sprintf( __( 'Buy %s now and continue to checkout', 'poultryfarmer' ), $product->get_name() ) ); ?>" data-label="<?php esc_attr_e( 'Buy Now', 'poultryfarmer' ); ?>" data-loading-label="<?php esc_attr_e( 'Continuing to checkout…', 'poultryfarmer' ); ?>">
		<?php esc_html_e( 'Buy Now', 'poultryfarmer' ); ?>
	</button>
	<?php
}
add_action( 'woocommerce_after_add_to_cart_button', 'poultryfarmer_woocommerce_buy_now_button' );

/** Return the validated idempotency token for a Buy Now submission. */
function poultryfarmer_woocommerce_buy_now_token() {
	if (
		! isset( $_REQUEST['pf_buy_now'], $_REQUEST['pf_buy_now_token'], $_REQUEST['pf_buy_now_nonce'] )
		|| ! is_string( $_REQUEST['pf_buy_now'] )
		|| ! is_string( $_REQUEST['pf_buy_now_token'] )
		|| ! is_string( $_REQUEST['pf_buy_now_nonce'] )
		|| '1' !== wp_unslash( $_REQUEST['pf_buy_now'] )
	) {
		return '';
	}

	$token = sanitize_text_field( wp_unslash( $_REQUEST['pf_buy_now_token'] ) );
	$nonce = sanitize_text_field( wp_unslash( $_REQUEST['pf_buy_now_nonce'] ) );

	if ( ! preg_match( '/^[a-f0-9-]{36}$/i', $token ) || ! wp_verify_nonce( $nonce, 'poultryfarmer_buy_now_' . $token ) ) {
		return '';
	}

	return $token;
}

/** Require a valid form token when the Buy Now submit control is used. */
function poultryfarmer_woocommerce_buy_now_validate( $passed ) {
	if ( isset( $_REQUEST['pf_buy_now'] ) && '' === poultryfarmer_woocommerce_buy_now_token() ) {
		wc_add_notice( __( 'Please refresh the product page and try Buy Now again.', 'poultryfarmer' ), 'error' );
		return false;
	}

	return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'poultryfarmer_woocommerce_buy_now_validate' );

/** Reject replayed Buy Now submissions before WooCommerce adds the item again. */
function poultryfarmer_woocommerce_buy_now_prevent_duplicate() {
	$token = poultryfarmer_woocommerce_buy_now_token();

	if ( '' === $token || ! WC()->session ) {
		return;
	}

	$tokens = WC()->session->get( 'poultryfarmer_buy_now_tokens', array() );
	if ( isset( $tokens[ $token ] ) ) {
		wp_safe_redirect( wc_get_checkout_url() );
		exit;
	}
}
add_action( 'wp_loaded', 'poultryfarmer_woocommerce_buy_now_prevent_duplicate', 19 );

/** Record successful Buy Now submissions for replay protection. */
function poultryfarmer_woocommerce_buy_now_record_submission() {
	$token = poultryfarmer_woocommerce_buy_now_token();

	if ( '' === $token || ! WC()->session ) {
		return;
	}

	$tokens   = WC()->session->get( 'poultryfarmer_buy_now_tokens', array() );
	$cutoff   = time() - DAY_IN_SECONDS;
	$tokens   = array_filter(
		$tokens,
		static function ( $created_at ) use ( $cutoff ) {
			return is_numeric( $created_at ) && (int) $created_at >= $cutoff;
		}
	);
	$tokens[ $token ] = time();
	WC()->session->set( 'poultryfarmer_buy_now_tokens', array_slice( $tokens, -20, null, true ) );
}
add_action( 'woocommerce_add_to_cart', 'poultryfarmer_woocommerce_buy_now_record_submission', 10, 6 );

/** Send valid Buy Now submissions directly to the configured checkout page. */
function poultryfarmer_woocommerce_buy_now_redirect( $url ) {
	return '' !== poultryfarmer_woocommerce_buy_now_token() ? wc_get_checkout_url() : $url;
}
add_filter( 'woocommerce_add_to_cart_redirect', 'poultryfarmer_woocommerce_buy_now_redirect' );

/** Disable only the Buy Now form after its first submit, including keyboard submits. */
function poultryfarmer_woocommerce_buy_now_script() {
	if ( ! is_product() ) {
		return;
	}
	?>
	<script>
		document.addEventListener('submit', function (event) {
			const button = event.submitter;
			if (!button || !button.matches('button[name="pf_buy_now"]')) return;
			if (button.dataset.submitting === 'true') {
				event.preventDefault();
				return;
			}
			button.dataset.submitting = 'true';
			button.setAttribute('aria-disabled', 'true');
			button.textContent = button.dataset.loadingLabel;
		});
		window.addEventListener('pageshow', function () {
			document.querySelectorAll('button[name="pf_buy_now"][data-submitting="true"]').forEach(function (button) {
				button.dataset.submitting = 'false';
				button.removeAttribute('aria-disabled');
				button.textContent = button.dataset.label;
			});
		});
	</script>
	<?php
}
add_action( 'wp_footer', 'poultryfarmer_woocommerce_buy_now_script', 20 );

/** Link archive cards to their digital product page. */
function poultryfarmer_woocommerce_archive_add_to_cart_text( $text, $product ) {
	return $product instanceof WC_Product ? __( 'View Product', 'poultryfarmer' ) : $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'poultryfarmer_woocommerce_archive_add_to_cart_text', 10, 2 );

/** Add concise editor-managed decision copy to digital product cards. */
function poultryfarmer_woocommerce_archive_decision_copy() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$description = wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 28 );
	if ( '' !== $description ) {
		printf( '<p class="poultryfarmer-product-card__summary">%s</p>', esc_html( $description ) );
	}
}
add_action( 'woocommerce_after_shop_loop_item_title', 'poultryfarmer_woocommerce_archive_decision_copy', 7 );

/** Put downloads where digital-product customers can find them quickly. */
function poultryfarmer_woocommerce_account_menu_items( $items ) {
	$ordered = array();

	foreach ( array( 'dashboard', 'downloads', 'orders', 'edit-address', 'payment-methods', 'edit-account', 'customer-logout' ) as $endpoint ) {
		if ( isset( $items[ $endpoint ] ) ) {
			$ordered[ $endpoint ] = $items[ $endpoint ];
		}
	}

	return $ordered + $items;
}
add_filter( 'woocommerce_account_menu_items', 'poultryfarmer_woocommerce_account_menu_items' );

/** Explain order state without promising access before WooCommerce grants it. */
function poultryfarmer_woocommerce_order_access_panel( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$downloads = $order->is_download_permitted() ? $order->get_downloadable_items() : array();
	$status    = $order->get_status();

	if ( ! empty( $downloads ) ) {
		$eyebrow = __( 'Payment confirmed', 'poultryfarmer' );
		$title   = __( 'Your files are ready', 'poultryfarmer' );
		$message = __( 'Download your digital product below. You can also return to My Account to access eligible files again.', 'poultryfarmer' );
		$tone    = 'confirmed';
	} elseif ( in_array( $status, array( 'pending', 'on-hold' ), true ) ) {
		$eyebrow = __( 'Payment pending', 'poultryfarmer' );
		$title   = __( 'We are waiting for payment confirmation', 'poultryfarmer' );
		$message = __( 'Your download will become available after WooCommerce confirms payment. Keep this order number for reference.', 'poultryfarmer' );
		$tone    = 'pending';
	} elseif ( 'failed' === $status ) {
		$eyebrow = __( 'Payment not completed', 'poultryfarmer' );
		$title   = __( 'Your files are not available yet', 'poultryfarmer' );
		$message = __( 'The payment was not confirmed. Try again from your order or contact support if money left your account.', 'poultryfarmer' );
		$tone    = 'attention';
	} elseif ( in_array( $status, array( 'cancelled', 'refunded' ), true ) ) {
		$eyebrow = __( 'Order closed', 'poultryfarmer' );
		$title   = __( 'Download access is not available', 'poultryfarmer' );
		$message = __( 'This order was cancelled or refunded. Contact support if the order status does not match your records.', 'poultryfarmer' );
		$tone    = 'attention';
	} else {
		$eyebrow = __( 'Order received', 'poultryfarmer' );
		$title   = __( 'We are preparing your access', 'poultryfarmer' );
		$message = __( 'Check your order details below. Eligible downloads appear only after payment and order validation are complete.', 'poultryfarmer' );
		$tone    = 'pending';
	}
	?>
	<section class="poultryfarmer-order-access poultryfarmer-order-access--<?php echo esc_attr( $tone ); ?>" aria-labelledby="poultryfarmer-order-access-title">
		<p class="poultryfarmer-order-access__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<h2 id="poultryfarmer-order-access-title"><?php echo esc_html( $title ); ?></h2>
		<p><?php echo esc_html( $message ); ?></p>
		<?php if ( ! empty( $downloads ) ) : ?>
			<ul class="poultryfarmer-order-access__downloads">
				<?php foreach ( $downloads as $download ) : ?>
					<li><a class="button" href="<?php echo esc_url( $download['download_url'] ); ?>">
						<?php printf( esc_html__( 'Download %s', 'poultryfarmer' ), esc_html( $download['download_name'] ) ); ?>
					</a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<div class="poultryfarmer-order-access__links">
			<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'downloads' ) ); ?>"><?php esc_html_e( 'My downloads', 'poultryfarmer' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact support', 'poultryfarmer' ); ?></a>
		</div>
	</section>
	<?php
}
add_action( 'woocommerce_thankyou', 'poultryfarmer_woocommerce_order_access_panel', 5 );

/** Clarify the billing phone purpose for classic checkout fallback. */
function poultryfarmer_woocommerce_checkout_fields( $fields ) {
	if ( isset( $fields['billing']['billing_phone'] ) ) {
		$fields['billing']['billing_phone']['label'] = __( 'Phone number for payment and order updates', 'poultryfarmer' );
		$fields['billing']['billing_phone']['type']  = 'tel';
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'poultryfarmer_woocommerce_checkout_fields' );
