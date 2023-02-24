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
	 * The settings.
	 *
	 * @var array
	 */
	private $settings = array();

	/**
	 * Check if we're on the Kirki settings page.
	 *
	 * @return bool
	 */
	private function is_settings_page() {
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

	/**
	 * Add settings.
	 */
	public function add_settings() {

		$this->settings = get_option( 'mediatoolkit_settings', [] );

		// Register settings.
		register_setting( 'mediatoolkit-settings-group', 'mediatoolkit_settings' );

		// Register sections.
		add_settings_section( 'mediatoolkit-rename-section', __( 'Rename uploaded image', 'media-toolkit' ), '', 'mediatoolkit-rename-settings' );
		add_settings_section( 'mediatoolkit-quality-section', __( 'Image quality', 'media-toolkit' ), '', 'mediatoolkit-quality-settings' );
		add_settings_section( 'mediatoolkit-replace-original-section', __( 'Replace original image', 'media-toolkit' ), '', 'mediatoolkit-replace-original-settings' );

		// General fields.
		add_settings_field( 'rename-uploaded-image', __( 'File name structure', 'media-toolkit' ), array( $this, 'rename_uploaded_image_field' ), 'mediatoolkit-rename-settings', 'mediatoolkit-rename-section' );
		add_settings_field( 'compression-quality', __( 'Compression quality', 'media-toolkit' ), array( $this, 'compression_quality_field' ), 'mediatoolkit-quality-settings', 'mediatoolkit-quality-section' );

		$max_dimension_label = sprintf(
			/* translators: %s: The max upload size. */
			__( 'Max image dimension %1$s', 'media-toolkit' ),
			'<div class="description">Max image size (in px) for both width and height</div>'
		);

		add_settings_field( 'replace-original-image', __( 'Replace original image', 'media-toolkit' ), array( $this, 'replace_original_image_field' ), 'mediatoolkit-replace-original-settings', 'mediatoolkit-replace-original-section' );
		add_settings_field( 'image-max-dimension', $max_dimension_label, array( $this, 'image_max_dimension_field' ), 'mediatoolkit-replace-original-settings', 'mediatoolkit-replace-original-section' );

	}

	/**
	 * Render the "rename uploaded image" field.
	 */
	public function rename_uploaded_image_field() {

		$field = require __DIR__ . '/templates/fields/rename-uploaded-image-field.php';
		$field( $this->settings );

	}

	/**
	 * Render the "compression quality" field.
	 */
	public function compression_quality_field() {

		$field = require __DIR__ . '/templates/fields/compression-quality-field.php';
		$field( $this->settings );

	}

	/**
	 * Render the "replace original image" field.
	 */
	public function replace_original_image_field() {

		$field = require __DIR__ . '/templates/fields/replace-original-image-field.php';
		$field( $this->settings );

	}

	/**
	 * Render the "image max dimension" field.
	 */
	public function image_max_dimension_field() {

		$field = require __DIR__ . '/templates/fields/image-max-dimension-field.php';
		$field( $this->settings );

	}

}
