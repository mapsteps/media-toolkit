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
	add_action( 'wp_ajax_mediatoolkit_bulk_ids', '\MediaToolkit\bulk_ids' );
	add_action( 'wp_ajax_mediatoolkit_bulk_optimize', '\MediaToolkit\bulk_optimize' );

}

/**
 * Output the plugin's functionalities.
 */
function output() {

	add_filter( 'big_image_size_threshold', '\MediaToolkit\change_big_image_threshold', 10000 );
	add_filter( 'intermediate_image_sizes_advanced', '\MediaToolkit\remove_oversized_image_sizes', 10, 2 );
	add_filter( 'wp_handle_upload_prefilter', '\MediaToolkit\modify_attachment_filename' );
	add_filter( 'wp_editor_set_quality', '\MediaToolkit\set_compression_quality' );
	add_filter( 'wp_generate_attachment_metadata', '\MediaToolkit\replace_original_image', 10, 3 );
	add_filter( 'image_editor_output_format', '\MediaToolkit\convert_to_webp' );

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
 * Get the max image size, if "Limit image size" is enabled.
 *
 * @return int The max width or height, 0 if disabled.
 */
function get_max_dimension() {

	$settings = get_plugin_settings();

	if ( empty( $settings['replace_original_image'] ) || empty( $settings['image_max_dimension'] ) ) {
		return 0;
	}

	return absint( $settings['image_max_dimension'] );

}

/**
 * Check whether the server can save WebP images.
 *
 * @return bool
 */
function webp_supported() {

	return wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) );

}

/**
 * Check whether an attachment is an image we can resize.
 * GIFs are skipped, resizing them would remove their animation.
 *
 * @param int $attachment_id The attachment ID.
 * @return bool
 */
function is_resizable_image( $attachment_id ) {

	return in_array( get_post_mime_type( $attachment_id ), array( 'image/jpeg', 'image/png', 'image/webp', 'image/avif' ), true );

}

/**
 * Estimate the quality a JPEG was saved with, from its quantization table (like ImageMagick does).
 *
 * @param string $path The image path.
 * @return int The quality (1-100) or 0 if unknown, e.g. for other formats.
 */
function get_jpeg_quality( $path ) {

	$filetype = wp_check_filetype( $path );

	if ( 'image/jpeg' !== $filetype['type'] || ! is_readable( $path ) ) {
		return 0;
	}

	// phpcs:disable WordPress.WP.AlternativeFunctions -- Reading a few bytes of the header, WP_Filesystem can't seek.
	$handle = fopen( $path, 'rb' );

	if ( ! $handle ) {
		return 0;
	}

	$table = '';

	// Walk the segments (skipping e.g. EXIF data with its preview image) until the quantization table.
	if ( "\xFF\xD8" === fread( $handle, 2 ) ) {
		while ( ! feof( $handle ) ) {
			$header = fread( $handle, 4 );

			if ( 4 !== strlen( $header ) || "\xFF" !== $header[0] ) {
				break;
			}

			$marker  = ord( $header[1] );
			$segment = ( ord( $header[2] ) << 8 ) + ord( $header[3] ) - 2;

			if ( 0xDB === $marker ) {
				$table = fread( $handle, 65 );
				break;
			}

			// Start of the image data: no table found.
			if ( 0xDA === $marker || $segment < 0 ) {
				break;
			}

			fseek( $handle, $segment, SEEK_CUR );
		}
	}

	fclose( $handle );
	// phpcs:enable

	// Precision & table ID (1 byte), then 64 values. Only 8-bit tables.
	if ( 65 !== strlen( $table ) || ( ord( $table[0] ) >> 4 ) !== 0 ) {
		return 0;
	}

	// The standard luminance table (JPEG spec, Annex K), in zigzag order like in the file.
	$standard = array( 16, 11, 12, 14, 12, 10, 16, 14, 13, 14, 18, 17, 16, 19, 24, 40, 26, 24, 22, 22, 24, 49, 35, 37, 29, 40, 58, 51, 61, 60, 57, 51, 56, 55, 64, 72, 92, 78, 64, 68, 87, 69, 55, 56, 80, 109, 81, 87, 95, 98, 103, 104, 103, 62, 77, 113, 121, 112, 100, 120, 92, 101, 103, 99 );

	$sum = 0;

	for ( $i = 1; $i <= 64; $i++ ) {
		$sum += ord( $table[ $i ] );
	}

	$scale = $sum * 100 / array_sum( $standard );

	if ( $scale <= 0 ) {
		return 100;
	}

	$quality = $scale <= 100 ? ( 200 - $scale ) / 2 : 5000 / $scale;

	return (int) max( 1, min( 100, round( $quality ) ) );

}

/**
 * Get the quality WordPress uses to save an image format, including the plugin's quality setting.
 *
 * @param string $mime_type The mime type.
 * @return int
 */
