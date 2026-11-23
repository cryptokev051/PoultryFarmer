<?php
/**
 * Plugin Name: Poultry Farmer Starter Setup
 * Description: Administrator-triggered, one-time starter content and site setup for Poultry Farmer.
 * Version: 1.0.2
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Author: Poultry Farmer
 * Text Domain: poultryfarmer-starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PoultryFarmer_Starter_Setup {
	const VERSION         = '1.0.2';
	const OPTION_MANIFEST = 'poultryfarmer_starter_manifest_v1';
	const PAGE_META       = '_poultryfarmer_starter_key';
	const TERM_META       = '_poultryfarmer_starter_key';
	const ACTION          = 'poultryfarmer_starter_apply';
	const NONCE           = 'poultryfarmer_starter_apply_v1';

	/** Register the administrator interface. */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle_apply' ) );
	}

	/** Add the owner-triggered setup screen under Tools. */
	public static function admin_menu() {
		add_management_page(
			__( 'Poultry Farmer Starter Setup', 'poultryfarmer-starter' ),
			__( 'Poultry Farmer Setup', 'poultryfarmer-starter' ),
			'manage_options',
			'poultryfarmer-starter',
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/** Render the preview and explicit apply action. */
	public static function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to run this setup.', 'poultryfarmer-starter' ) );
		}

		$plan       = self::build_plan();
		$message    = isset( $_GET['pf_starter_message'] ) ? sanitize_key( wp_unslash( $_GET['pf_starter_message'] ) ) : '';
		$has_errors = ! empty( $plan['conflicts'] );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Poultry Farmer Starter Setup', 'poultryfarmer-starter' ); ?></h1>
			<p><?php esc_html_e( 'Preview the intended fresh-install changes below. Nothing is changed until an administrator presses the setup button.', 'poultryfarmer-starter' ); ?></p>

			<?php if ( 'success' === $message ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Starter setup completed. Existing tracked content was preserved.', 'poultryfarmer-starter' ); ?></p></div>
			<?php elseif ( 'blocked' === $message ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'Setup was blocked by a conflict. No setup changes were applied.', 'poultryfarmer-starter' ); ?></p></div>
			<?php elseif ( 'failed' === $message ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'Setup stopped after an unexpected WordPress API error. Review the current plan before retrying.', 'poultryfarmer-starter' ); ?></p></div>
			<?php endif; ?>

			<?php if ( $has_errors ) : ?>
				<div class="notice notice-error"><p><strong><?php esc_html_e( 'Review required. Setup cannot run while these conflicts exist:', 'poultryfarmer-starter' ); ?></strong></p><ul>
					<?php foreach ( $plan['conflicts'] as $conflict ) : ?>
						<li><?php echo esc_html( $conflict ); ?></li>
					<?php endforeach; ?>
				</ul></div>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Pages', 'poultryfarmer-starter' ); ?></h2>
			<table class="widefat striped">
				<thead><tr><th><?php esc_html_e( 'Page', 'poultryfarmer-starter' ); ?></th><th><?php esc_html_e( 'Route', 'poultryfarmer-starter' ); ?></th><th><?php esc_html_e( 'Initial status', 'poultryfarmer-starter' ); ?></th><th><?php esc_html_e( 'Template', 'poultryfarmer-starter' ); ?></th><th><?php esc_html_e( 'Plan', 'poultryfarmer-starter' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $plan['pages'] as $row ) : ?>
					<tr><td><?php echo esc_html( $row['title'] ); ?></td><td><code><?php echo esc_html( $row['route'] ); ?></code></td><td><?php echo esc_html( $row['status'] ); ?></td><td><?php echo esc_html( $row['template'] ?: 'Default' ); ?></td><td><?php echo esc_html( $row['action'] ); ?></td></tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Categories', 'poultryfarmer-starter' ); ?></h2>
			<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Category', 'poultryfarmer-starter' ); ?></th><th><?php esc_html_e( 'Slug', 'poultryfarmer-starter' ); ?></th><th><?php esc_html_e( 'Plan', 'poultryfarmer-starter' ); ?></th></tr></thead><tbody>
			<?php foreach ( $plan['categories'] as $row ) : ?>
				<tr><td><?php echo esc_html( $row['name'] ); ?></td><td><code><?php echo esc_html( $row['slug'] ); ?></code></td><td><?php echo esc_html( $row['action'] ); ?></td></tr>
			<?php endforeach; ?>
			</tbody></table>

			<h2><?php esc_html_e( 'Site settings', 'poultryfarmer-starter' ); ?></h2>
			<ul>
			<?php foreach ( $plan['settings'] as $setting ) : ?>
				<li><strong><?php echo esc_html( $setting['label'] ); ?>:</strong> <?php echo esc_html( $setting['action'] ); ?></li>
			<?php endforeach; ?>
			</ul>

			<div class="notice notice-warning inline"><p><?php esc_html_e( 'Only Home and Guides are initially published because they are required WordPress structural pages. Editorial hubs and informational pages remain drafts until an owner reviews and publishes their editable content.', 'poultryfarmer-starter' ); ?></p></div>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
				<?php wp_nonce_field( self::NONCE ); ?>
				<?php submit_button( __( 'Create or verify starter content', 'poultryfarmer-starter' ), 'primary', 'submit', true, $has_errors ? array( 'disabled' => 'disabled' ) : array() ); ?>
			</form>
		</div>
		<?php
	}

	/** Handle the explicit administrator action. */
	public static function handle_apply() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to run this setup.', 'poultryfarmer-starter' ), 403 );
		}

		check_admin_referer( self::NONCE );
		$plan = self::build_plan();

		if ( ! empty( $plan['conflicts'] ) ) {
			self::redirect_with_message( 'blocked' );
		}

		$result = self::apply_setup();
		self::redirect_with_message( is_wp_error( $result ) ? 'failed' : 'success' );
	}

	/** Redirect back to the setup screen. */
	private static function redirect_with_message( $message ) {
		wp_safe_redirect(
			add_query_arg(
				array(
				'page'               => 'poultryfarmer-starter',
				'pf_starter_message' => $message,
				),
				admin_url( 'tools.php' )
			)
		);
		exit;
	}

	/** Build a read-only preview with conflicts. */
	public static function build_plan() {
		$manifest   = self::manifest();
		$conflicts  = array();
		$page_rows  = array();
		$term_rows  = array();
		$definitions = self::page_definitions();

		if ( 'poultryfarmer' !== get_stylesheet() ) {
			$conflicts[] = __( 'The active theme is not Poultry Farmer. Activate the approved theme before setup.', 'poultryfarmer-starter' );
		}

		foreach ( self::category_definitions() as $key => $definition ) {
			$tracked_id = isset( $manifest['categories'][ $key ] ) ? absint( $manifest['categories'][ $key ] ) : 0;
			$action     = __( 'Create', 'poultryfarmer-starter' );

			if ( $tracked_id ) {
				$term = get_term( $tracked_id, 'category' );
				if ( ! $term || is_wp_error( $term ) || $key !== get_term_meta( $tracked_id, self::TERM_META, true ) ) {
					$conflicts[] = sprintf( __( 'Tracked category "%s" is missing or no longer carries the starter identifier.', 'poultryfarmer-starter' ), $definition['name'] );
					$action      = __( 'Blocked', 'poultryfarmer-starter' );
				} else {
					$action = __( 'Preserve unchanged', 'poultryfarmer-starter' );
				}
			} else {
				$existing = get_term_by( 'slug', $definition['slug'], 'category' );
				if ( $existing ) {
					$conflicts[] = sprintf( __( 'Category slug "%s" already belongs to an untracked category.', 'poultryfarmer-starter' ), $definition['slug'] );
					$action      = __( 'Blocked by existing slug', 'poultryfarmer-starter' );
				}
			}

			$term_rows[] = array( 'name' => $definition['name'], 'slug' => $definition['slug'], 'action' => $action );
		}

		foreach ( $definitions as $key => $definition ) {
			$tracked_id = isset( $manifest['pages'][ $key ] ) ? absint( $manifest['pages'][ $key ] ) : 0;
			$action     = __( 'Create', 'poultryfarmer-starter' );

			if ( $tracked_id ) {
				$post = get_post( $tracked_id );
				if ( ! $post || 'page' !== $post->post_type || $key !== get_post_meta( $tracked_id, self::PAGE_META, true ) ) {
					$conflicts[] = sprintf( __( 'Tracked Page "%s" is missing or no longer carries the starter identifier.', 'poultryfarmer-starter' ), $definition['title'] );
					$action      = __( 'Blocked', 'poultryfarmer-starter' );
				} else {
					$action = __( 'Preserve owner content and settings', 'poultryfarmer-starter' );
				}
			} else {
				$matches = get_posts(
					array(
						'post_type'      => 'page',
						'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ),
						'post_name__in'  => array( $definition['slug'] ),
						'posts_per_page' => 1,
						'fields'         => 'ids',
					)
				);
				if ( ! empty( $matches ) ) {
					$conflicts[] = sprintf( __( 'Page slug "%s" already belongs to an untracked Page. It will not be adopted or overwritten.', 'poultryfarmer-starter' ), $definition['slug'] );
					$action      = __( 'Blocked by existing slug', 'poultryfarmer-starter' );
				}
			}

			$page_rows[] = array(
				'title'    => $definition['title'],
				'route'    => self::definition_route( $key, $definitions ),
				'status'   => $definition['status'],
				'template' => $definition['template'],
				'action'   => $action,
			);
		}

		$settings = self::settings_plan( $manifest, $conflicts );

		return array( 'pages' => $page_rows, 'categories' => $term_rows, 'settings' => $settings, 'conflicts' => array_values( array_unique( $conflicts ) ) );
	}

	/** Apply setup through standard WordPress APIs. */
	private static function apply_setup() {
		$preflight = self::build_plan();
		if ( ! empty( $preflight['conflicts'] ) ) {
			return new WP_Error(
				'poultryfarmer_preflight_conflict',
				__( 'Setup stopped before any write: ', 'poultryfarmer-starter' ) . implode( ' ', $preflight['conflicts'] )
			);
		}

		$manifest = self::manifest();

		foreach ( self::category_definitions() as $key => $definition ) {
			if ( isset( $manifest['categories'][ $key ] ) ) {
				continue;
			}

			$created = wp_insert_term( $definition['name'], 'category', array( 'slug' => $definition['slug'], 'description' => $definition['description'] ) );
			if ( is_wp_error( $created ) ) {
				return $created;
			}
			$term_id = absint( $created['term_id'] );
			update_term_meta( $term_id, self::TERM_META, $key );
			$manifest['categories'][ $key ] = $term_id;
			if ( ! self::save_manifest( $manifest ) ) {
				return new WP_Error( 'poultryfarmer_manifest_write_failed', __( 'The starter manifest could not be saved after Category creation. The setup stopped with no further writes.', 'poultryfarmer-starter' ) );
			}
			$checkpoint = self::checkpoint( 'after_category', $manifest );
			if ( is_wp_error( $checkpoint ) ) {
				return $checkpoint;
			}
		}

		$definitions = self::page_definitions();
		foreach ( $definitions as $key => $definition ) {
			if ( isset( $manifest['pages'][ $key ] ) ) {
				continue;
			}

			$parent_id = $definition['parent'] ? absint( $manifest['pages'][ $definition['parent'] ] ?? 0 ) : 0;
			$content   = self::page_content( $key, $definition, $manifest );
			$page_id   = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => $definition['status'],
					'post_title'   => $definition['title'],
					'post_name'    => $definition['slug'],
					'post_parent'  => $parent_id,
					'post_excerpt' => $definition['excerpt'],
					'post_content' => $content,
				),
				true
			);
			if ( is_wp_error( $page_id ) ) {
				return $page_id;
			}

			update_post_meta( $page_id, self::PAGE_META, $key );
			if ( $definition['template'] ) {
				update_post_meta( $page_id, '_wp_page_template', $definition['template'] );
			}
			$manifest['pages'][ $key ] = absint( $page_id );
			if ( ! self::save_manifest( $manifest ) ) {
				return new WP_Error( 'poultryfarmer_manifest_write_failed', __( 'The starter manifest could not be saved after Page creation. The setup stopped with no further writes.', 'poultryfarmer-starter' ) );
			}
			$checkpoint = self::checkpoint( 'after_page', $manifest );
			if ( is_wp_error( $checkpoint ) ) {
				return $checkpoint;
			}
		}

		$checkpoint = self::checkpoint( 'before_settings', $manifest );
		if ( is_wp_error( $checkpoint ) ) {
			return $checkpoint;
		}

		$home_id   = absint( $manifest['pages']['home'] );
		$guides_id = absint( $manifest['pages']['guides'] );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );
		update_option( 'page_for_posts', $guides_id );
		update_option( 'permalink_structure', '/guides/%postname%/' );

		$expected_settings = array(
			'show_on_front'      => 'page',
			'page_on_front'      => (string) $home_id,
			'page_for_posts'     => (string) $guides_id,
			'permalink_structure'=> '/guides/%postname%/',
		);
		foreach ( $expected_settings as $option => $expected ) {
			$actual = (string) get_option( $option );
			if ( $actual !== $expected ) {
				return new WP_Error(
					'poultryfarmer_setting_verification_failed',
					sprintf( __( 'WordPress did not save %1$s: expected "%2$s", found "%3$s".', 'poultryfarmer-starter' ), $option, $expected, $actual )
				);
			}
		}
		flush_rewrite_rules();

		$manifest['settings_applied'] = true;
		$manifest['completed_at']     = gmdate( 'c' );
		$manifest['version']          = self::VERSION;
		if ( ! self::save_manifest( $manifest ) ) {
			return new WP_Error( 'poultryfarmer_completion_manifest_failed', __( 'The settings were saved, but the completion manifest could not be recorded. Setup must not be reported as successful.', 'poultryfarmer-starter' ) );
		}

		return true;
	}

	/** Describe settings without changing them. */
	private static function settings_plan( $manifest, &$conflicts ) {
		$applied       = ! empty( $manifest['settings_applied'] );
		$show_on_front = get_option( 'show_on_front' );
		$front_id      = absint( get_option( 'page_on_front' ) );
		$posts_id      = absint( get_option( 'page_for_posts' ) );
		$permalink     = (string) get_option( 'permalink_structure' );
		$target_home   = absint( $manifest['pages']['home'] ?? 0 );
		$target_guides = absint( $manifest['pages']['guides'] ?? 0 );
		$has_manifest  = ! empty( $manifest['pages'] ) || ! empty( $manifest['categories'] );

		$state = $applied ? 'completed' : ( $has_manifest ? 'partial' : 'initial' );

		if ( 'completed' === $state ) {
			if ( 'page' !== $show_on_front || $front_id !== $target_home || $posts_id !== $target_guides ) {
				$conflicts[] = __( 'Front/Posts page settings changed after starter setup. They will not be reset automatically.', 'poultryfarmer-starter' );
			}
			if ( '/guides/%postname%/' !== $permalink ) {
				$conflicts[] = __( 'Permalink settings changed after starter setup. They will not be reset automatically.', 'poultryfarmer-starter' );
			}
		} elseif ( 'partial' === $state ) {
			$default_state = 'posts' === $show_on_front && 0 === $front_id && 0 === $posts_id && self::is_supported_initial_permalink( $permalink );
			$target_state  = 'page' === $show_on_front && $front_id === $target_home && $posts_id === $target_guides && '/guides/%postname%/' === $permalink;
			if ( ! $default_state && ! $target_state ) {
				$conflicts[] = __( 'The verified partial setup has unexpected front-page, Posts-page or permalink settings. It will not guess whether those values are safe to resume.', 'poultryfarmer-starter' );
			}
			$partial_reason = self::verified_partial_install_reason( $manifest );
			if ( '' !== $partial_reason ) {
				$conflicts[] = sprintf( __( 'Verified partial setup cannot resume: %s', 'poultryfarmer-starter' ), $partial_reason );
			}
		} else {
			if ( 'posts' !== $show_on_front || 0 !== $front_id || 0 !== $posts_id ) {
				$conflicts[] = __( 'Existing Reading settings are not the supported fresh-install defaults (show_on_front=posts, page_on_front=0, page_for_posts=0). They will not be replaced.', 'poultryfarmer-starter' );
			}
			$fresh_reason = self::fresh_install_rejection_reason();
			if ( '' !== $fresh_reason ) {
				$conflicts[] = sprintf( __( 'Initial setup blocked: %s', 'poultryfarmer-starter' ), $fresh_reason );
			}
			if ( ! self::is_supported_initial_permalink( $permalink ) ) {
				$conflicts[] = sprintf(
					__( 'Existing permalink configuration "%s" is custom or owner-configured. Starter setup will preserve it and will not continue on an unverified installation.', 'poultryfarmer-starter' ),
					$permalink
				);
			}
		}

		return array(
			array( 'label' => __( 'Homepage', 'poultryfarmer-starter' ), 'action' => $applied ? __( 'Preserve verified Home assignment', 'poultryfarmer-starter' ) : __( 'Assign the starter Home Page', 'poultryfarmer-starter' ) ),
			array( 'label' => __( 'Posts page', 'poultryfarmer-starter' ), 'action' => $applied ? __( 'Preserve verified Guides assignment', 'poultryfarmer-starter' ) : __( 'Assign the starter Guides Page', 'poultryfarmer-starter' ) ),
			array( 'label' => __( 'Permalinks', 'poultryfarmer-starter' ), 'action' => $applied ? __( 'Preserve /guides/%postname%/', 'poultryfarmer-starter' ) : __( 'Set /guides/%postname%/', 'poultryfarmer-starter' ) ),
		);
	}

	/** An untracked install may proceed only from WordPress's supported defaults. */
	private static function is_supported_initial_permalink( $permalink ) {
		return in_array( (string) $permalink, array( '', '/%year%/%monthnum%/%day%/%postname%/', '/%postname%/' ), true );
	}

	/** Test-only interruption seam; production has no checkpoint callback. */
	private static function checkpoint( $name, $manifest ) {
		$result = apply_filters( 'poultryfarmer_starter_checkpoint', null, $name, $manifest );
		return is_wp_error( $result ) ? $result : true;
	}

	/**
	 * Return a specific reason when the database is not a strict fresh installation.
	 */
	private static function fresh_install_rejection_reason() {
		if ( 'en_US' !== get_locale() ) {
			return sprintf( __( 'The baseline cannot be established safely for locale "%s"; only the exact en_US WordPress 7.1.4 or 7.1.5 fixture is supported.', 'poultryfarmer-starter' ), get_locale() );
		}
		if ( ! in_array( get_bloginfo( 'version' ), array( '7.1.4', '7.1.5' ), true ) ) {
			return sprintf( __( 'The baseline cannot be established safely for WordPress version "%s"; only the exact supported WordPress 7.1.4 or 7.1.5 fixture is supported.', 'poultryfarmer-starter' ), get_bloginfo( 'version' ) );
		}

		$allowed_types = array( 'post', 'page', 'product' );
		foreach ( get_post_types( array( 'public' => true, 'show_ui' => true ), 'names' ) as $post_type ) {
			$allowed_types[] = $post_type;
		}
		$allowed_types = array_unique( $allowed_types );

		$editorial = get_posts(
			array(
				'post_type'      => $allowed_types,
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);

		$hello   = get_page_by_path( 'hello-world', OBJECT, 'post' );
		$sample  = get_page_by_path( 'sample-page', OBJECT, 'page' );
		$privacy = get_page_by_path( 'privacy-policy', OBJECT, 'page' );
		$allowed = array();

		if ( $hello instanceof WP_Post ) {
			if ( ! self::matches_core_sample_fixture( $hello, 'hello-world' ) ) {
				return __( 'The default Hello World Post is missing, renamed, unpublished or modified.', 'poultryfarmer-starter' );
			}
			$allowed[] = (int) $hello->ID;
		}
		if ( $sample instanceof WP_Post ) {
			if ( ! self::matches_core_sample_fixture( $sample, 'sample-page' ) ) {
				return __( 'The default Sample Page is missing, renamed, unpublished or modified.', 'poultryfarmer-starter' );
			}
			$allowed[] = (int) $sample->ID;
		}
		if ( $privacy instanceof WP_Post ) {
			if ( ! self::matches_privacy_policy_fixture( $privacy ) ) {
				return __( 'The default draft Privacy Policy is missing, renamed, unpublished or modified; its exact supported fixture baseline could not be established safely.', 'poultryfarmer-starter' );
			}
			$allowed[] = (int) $privacy->ID;
		} else {
			return __( 'The supported WordPress 7.1.4/7.1.5 fresh-install Privacy Policy fixture is missing; its baseline cannot be established safely.', 'poultryfarmer-starter' );
		}

		$found = array();
		foreach ( $editorial as $post ) {
			if ( in_array( (int) $post->ID, $allowed, true ) ) {
				continue;
			}
			$found[] = sprintf( '%s "%s" (%s)', $post->post_type, $post->post_title ? $post->post_title : '(untitled)', $post->post_status );
		}
		if ( ! empty( $found ) ) {
			return sprintf( __( 'Additional editorial or custom content exists: %s.', 'poultryfarmer-starter' ), implode( ', ', array_slice( $found, 0, 5 ) ) );
		}

		$custom_terms = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
				'fields'     => 'all',
			)
		);
		if ( ! is_wp_error( $custom_terms ) ) {
			foreach ( $custom_terms as $term ) {
				if ( 'uncategorized' !== $term->slug ) {
					return sprintf( __( 'A custom Category exists: "%s" (%s).', 'poultryfarmer-starter' ), $term->name, $term->slug );
				}
			}
		}

		if ( count( $allowed ) < 2 ) {
			return __( 'The expected WordPress core sample Post/Page records are not present.', 'poultryfarmer-starter' );
		}

		return '';
	}

	/**
	 * Compare an exact supported fixture fingerprint, not a characteristic phrase.
	 * The fingerprint covers identity, state and full normalized content. Volatile
	 * timestamps, GUIDs, generated excerpts and authors are intentionally excluded.
	 */
	private static function matches_core_sample_fixture( $post, $fixture ) {
		$expected_content_hashes = array(
			'hello-world' => array( '7878a5827cf33686ca147a522a31b5dc865f8387ee7939c2c1a57e07a02d8b58' ),
			'sample-page' => array( 'fe535939717fed5f665185be3836c326a88734fcad625ac299a953fdeceede83', '5d213d2b81bcec4302e59169a328d6a906654e2d7806656d1f600b9542771240' ),
		);
		$expected_identity = array(
			'hello-world' => array( 'post', 'publish', 'Hello world!', 'hello-world' ),
			'sample-page' => array( 'page', 'publish', 'Sample Page', 'sample-page' ),
		);
		if ( ! isset( $expected_content_hashes[ $fixture ], $expected_identity[ $fixture ] ) || ! ( $post instanceof WP_Post ) ) {
			return false;
		}
		$identity = array( (string) $post->post_type, (string) $post->post_status, (string) $post->post_title, (string) $post->post_name );
		return $identity === $expected_identity[ $fixture ]
			&& in_array( hash( 'sha256', self::normalized_sample_text( $post->post_content ) ), $expected_content_hashes[ $fixture ], true );
	}

	/** Match the supported generated Privacy Policy after normalizing only the site URL. */
	private static function matches_privacy_policy_fixture( $post ) {
		if ( ! ( $post instanceof WP_Post ) || 'page' !== $post->post_type || 'draft' !== $post->post_status || 'Privacy Policy' !== $post->post_title || 'privacy-policy' !== $post->post_name ) {
			return false;
		}
		$content = self::normalized_sample_text( $post->post_content );
		$content = str_replace( untrailingslashit( home_url() ), 'SITE_URL', $content );
		return hash_equals( 'bebc913435795558c346cdbf473f78519c4bca9f05b10d3d636e33ea7390216c', hash( 'sha256', $content ) );
	}

	/** Normalize a supported core sample for exact English 7.1.4/7.1.5 baseline comparison. */
	private static function normalized_sample_text( $content ) {
		$text = (string) $content;
		$text = preg_replace( '/<!--\\/?wp:[^>]*-->/', '', $text );
		$text = wp_strip_all_tags( $text );
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$text = preg_replace( '/\\s+/u', ' ', $text );
		return trim( (string) $text );
	}

	/**
	 * Verify that a manifest-backed interrupted setup contains only tracked starter
	 * objects, core samples and the records already represented by the manifest.
	 */
	private static function verified_partial_install_reason( $manifest ) {
		$tracked_page_ids = array();
		foreach ( $manifest['pages'] as $key => $page_id ) {
			$post = get_post( absint( $page_id ) );
			if ( ! $post || 'page' !== $post->post_type || $key !== get_post_meta( $post->ID, self::PAGE_META, true ) ) {
				return sprintf( __( 'tracked Page "%s" is missing or no longer carries its starter identifier', 'poultryfarmer-starter' ), $key );
			}
			$tracked_page_ids[] = (int) $post->ID;
		}

		foreach ( $manifest['categories'] as $key => $term_id ) {
			$term = get_term( absint( $term_id ), 'category' );
			if ( ! $term || is_wp_error( $term ) || $key !== get_term_meta( absint( $term_id ), self::TERM_META, true ) ) {
				return sprintf( __( 'tracked Category "%s" is missing or no longer carries its starter identifier', 'poultryfarmer-starter' ), $key );
			}
		}

		$allowed_types = array_unique( array_merge( array( 'post', 'page', 'product' ), get_post_types( array( 'public' => true, 'show_ui' => true ), 'names' ) ) );
		$posts = get_posts(
			array(
				'post_type'      => $allowed_types,
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		$allowed_page_ids = $tracked_page_ids;
		foreach ( array( 'hello-world', 'sample-page', 'privacy-policy' ) as $path ) {
			$core = get_page_by_path( $path, OBJECT, in_array( $path, array( 'hello-world' ), true ) ? 'post' : 'page' );
			if ( $core instanceof WP_Post ) {
				if ( 'hello-world' === $path ) {
					continue;
				}
				$allowed_page_ids[] = (int) $core->ID;
			}
		}
		foreach ( $posts as $post_id ) {
			if ( in_array( (int) $post_id, $allowed_page_ids, true ) ) {
				continue;
			}
			$post = get_post( $post_id );
			if ( $post && 'post' === $post->post_type && 'hello-world' === $post->post_name ) {
				continue;
			}
			return sprintf( __( 'an untracked record exists: %s "%s" (%s)', 'poultryfarmer-starter' ), $post->post_type, $post->post_title ? $post->post_title : '(untitled)', $post->post_status );
		}

		$custom_terms = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false, 'fields' => 'all' ) );
		if ( ! is_wp_error( $custom_terms ) ) {
			foreach ( $custom_terms as $term ) {
				if ( 'uncategorized' === $term->slug ) {
					continue;
				}
				$tracked = false;
				foreach ( $manifest['categories'] as $term_id ) {
					if ( absint( $term_id ) === absint( $term->term_id ) ) {
						$tracked = true;
						break;
					}
				}
				if ( ! $tracked ) {
					return sprintf( __( 'an untracked Category exists: "%s" (%s)', 'poultryfarmer-starter' ), $term->name, $term->slug );
				}
			}
		}

		return '';
	}

	/** Backward-compatible strict-fresh boolean used by setup planning. */
	private static function has_only_core_sample_content() {
		return '' === self::fresh_install_rejection_reason();
	}

	/** Stable category definitions. */
	private static function category_definitions() {
		return array(
			'kienyeji' => array( 'name' => 'Kienyeji', 'slug' => 'kienyeji', 'description' => 'Guides for indigenous and improved Kienyeji chicken farming.' ),
			'layers'   => array( 'name' => 'Layers', 'slug' => 'layers', 'description' => 'Guides for egg-production planning and layer management.' ),
			'broilers' => array( 'name' => 'Broilers', 'slug' => 'broilers', 'description' => 'Guides for broiler batches and meat-bird management.' ),
			'hatching' => array( 'name' => 'Hatching', 'slug' => 'hatching', 'description' => 'Guides for incubation, hatch preparation and early chick care.' ),
			'feeding'  => array( 'name' => 'Feeding', 'slug' => 'feeding', 'description' => 'Guides for poultry feeding and water management.' ),
			'housing'  => array( 'name' => 'Housing', 'slug' => 'housing', 'description' => 'Guides for practical poultry housing decisions.' ),
			'health'   => array( 'name' => 'Health', 'slug' => 'health', 'description' => 'Guides for flock-health observation and management routines.' ),
			'records'  => array( 'name' => 'Records', 'slug' => 'records', 'description' => 'Guides for flock records and production monitoring.' ),
		);
	}

	/** Stable Page definitions in parent-before-child order. */
	private static function page_definitions() {
		return array(
			'home'             => array( 'title' => 'Home', 'slug' => 'home', 'parent' => '', 'template' => '', 'status' => 'publish', 'excerpt' => 'Practical poultry farming guidance for Kenyan farmers.' ),
			'farming'          => array( 'title' => 'Farming', 'slug' => 'farming', 'parent' => '', 'template' => '', 'status' => 'draft', 'excerpt' => 'Compare poultry production models and choose the guidance relevant to your farm.' ),
			'kienyeji'         => array( 'title' => 'Kienyeji Chicken Farming in Kenya', 'slug' => 'kienyeji', 'parent' => 'farming', 'template' => 'poultry-hub', 'status' => 'draft', 'excerpt' => 'Guidance for indigenous and improved Kienyeji chicken production.' ),
			'layers'           => array( 'title' => 'Layers Farming in Kenya', 'slug' => 'layers', 'parent' => 'farming', 'template' => 'poultry-hub', 'status' => 'draft', 'excerpt' => 'Planning and management guidance for egg-producing flocks.' ),
			'broilers'         => array( 'title' => 'Broiler Farming in Kenya', 'slug' => 'broilers', 'parent' => 'farming', 'template' => 'poultry-hub', 'status' => 'draft', 'excerpt' => 'Planning and management guidance for broiler batches.' ),
			'hatching'         => array( 'title' => 'Chicken Hatching and Incubation in Kenya', 'slug' => 'hatching', 'parent' => 'farming', 'template' => 'poultry-hub', 'status' => 'draft', 'excerpt' => 'Guidance for incubation, hatch preparation and early chick care.' ),
			'management'       => array( 'title' => 'Farm Management', 'slug' => 'management', 'parent' => '', 'template' => 'section-hub', 'status' => 'draft', 'excerpt' => 'Practical systems for feeding, housing, health, records and production monitoring.' ),
			'feeding'          => array( 'title' => 'Feeding and Water', 'slug' => 'feeding', 'parent' => 'management', 'template' => 'section-hub', 'status' => 'draft', 'excerpt' => 'Editorial guidance for poultry feeding and water routines.' ),
			'housing'          => array( 'title' => 'Poultry Housing in Kenya', 'slug' => 'housing', 'parent' => 'management', 'template' => 'section-hub', 'status' => 'draft', 'excerpt' => 'Editorial guidance for practical poultry housing decisions.' ),
			'health'           => array( 'title' => 'Flock Health', 'slug' => 'health', 'parent' => 'management', 'template' => 'section-hub', 'status' => 'draft', 'excerpt' => 'Editorial guidance for flock-health observation and management.' ),
			'records'          => array( 'title' => 'Poultry Farm Records', 'slug' => 'records', 'parent' => 'management', 'template' => 'section-hub', 'status' => 'draft', 'excerpt' => 'Editorial guidance for useful flock records and production monitoring.' ),
			'prices'           => array( 'title' => 'Poultry Prices and Markets', 'slug' => 'prices', 'parent' => '', 'template' => 'section-hub', 'status' => 'draft', 'excerpt' => 'A future home for verified, dated poultry market context.' ),
			'calculators'      => array( 'title' => 'Poultry Calculators', 'slug' => 'calculators', 'parent' => '', 'template' => 'section-hub', 'status' => 'draft', 'excerpt' => 'Planned financial tools; no calculator is active yet.' ),
			'guides'           => array( 'title' => 'Guides', 'slug' => 'guides', 'parent' => '', 'template' => '', 'status' => 'publish', 'excerpt' => 'Practical poultry farming guides for Kenyan readers.' ),
			'start-here'       => array( 'title' => 'Start Here', 'slug' => 'start-here', 'parent' => '', 'template' => '', 'status' => 'draft', 'excerpt' => 'A planned starting point for poultry farming guidance.' ),
			'about'            => array( 'title' => 'About Poultry Farmer', 'slug' => 'about', 'parent' => '', 'template' => '', 'status' => 'draft', 'excerpt' => 'About the Poultry Farmer publication.' ),
			'editorial-policy' => array( 'title' => 'Editorial Policy', 'slug' => 'editorial-policy', 'parent' => '', 'template' => '', 'status' => 'draft', 'excerpt' => 'The planned editorial standards and correction process.' ),
			'contact'          => array( 'title' => 'Contact', 'slug' => 'contact', 'parent' => '', 'template' => '', 'status' => 'draft', 'excerpt' => 'Contact information will be reviewed before publication.' ),
		);
	}

	/** Build editable block content, resolving category IDs by stable slug. */
	private static function page_content( $key, $definition, $manifest ) {
		$type_copy = array(
			'kienyeji' => array( 'Kienyeji flocks may use indigenous or improved birds for meat, eggs or both.', array( 'Choose a bird type for a defined purpose.', 'Plan suitable housing and protection.', 'Match feeding, health routines and markets to the flock.' ) ),
			'layers'   => array( 'Layer farming is organised around consistent egg production and daily flock routines.', array( 'Plan flock size around housing and labour.', 'Prepare reliable feed, water and lighting routines.', 'Identify realistic egg buyers and keep production records.' ) ),
			'broilers' => array( 'Broiler farming uses planned batches of meat birds with a defined placement and sale window.', array( 'Prepare brooding before chicks arrive.', 'Plan feed, water and health routines for the batch.', 'Confirm intended buyers and sale timing.' ) ),
			'hatching' => array( 'Hatching begins with suitable fertile eggs, controlled incubation and prepared early chick care.', array( 'Confirm fertile-egg source and handling.', 'Document incubation and hatch-hygiene checks.', 'Prepare brooding before setting eggs.' ) ),
		);

		if ( isset( $type_copy[ $key ] ) ) {
			return self::hub_content( $key, $type_copy[ $key ][0], $type_copy[ $key ][1], $manifest );
		}

		if ( in_array( $key, array( 'feeding', 'housing', 'health', 'records' ), true ) ) {
			return self::management_topic_content( $key, $manifest );
		}

		switch ( $key ) {
			case 'home':
				return '<!-- wp:paragraph --><p>Poultry Farmer provides practical Kenyan guidance for researching, starting and managing a poultry farm.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Poultry farming involves raising chickens for meat, eggs, breeding stock or chicks. Before starting, decide who you expect to sell to, how many birds you can manage well and how you will fund daily operations. Housing, feed quality, flock health, labour, record-keeping and reliable market access all affect whether the farm can operate consistently.</p><!-- /wp:paragraph -->';
			case 'farming':
				return '<!-- wp:paragraph --><p>Use this directory to compare Kienyeji, layers, broilers and hatching before choosing the guidance relevant to your farm.</p><!-- /wp:paragraph -->';
			case 'management':
				return '<!-- wp:heading --><h2 class="wp-block-heading">Build dependable daily systems</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Organise practical feeding, housing, flock-health, record-keeping and production-monitoring guidance here.</p><!-- /wp:paragraph --><!-- wp:list --><ul class="wp-block-list"><li><a href="/management/feeding/">Feeding and water</a></li><li><a href="/management/housing/">Poultry housing</a></li><li><a href="/management/health/">Flock health</a></li><li><a href="/management/records/">Farm records</a></li></ul><!-- /wp:list -->';
			case 'prices':
				return '<!-- wp:paragraph --><p><strong>Draft starter page:</strong> Publish market information only after a documented process can verify the location, date, unit and source. No current price is supplied by starter setup.</p><!-- /wp:paragraph -->';
			case 'calculators':
				return '<!-- wp:paragraph --><p><strong>Coming soon:</strong> No calculator or financial result is active. Calculator logic belongs in the future poultryfarmer-core plugin.</p><!-- /wp:paragraph -->';
			case 'guides':
				return '';
			default:
				return '<!-- wp:paragraph {"backgroundColor":"brand-soft"} --><p class="has-brand-soft-background-color has-background"><strong>Draft starter page:</strong> Replace this editorial prompt with reviewed, approved content before publication.</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Editorial outline</h2><!-- /wp:heading --><!-- wp:list --><ul class="wp-block-list"><li>State the page purpose clearly.</li><li>Add verified details and useful internal links.</li><li>Remove this prompt before publishing.</li></ul><!-- /wp:list -->';
		}
	}

	/** Build a poultry-type hub with a slug-resolved category Query Loop. */
	private static function hub_content( $key, $overview, $considerations, $manifest ) {
		$query = self::query_loop( $key, $manifest, 'No matching guides are published yet.' );
		$items = '';
		foreach ( $considerations as $item ) {
			$items .= '<li>' . esc_html( $item ) . '</li>';
		}

		return '<!-- wp:heading --><h2 class="wp-block-heading">How this production model works</h2><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html( $overview ) . '</p><!-- /wp:paragraph -->'
			. '<!-- wp:heading --><h2 class="wp-block-heading">Important planning decisions</h2><!-- /wp:heading --><!-- wp:list --><ul class="wp-block-list">' . $items . '</ul><!-- /wp:list -->'
			. '<!-- wp:heading --><h2 class="wp-block-heading">Related published guides</h2><!-- /wp:heading -->' . $query
			. '<!-- wp:heading --><h2 class="wp-block-heading">Management topics</h2><!-- /wp:heading --><!-- wp:list --><ul class="wp-block-list"><li><a href="/management/feeding/">Feeding and water</a></li><li><a href="/management/housing/">Housing</a></li><li><a href="/management/health/">Flock health</a></li><li><a href="/management/records/">Records and monitoring</a></li></ul><!-- /wp:list -->';
	}

	/** Build a management topic hub with a matching Query Loop. */
	private static function management_topic_content( $key, $manifest ) {
		$outlines = array(
			'feeding' => array(
				'Feeding and water planning',
				'Build a feeding and water outline around the flock type, age, production stage and locally available feeding systems.',
				array( 'Define feeding and water points for a proposed flock size.', 'Plan routine checks for feed quality, waste and access.', 'Identify records for feed use, water access and observed flock response.' ),
			),
			'housing' => array(
				'Housing and environment planning',
				'Use housing guidance to organise shelter, ventilation, cleaning and movement around the needs of the birds and local conditions.',
				array( 'Define the bird count, breed type and production purpose.', 'Map space, shelter, ventilation, drainage and protection requirements.', 'Plan cleaning, bedding, equipment and visitor movement.' ),
			),
			'health' => array(
				'Flock-health observation planning',
				'Structure health content around observation, prevention, hygiene and escalation to an appropriate qualified adviser.',
				array( 'Define a routine for normal behaviour and physical checks.', 'Plan hygiene, isolation and record-keeping procedures.', 'Explain when to seek qualified local diagnosis rather than guessing.' ),
			),
			'records' => array(
				'Records and production monitoring',
				'Build records around decisions: inputs used, birds or batches managed, production observed and changes made.',
				array( 'Choose a simple register for the farm size and labour structure.', 'Link feed, mortality, production and health records to a date and batch.', 'Review records regularly and document source and limitation fields.' ),
			),
		);
		$outlines = $outlines[ $key ];
		$items = '';
		foreach ( $outlines[2] as $item ) {
			$items .= '<li>' . esc_html( $item ) . '</li>';
		}
		return '<!-- wp:paragraph {"backgroundColor":"brand-soft"} --><p class="has-brand-soft-background-color has-background"><strong>Draft starter outline:</strong> ' . esc_html( $outlines[1] ) . '</p><!-- /wp:paragraph -->'
			. '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $outlines[0] ) . '</h2><!-- /wp:heading -->'
			. '<!-- wp:list --><ul class="wp-block-list">' . $items . '</ul><!-- /wp:list -->'
			. '<!-- wp:heading --><h2 class="wp-block-heading">Related published guides</h2><!-- /wp:heading -->' . self::query_loop( $key, $manifest, 'No matching management guides are published yet.' );
	}

	/** Build the native Query Loop using a term resolved from its stable slug. */
	private static function query_loop( $category_key, $manifest, $empty_message ) {
		$term_id = absint( $manifest['categories'][ $category_key ] ?? 0 );
		$query   = wp_json_encode(
			array(
				'queryId' => 700 + $term_id,
				'query'   => array(
					'perPage'  => 6,
					'pages'    => 0,
					'offset'   => 0,
					'postType' => 'post',
					'order'    => 'desc',
					'orderBy'  => 'date',
					'taxQuery' => array( 'category' => array( $term_id ) ),
					'inherit'  => false,
				),
			)
		);

		return '<!-- wp:query ' . $query . ' --><div class="wp-block-query phase2b2-guide-query"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} --><!-- wp:group {"className":"poultryfarmer-card phase2b2-guide-card","backgroundColor":"surface","layout":{"type":"constrained"}} --><div class="wp-block-group poultryfarmer-card phase2b2-guide-card has-surface-background-color has-background"><!-- wp:post-terms {"term":"category","fontSize":"small"} /--><!-- wp:post-title {"isLink":true,"level":3} /--><!-- wp:post-excerpt {"moreText":"","showMoreOnNewLine":false} /--><!-- wp:post-date {"fontSize":"small"} /--></div><!-- /wp:group --><!-- /wp:post-template --><!-- wp:query-no-results --><!-- wp:paragraph --><p>' . esc_html( $empty_message ) . '</p><!-- /wp:paragraph --><!-- /wp:query-no-results --></div><!-- /wp:query -->';
	}

	/** Return the expected route for preview only. */
	private static function definition_route( $key, $definitions ) {
		$segments = array( $definitions[ $key ]['slug'] );
		$parent   = $definitions[ $key ]['parent'];
		while ( $parent ) {
			array_unshift( $segments, $definitions[ $parent ]['slug'] );
			$parent = $definitions[ $parent ]['parent'];
		}
		return '/' . implode( '/', $segments ) . '/';
	}

	/** Read the setup-owned object manifest. */
	private static function manifest() {
		$manifest = get_option( self::OPTION_MANIFEST, array() );
		return wp_parse_args( is_array( $manifest ) ? $manifest : array(), array( 'version' => self::VERSION, 'pages' => array(), 'categories' => array(), 'settings_applied' => false ) );
	}

	/** Save the setup-owned object manifest without autoloading it. */
	private static function save_manifest( $manifest ) {
		if ( false === get_option( self::OPTION_MANIFEST, false ) ) {
			$saved = add_option( self::OPTION_MANIFEST, $manifest, '', false );
		} else {
			$saved = update_option( self::OPTION_MANIFEST, $manifest, false );
		}
		if ( ! $saved && $manifest !== get_option( self::OPTION_MANIFEST ) ) {
			return false;
		}
		return true;
	}
}

PoultryFarmer_Starter_Setup::init();
