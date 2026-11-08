<?php
/**
 * Media Toolkit's functions.
 *
 * The hooks call plain functions so they can be removed without a singleton.
 *
 * @package MediaToolkit
 */

namespace MediaToolkit;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Setup the plugin's functionalities.
 */
function setup() {

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

	add_filter( 'big_image_size_threshold', '\MediaToolkit\change_big_image_threshold', 10000 );
	add_filter( 'wp_handle_upload_prefilter', '\MediaToolkit\modify_attachment_filename' );
	add_filter( 'wp_editor_set_quality', '\MediaToolkit\set_compression_quality' );
	add_filter( 'wp_generate_attachment_metadata', '\MediaToolkit\replace_original_image', 10, 3 );

}

/**
 * Get the plugin settings.
 *
 * @return array
 */
function get_plugin_settings() {

	$settings = get_option( 'mediatoolkit_settings', array() );

	return is_array( $settings ) ? $settings : array();

}

/**
 * Add action links displayed in plugins page.
 *
 * @param array $links The action links array.
 * @return array The modified action links array.
 */
function plugin_action_links( $links ) {

	$settings = array( '<a href="' . esc_url( admin_url( 'upload.php?page=media-toolkit' ) ) . '">' . esc_html__( 'Settings', 'media-toolkit' ) . '</a>' );

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
 * @param int|false $threshold The current threshold.
 * @return int|false
 */
function change_big_image_threshold( $threshold ) {

	$settings = get_plugin_settings();

	$replace_original = ! empty( $settings['replace_original_image'] );
	$max_dimension    = isset( $settings['image_max_dimension'] ) ? absint( $settings['image_max_dimension'] ) : 0;

	// Disable the threshold if the original image is being replaced.
	if ( $replace_original && $max_dimension ) {
		return false;
	}

	return $threshold;

}

/**
 * Modify attachment filename.
 *
 * @param array $file The file info array.
 * @return array The file info array.
 */
function modify_attachment_filename( $file ) {

	$instance = new MediaToolkitOutput();
	return $instance->modify_attachment_filename( $file );

}

/**
 * Set compression quality.
 *
 * @param int $quality The current compression quality.
 * @return int
 */
function set_compression_quality( $quality ) {

	$instance = new MediaToolkitOutput();
	return $instance->set_compression_quality( $quality );

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
function replace_original_image( $metadata, $attachment_id, $context = 'create' ) {

	$instance = new MediaToolkitOutput();
	return $instance->replace_original_image( $metadata, $attachment_id, $context );

}