function get_quality_setting( $mime_type ) {

	$quality = in_array( $mime_type, array( 'image/webp', 'image/avif' ), true ) ? 86 : 82;
	$quality = apply_filters( 'wp_editor_set_quality', $quality, $mime_type );

	if ( 'image/jpeg' === $mime_type ) {
		$quality = apply_filters( 'jpeg_quality', $quality, 'image_resize' );
	}

	return (int) $quality;

}

/**
 * Get the quality an image's "full" size was saved with, as stored by the plugin.
 *
 * @param int $attachment_id The attachment ID.
 * @return int The quality or 0 if unknown.
 */
function get_saved_quality( $attachment_id ) {

	return absint( get_post_meta( $attachment_id, '_mediatoolkit_quality', true ) );

}

/**
 * Store the quality an image's "full" size was saved with.
 *
 * @param int $attachment_id The attachment ID.
 * @param int $quality       The quality, 0 if unknown.
 */
function save_quality( $attachment_id, $quality ) {

	$quality = absint( $quality );

	if ( $quality > 0 && $quality <= 100 ) {
		update_post_meta( $attachment_id, '_mediatoolkit_quality', $quality );
	}

}

/**
 * Don't save an image with a higher quality than it already has: it only adds bytes, not detail.
 *
 * @param \WP_Image_Editor $editor        The image editor.
 * @param string           $path          The path of the image that was loaded.
 * @param int              $known_quality The quality the image was saved with, if known.
 */
function limit_quality( $editor, $path, $known_quality = 0 ) {

	$qualities = array_filter( array( absint( $known_quality ), get_jpeg_quality( $path ) ) );

	if ( $qualities && min( $qualities ) < $editor->get_quality() ) {
		$editor->set_quality( min( $qualities ) );
	}

}

/**
 * Run an image save without any other format conversion, so files keep their name & format.
 *
 * @param callable $callback       The callback that saves the image.
 * @param array    $output_formats Only these conversions (source => output mime type), none by default.
 *
 * @return mixed The callback's return value.
 */
function save_without_conversion( $callback, $output_formats = array() ) {

	$filter = function () use ( $output_formats ) {
		return $output_formats;
	};

	add_filter( 'image_editor_output_format', $filter, PHP_INT_MAX );

	$result = call_user_func( $callback );

	remove_filter( 'image_editor_output_format', $filter, PHP_INT_MAX );

	return $result;

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

	// Disable the threshold if the original image is being replaced.
	if ( get_max_dimension() ) {
		return false;
	}

	return $threshold;

}

/**
 * Don't create image sizes larger than the max image size when the original image is being replaced.
 *
 * The real dimensions depend on the image: e.g. "768 wide, any height" is 1152px high for a portrait photo.
 *
 * @param array $sizes      The image sizes to create, with their width, height & crop.
 * @param array $image_meta The image metadata, with the image's width & height.
 *
 * @return array
 */
function remove_oversized_image_sizes( $sizes, $image_meta = array() ) {

	$max_dimension = get_max_dimension();

	if ( ! $max_dimension || ! is_array( $sizes ) ) {
		return $sizes;
	}

	$image_width  = isset( $image_meta['width'] ) ? absint( $image_meta['width'] ) : 0;
	$image_height = isset( $image_meta['height'] ) ? absint( $image_meta['height'] ) : 0;

	foreach ( $sizes as $name => $size ) {
		$width  = isset( $size['width'] ) ? absint( $size['width'] ) : 0;
		$height = isset( $size['height'] ) ? absint( $size['height'] ) : 0;

		if ( $image_width && $image_height ) {
			$dimensions = image_resize_dimensions( $image_width, $image_height, $width, $height, ! empty( $size['crop'] ) );

			// No resize needed means the image is smaller, WordPress won't create this size anyway.
			if ( ! $dimensions ) {
				continue;
			}

			$width  = $dimensions[4];
			$height = $dimensions[5];
		}

		if ( $width > $max_dimension || $height > $max_dimension ) {
			unset( $sizes[ $name ] );
		}
	}

	return $sizes;

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

/**
 * Convert new JPEG & PNG uploads to WebP.
 *
 * @param array $formats Mime type mappings (source => output).
 * @return array
 */
function convert_to_webp( $formats ) {

	$instance = new MediaToolkitOutput();
	return $instance->convert_to_webp( $formats );

}

/**
 * Bulk optimize: get the IDs of all images.
 */
function bulk_ids() {

	$instance = new MediaToolkitBulk();
	$instance->ajax_get_ids();

}

/**
 * Bulk optimize: optimize a batch of images.
 */
function bulk_optimize() {

	$instance = new MediaToolkitBulk();
	$instance->ajax_optimize();

}
