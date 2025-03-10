<?php
/**
 * Plugin Name:     WP Learn Plugin Security
 * Description:     Plugin demo learn security
 * Author:          VN
 * Author URI:      https://wpmasterynow.com
 * Text Domain:     wp-learn-plugin-security
 * Domain Path:     /languages
 * Version:         1.1.0
 *
 * @package         WP_Learn_Plugin_Security
 */

/**
 * Update these with the page slugs of your success and error pages
 */
define( 'WP_LEARN_SUCCESS_PAGE_SLUG', 'register' );
define( 'WP_LEARN_ERROR_PAGE_SLUG', 'form-error-page' );

/**
 * Setting up some URL constants
 */
define( 'WP_LEARN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_LEARN_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

register_activation_hook( __FILE__, 'wp_learn_setup_table' );
/**
 * Set up the required form submission table.
 * Creates a new database table for storing form submissions when the plugin is activated.
 *
 * @return void
 */
function wp_learn_setup_table() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'form_submissions';

	$sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    name varchar(100) NOT NULL,
    email varchar(100) NOT NULL,
    PRIMARY KEY (id)
    )";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

add_action( 'admin_enqueue_scripts', 'wp_learn_enqueue_script' );
/**
 * Enqueue Admin assets.
 * Registers and enqueues JavaScript for the admin area and localizes AJAX variables.
 *
 * @return void
 */
function wp_learn_enqueue_script() {
	wp_register_script(
		'wp-learn-admin',
		WP_LEARN_PLUGIN_URL . 'assets/admin.js',
		array( 'jquery' ),
		'1.0.0',
		true
	);
	wp_enqueue_script( 'wp-learn-admin' );
	wp_localize_script(
		'wp-learn-admin',
		'wp_learn_ajax',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'delete_form_submission_nonce' ),
		)
	);
}


add_action( 'wp_enqueue_scripts', 'wp_learn_enqueue_script_fontend' );
/**
 * Enqueue Frontend assets.
 * Registers and enqueues CSS styles for the frontend.
 *
 * @return void
 */
function wp_learn_enqueue_script_fontend() {
	wp_register_style(
		'wp-learn-style',
		WP_LEARN_PLUGIN_URL . 'assets/style.css',
		array(),
		'1.0.0'
	);
	wp_enqueue_style( 'wp-learn-style' );
}


add_shortcode( 'wp_learn_form_shortcode', 'wp_learn_form_shortcode' );
/**
 * Renders a submission form via shortcode.
 *
 * @param array $atts Shortcode attributes with default class 'red'.
 * @return string HTML markup for the form.
 */
function wp_learn_form_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'class' => 'red',
		),
		$atts
	);
	ob_start();
	?>

	<div id="wp_learn_form" class="<?php echo esc_attr( $atts['class'] ); ?>">
	<form method="post">
		<input type="hidden" name="wp_learn_form" value="submit">
		<?php
		wp_nonce_field( 'wp_learn_form_nonce_action', 'wp_learn_form_nonce_field' );
		?>
		<div>
			<label for="name">Name</label>
		  <input type="text" id="name" name="name" placeholder="Name">
		</div>
		<div>
		  <label for="email">Email address</label>
		  <input type="email" id="email" name="email" placeholder="Email address">
		</div>
		<div>
			<input type="submit" id="submit" name="submit" value="Submit">
		</div>
	</form>
	</div>

	<?php
	$form = ob_get_clean();
	return $form;
}

/**
 * Process the form data and redirect
 */

add_action( 'wp', 'wp_learn_maybe_process_form' );
/**
 * Processes the form submission if it exists.
 * Validates the nonce, sanitizes the input data, and inserts it into the database.
 * Redirects to success or error page based on the operation result.
 *
 * @return void
 */
