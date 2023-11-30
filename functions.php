<?php
/**
 * Media Toolkit's functions.
 *
 * The intention to make these functions are
 * to make it possible to deregister the plugin's hook without using singleton pattern.
 *
 * @package MediaToolkit
 */

namespace MediaToolkit;

use MediaToolkit\MediaToolkitSetup;

/**
 * Setup the plugin's functionalities.
 */
function setup() {

	add_action( 'init', '\MediaToolkit\setup_text_domain' );
	add_action( 'admin_menu', '\MediaToolkit\setup_submenu_page' );
	add_action( 'admin_enqueue_scripts', '\MediaToolkit\enqueue_admin_scripts' );
	add_filter( 'admin_body_class', '\MediaToolkit\setup_admin_body_class' );
	add_action( 'admin_init', '\MediaToolkit\setup_settings' );
	add_filter( 'plugin_action_links_' . MEDIA_TOOLKIT_PLUGIN_BASENAME, '\MediaToolkit\plugin_action_links' );

}

/**
 * Output the plugin's functionalities.
 */
function output() {

	add_action( 'big_image_size_threshold', '\MediaToolkit\change_big_image_threshold', 10000, 1 );
	add_filter( 'wp_handle_upload_prefilter', '\MediaToolkit\modify_attachment_filename' );
	add_filter( 'wp_editor_set_quality', '\MediaToolkit\set_compression_quality', 10, 2 );
	add_action( 'wp_generate_attachment_metadata', '\MediaToolkit\replace_original_image', 10, 3 );

}

/**
 * Setup textdomain.
 */
function setup_text_domain() {

	load_plugin_textdomain( 'media-toolkit', false, MEDIA_TOOLKIT_PLUGIN_DIR . '/languages' );

}

/**
 * Add action links displayed in plugins page.
 *
 * @param array $links The action links array.
 * @return array The modified action links array.
 */
function plugin_action_links( $links ) {

	$settings = array( '<a href="' . admin_url( 'upload.php?page=media-toolkit' ) . '">' . __( 'Settings', 'media-toolkit' ) . '</a>' );

	return array_merge( $settings, $links );

}

/**
 * Add submenu page.
 */
function setup_submenu_page() {

	$instance = new MediaToolkitSetup();
	$instance->add_submenu_page();

}

/**
 * Enqueue admin scripts.
 */
function enqueue_admin_scripts() {

	$instance = new MediaToolkitSetup();
	$instance->enqueue_admin_scripts();

}

/**
 * Add admin body class.
 *
 * @param string $classes The admin body classes.
 * @return string
 */
function setup_admin_body_class( $classes ) {

	$instance = new MediaToolkitSetup();
	return $instance->admin_body_class( $classes );

}

/**
 * Setup settings.
 */
function setup_settings() {

	$instance = new MediaToolkitSetup();
	$instance->add_settings();

}

/**
 * Disable big image threshold when the original image is being replaced.
 *
 * @param int $threshold The current threshold.
 * @return int|bool
 */
function change_big_image_threshold( $threshold ) {

	$settings = get_option( 'mediatoolkit_settings', [] );

	$replace_original = isset( $settings['replace_original_image'] ) ? absint( $settings['replace_original_image'] ) : 0;
	$max_dimension    = isset( $settings['image_max_dimension'] ) ? absint( $settings['image_max_dimension'] ) : 0;

	// Disable the threshold if the original image is being replaced.
	if ( $replace_original && ! empty( $max_dimension ) ) {
		return false;
	}

	return $threshold;

}

/**
 * Modify attachment filename.
 *
 * @param array $file The file info array.
 * @return string The file info array.
 */
function modify_attachment_filename( $file ) {

	$instance = new MediaToolkitOutput();
	return $instance->modify_attachment_filename( $file );

}

/**
 * Set compression quality.
 *
 * @param int    $quality The current compression quality.
 * @param string $mime_type The mime type.
 *
 * @return int
 */
function set_compression_quality( $quality, $mime_type ) {

	$instance = new MediaToolkitOutput();
	return $instance->set_compression_quality( $quality, $mime_type );

}

/**
 * Replace original image.
 *
 * @param array  $metadata The attachment metadata.
 * @param int    $attachment_id The attachment ID.
 * @param string $context The context.
 *
 * @return array
 */
function replace_original_image( $metadata, $attachment_id, $context ) {

	$instance = new MediaToolkitOutput();
	return $instance->replace_original_image( $metadata, $attachment_id, $context );

}
