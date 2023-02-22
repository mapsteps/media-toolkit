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
	add_action( 'admin_enqueue_scripts', '\MediaToolkit\enqueue_admin_scripts' );

}

/**
 * Setup textdomain.
 */
function setup_text_domain() {

	load_plugin_textdomain( 'media-toolkit', false, MEDIA_TOOLKIT_PLUGIN_DIR . '/languages' );

}

/**
 * Enqueue admin scripts.
 */
function enqueue_admin_scripts() {

	$instance = new MediaToolkitSetup();
	$instance->enqueue_admin_scripts();

}
