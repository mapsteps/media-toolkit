<?php
/**
 * The setup utility class.
 *
 * @package MediaToolkit
 */

namespace MediaToolkit;

/**
 * The setup utility class.
 */
class MediaToolkitSetup {

	/**
	 * Check if we're on the Kirki settings page.
	 *
	 * @return bool
	 */
	public function is_settings_page() {
		$current_screen = get_current_screen();

		return ( 'media_page_media-toolkit' === $current_screen->id ? true : false );
	}

	/**
	 * Add admin body class.
	 *
	 * @param string $classes The admin body classes.
	 * @return string
	 */
	public function admin_body_class( $classes ) {

		if ( ! $this->is_settings_page() ) {
			return $classes;
		}

		$classes .= ' heatbox-admin has-header';

		return $classes;

	}

	/**
	 * Enqueue admin scripts.
	 */
	public function enqueue_admin_scripts() {

		if ( ! $this->is_settings_page() ) {
			return;
		}

		wp_enqueue_style( 'heatbox', MEDIA_TOOLKIT_PLUGIN_URL . '/assets/css/heatbox.css', array(), MEDIA_TOOLKIT_PLUGIN_VERSION );
		wp_enqueue_style( 'media-toolkit-settings', MEDIA_TOOLKIT_PLUGIN_URL . '/assets/css/settings-page.css', array(), MEDIA_TOOLKIT_PLUGIN_VERSION );

		wp_enqueue_script( 'media-toolkit', MEDIA_TOOLKIT_PLUGIN_URL . '/assets/js/settings-page.js', array(), MEDIA_TOOLKIT_PLUGIN_VERSION, true );

	}

	/**
	 * Add submenu page.
	 */
	public function add_submenu_page() {

		add_submenu_page(
			'upload.php',
			__( 'Media Toolkit', 'media-toolkit' ),
			__( 'Media Toolkit', 'media-toolkit' ),
			'manage_options',
			'media-toolkit',
			array( $this, 'render_submenu_page' )
		);

	}

	/**
	 * Render submenu page.
	 */
	public function render_submenu_page() {

		require __DIR__ . '/templates/settings-page.php';

	}

}