function wp_learn_maybe_process_form() {
	if ( ! isset( $_POST['wp_learn_form'] ) || ! isset( $_POST['wp_learn_form_nonce_field'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['wp_learn_form_nonce_field'] ) ), 'wp_learn_form_nonce_action' ) ) {
		wp_safe_redirect( home_url( WP_LEARN_ERROR_PAGE_SLUG ) );
		die();
	}
	$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	global $wpdb;
	$table_name = $wpdb->prefix . 'form_submissions';

	// Fix: Use proper $wpdb->insert method instead of raw query
	$result = $wpdb->insert(
		$table_name,
		array(
			'name'  => $name,
			'email' => $email,
		),
		array(
			'%s',
			'%s',
		)
	);

	if ( $result ) {
		wp_cache_delete( 'form_submissions', 'wp_learn_plugin' );
	}

	if ( $result ) {
		wp_safe_redirect( home_url( WP_LEARN_SUCCESS_PAGE_SLUG ) );
		die();
	}
	wp_safe_redirect( home_url( WP_LEARN_ERROR_PAGE_SLUG ) );
	die();
}

/**
 * Create an admin page to show the form submissions
 */
add_action( 'admin_menu', 'wp_learn_submenu', 11 );
/**
 * Adds a submenu page under the Tools menu.
 *
 * @return void
 */
function wp_learn_submenu() {
	add_submenu_page(
		'tools.php',
		esc_html__( 'WP Learn Admin Page', 'wp-learn-plugin-security' ),
		esc_html__( 'WP Learn Admin Page', 'wp-learn-plugin-security' ),
		'manage_options',
		'wp_learn_admin',
		'wp_learn_render_admin_page'
	);
}

/**
 * Render the form submissions admin page
 */
function wp_learn_render_admin_page() {
	$submissions = wp_learn_get_form_submissions();
	?>
	<div class="wrap" id="wp_learn_admin">
	<h1>Admin</h1>
	<table>
		<thead>
		<tr>
			<th>Name</th>
			<th>Email</th>
		</tr>
		</thead>
		<?php foreach ( $submissions as $submission ) { ?>
		<tr>
			<td><?php echo esc_html( $submission->name ); ?></td>
			<td><?php echo esc_html( $submission->email ); ?></td>
			<td><a class="delete-submission" data-id="<?php echo (int) $submission->id; ?>" style="cursor:pointer;">Delete</a></td>
		</tr>
		<?php } ?>
	</table>
	</div>
	<?php
}

/**
 * Get all the form submissions.
 * Retrieves submissions from cache or database and stores in cache if needed.
 *
 * @return array Array of form submission objects.
 */
function wp_learn_get_form_submissions() {
	$submissions = wp_cache_get( 'form_submissions', 'wp_learn_plugin' );
	if ( false === $submissions ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'form_submissions';

		$submissions = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM %1$s', $table_name )
		);

		wp_cache_set( 'form_submissions', $submissions, 'wp_learn_plugin', 300 );

	}
	return $submissions;
}

/**
 * Ajax Hook to delete the form submissions.
 * Handles the AJAX request to delete a form submission entry.
 *
 * @return void
 */
add_action( 'wp_ajax_delete_form_submission', 'wp_learn_delete_form_submission' );
/**
 * Delete form submission via AJAX.
 * Validates user capabilities and nonce before deleting the submission from database.
 *
 * @return JSON Response with operation result.
 */
function wp_learn_delete_form_submission() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return wp_send_json( array( 'result' => 'Authentication error' ) );
	}

	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'delete_form_submission_nonce' ) ) {
		return wp_send_json( array( 'result' => 'Nonce verification failed' ) );
	}

	if ( ! isset( $_POST['id'] ) ) {
		return;
	}

	$submission_id = (int) $_POST['id'];

	if ( 0 === $submission_id ) {
		return wp_send_json( array( 'result' => 'Invalid ID passed' ) );
	}
	global $wpdb;
	$table_name = $wpdb->prefix . 'form_submissions';

	// Fix: Use delete method instead of get_results for deletion operation.
	$result = $wpdb->delete(
		$table_name,
		array( 'id' => $submission_id ),
		array( '%d' )
	);

	// Clear cache after successful deletion.
	if ( $result ) {
		wp_cache_delete( 'form_submissions', 'wp_learn_plugin' );
	}

	return wp_send_json( array( 'result' => $result ? 'success' : 'error' ) );
}
