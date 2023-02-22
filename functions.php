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
 * Setup the plugin.
 */
function setup() {

	add_action( 'init', '\MediaToolkit\setup_text_domain' );
	add_action( 'admin_menu', '\MediaToolkit\setup_submenu_page' );
	add_action( 'admin_enqueue_scripts', '\MediaToolkit\enqueue_admin_scripts' );
	add_filter( 'admin_body_class', '\MediaToolkit\setup_admin_body_class' );

}

/**
 * Setup textdomain.
 */
function setup_text_domain() {

	load_plugin_textdomain( 'media-toolkit', false, MEDIA_TOOLKIT_PLUGIN_DIR . '/languages' );

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
