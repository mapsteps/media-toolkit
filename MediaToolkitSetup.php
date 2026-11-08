<?php
/**
 * The setup utility class.
 *
 * @package MediaToolkit
 */

namespace MediaToolkit;

defined( 'ABSPATH' ) || die( "Can't access directly" );

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
	 * Check if we're on the Media Toolkit settings page.
	 *
	 * @return bool
	 */
	private function is_settings_page() {

		$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		return $current_screen && 'media_page_media-toolkit' === $current_screen->id;

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

		return $classes . ' heatbox-admin has-header';

	}

	/**
	 * Enqueue admin scripts.
	 */
	public function enqueue_admin_scripts() {

		if ( ! $this->is_settings_page() ) {
			return;
		}

		wp_enqueue_style( 'heatbox', MEDIA_TOOLKIT_PLUGIN_URL . '/assets/css/heatbox.css', array(), MEDIA_TOOLKIT_PLUGIN_VERSION );
		wp_enqueue_style( 'media-toolkit-settings', MEDIA_TOOLKIT_PLUGIN_URL . '/assets/css/settings-page.css', array( 'heatbox' ), MEDIA_TOOLKIT_PLUGIN_VERSION );

		wp_enqueue_script( 'media-toolkit', MEDIA_TOOLKIT_PLUGIN_URL . '/assets/js/settings-page.js', array(), MEDIA_TOOLKIT_PLUGIN_VERSION, true );

		wp_localize_script(
			'media-toolkit',
			'mediaToolkit',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'mediatoolkit_bulk' ),
				'i18n'    => array(
					'confirm'  => __( 'This overwrites image files on your server. Did you back up your uploads folder?', 'media-toolkit' ),
					'starting' => __( 'Looking for images…', 'media-toolkit' ),
					/* translators: 1: number of processed images, 2: total number of images. */
					'progress' => __( 'Optimizing… %1$d of %2$d', 'media-toolkit' ),
					/* translators: 1: optimized images, 2: skipped images, 3: failed images. */
					'done'     => __( 'Done! %1$d optimized, %2$d skipped, %3$d failed.', 'media-toolkit' ),
					'none'     => __( 'No images found.', 'media-toolkit' ),
					'error'    => __( 'Something went wrong. You can resume where it stopped.', 'media-toolkit' ),
					'failed'   => __( 'Something went wrong. Please reload the page and try again.', 'media-toolkit' ),
					/* translators: 1: number of processed images, 2: total number of images. */
					'stopped'  => __( 'The last run stopped at %1$d of %2$d images.', 'media-toolkit' ),
					/* translators: %s: saved disk space, e.g. "12.4 MB". */
					'saved'    => __( 'Saved so far: %s', 'media-toolkit' ),
					/* translators: %s: saved disk space, e.g. "12.4 MB". */
					'total'    => __( 'Saved %s of disk space.', 'media-toolkit' ),
				),
			)
		);

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

		$this->settings = get_plugin_settings();

		// Register settings.
		register_setting(
			'mediatoolkit-settings-group',
			'mediatoolkit_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);

		// Register sections.
		add_settings_section( 'mediatoolkit-rename-section', __( 'Change filename structure', 'media-toolkit' ), '__return_false', 'mediatoolkit-rename-settings' );
		add_settings_section( 'mediatoolkit-quality-section', __( 'Image quality & format', 'media-toolkit' ), '__return_false', 'mediatoolkit-quality-settings' );
		add_settings_section( 'mediatoolkit-replace-original-section', __( 'Max image size', 'media-toolkit' ), '__return_false', 'mediatoolkit-replace-original-settings' );

		// Fields.
		add_settings_field( 'rename-uploaded-image', __( 'Filename structure', 'media-toolkit' ), array( $this, 'rename_uploaded_image_field' ), 'mediatoolkit-rename-settings', 'mediatoolkit-rename-section', array( 'label_for' => 'mediatoolkit_settings--rename-uploaded-image' ) );
		add_settings_field( 'compression-quality', __( 'Quality', 'media-toolkit' ), array( $this, 'compression_quality_field' ), 'mediatoolkit-quality-settings', 'mediatoolkit-quality-section', array( 'label_for' => 'mediatoolkit_settings--compression-quality' ) );
		add_settings_field( 'convert-to-webp', __( 'Convert to WebP', 'media-toolkit' ), array( $this, 'convert_to_webp_field' ), 'mediatoolkit-quality-settings', 'mediatoolkit-quality-section', array( 'label_for' => 'mediatoolkit_settings--convert-to-webp' ) );
		add_settings_field( 'replace-original-image', __( 'Limit image size', 'media-toolkit' ), array( $this, 'replace_original_image_field' ), 'mediatoolkit-replace-original-settings', 'mediatoolkit-replace-original-section', array( 'label_for' => 'mediatoolkit_settings--replace-original-image' ) );
		add_settings_field( 'image-max-dimension', __( 'Max image size', 'media-toolkit' ), array( $this, 'image_max_dimension_field' ), 'mediatoolkit-replace-original-settings', 'mediatoolkit-replace-original-section', array( 'label_for' => 'mediatoolkit_settings--image-max-dimension' ) );

	}

	/**
	 * Sanitize the settings before they're saved.
	 *
	 * @param mixed $input The submitted values.
	 * @return array
	 */
	public function sanitize_settings( $input ) {

		$input    = is_array( $input ) ? $input : array();
		$settings = array();

		if ( isset( $input['image_rename_format'] ) ) {
			$settings['image_rename_format'] = sanitize_text_field( $input['image_rename_format'] );
		}

		// Empty means WordPress' default quality.
		if ( isset( $input['compression_quality'] ) && is_numeric( $input['compression_quality'] ) ) {
			$settings['compression_quality'] = min( 100, max( 1, absint( $input['compression_quality'] ) ) );
		}

		if ( ! empty( $input['convert_to_webp'] ) ) {
			$settings['convert_to_webp'] = 1;
		}

		if ( ! empty( $input['replace_original_image'] ) ) {
			$settings['replace_original_image'] = 1;
		}

		if ( ! empty( $input['image_max_dimension'] ) && absint( $input['image_max_dimension'] ) ) {
			$settings['image_max_dimension'] = absint( $input['image_max_dimension'] );
		}

		return $settings;

	}

	/**
	 * Render the "Change filename structure" field.
	 */
	public function rename_uploaded_image_field() {

		$field = require __DIR__ . '/templates/fields/rename-uploaded-image-field.php';
		$field( $this->settings );

	}

	/**
	 * Render the "Quality" field.
	 */
	public function compression_quality_field() {

		$field = require __DIR__ . '/templates/fields/compression-quality-field.php';
		$field( $this->settings );

	}

	/**
	 * Render the "Convert to WebP" field.
	 */
	public function convert_to_webp_field() {

		$field = require __DIR__ . '/templates/fields/convert-to-webp-field.php';
		$field( $this->settings );

	}

	/**
	 * Render the "Replace original image" field.
	 */
	public function replace_original_image_field() {

		$field = require __DIR__ . '/templates/fields/replace-original-image-field.php';
		$field( $this->settings );

	}

	/**
	 * Render the "Max image size" field.
	 */
	public function image_max_dimension_field() {

		$field = require __DIR__ . '/templates/fields/image-max-dimension-field.php';
		$field( $this->settings );

	}

}
