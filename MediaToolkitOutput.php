<?php
/**
 * The output utility class.
 *
 * @package MediaToolkit
 */

namespace MediaToolkit;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * The output utility class.
 */
class MediaToolkitOutput {

	/**
	 * Rename uploaded images based on the filename structure setting.
	 *
	 * @param array $file The file info array.
	 * @return array The file info array.
	 */
	public function modify_attachment_filename( $file ) {

		$settings      = get_plugin_settings();
		$rename_format = isset( $settings['image_rename_format'] ) ? $settings['image_rename_format'] : '';

		if ( ! $rename_format || empty( $file['name'] ) ) {
			return $file;
		}

		// Only rename images.
		$filetype = wp_check_filetype( $file['name'] );

		if ( empty( $filetype['type'] ) || 0 !== strpos( $filetype['type'], 'image/' ) ) {
			return $file;
		}

		$current_user = wp_get_current_user();

		if ( $current_user->exists() ) {
			$first_name = $current_user->first_name ? $current_user->first_name : $current_user->display_name;
			$last_name  = $current_user->last_name;
			$full_name  = $first_name . ' ' . $last_name;
		} else {
			$first_name = '';
			$last_name  = '';
			$full_name  = '';
		}

		$info = pathinfo( $file['name'] );
		$ext  = empty( $info['extension'] ) ? '' : '.' . $info['extension'];

		$original_filename = basename( $file['name'], $ext );

		$filename = str_ireplace(
			array(
				'{author_first_name}',
				'{author_last_name}',
				'{author_full_name}',
				'{original_file_name}',
				'{upload_timestamp}',
				'{upload_date}',
				'{random_id}',
			),
			array(
				$first_name,
				$last_name,
				$full_name,
				$original_filename,
				time(),
				current_time( 'Y-m-d' ),
				uniqid(),
			),
			sanitize_text_field( $rename_format )
		);

		$filename = sanitize_title( strtolower( trim( $filename ) ) );

		// Keep the original name if nothing is left of the new one.
		if ( '' === $filename ) {
			return $file;
		}

		$file['name'] = apply_filters( 'mediatoolkit_attachment_filename', $filename . $ext );

		return $file;

	}

	/**
	 * Set compression quality.
	 *
	 * @param int $quality The current compression quality.
	 * @return int
	 */
	public function set_compression_quality( $quality ) {

		$settings    = get_plugin_settings();
		$new_quality = isset( $settings['compression_quality'] ) ? absint( $settings['compression_quality'] ) : 0;

		if ( ! $new_quality ) {
			return $quality;
		}

		return min( 100, $new_quality );

	}

	/**
	 * Replace the original uploaded image with a downscaled version.
	 * The image's EXIF data will be removed.
	 *
	 * This runs on `wp_generate_attachment_metadata` instead of `wp_handle_upload`,
	 * so the sub-sizes are already generated from the full-quality original.
	 *
	 * @see https://wordpress.stackexchange.com/questions/224536/how-to-reduce-original-image-quality-on-upload
	 * @see https://wordpress.stackexchange.com/questions/76602/how-can-you-set-maximum-width-for-original-images
	 *
	 * @param array  $metadata      An array of attachment meta data.
	 * @param int    $attachment_id Current attachment ID.
	 * @param string $context       'create' for a new attachment, 'update' when the metadata was updated.
	 *
	 * @return array
	 */
	public function replace_original_image( $metadata, $attachment_id, $context = 'create' ) {

		if ( 'create' !== $context || ! is_array( $metadata ) ) {
			return $metadata;
		}

		$settings      = get_plugin_settings();
		$enabled       = ! empty( $settings['replace_original_image'] );
		$max_dimension = isset( $settings['image_max_dimension'] ) ? absint( $settings['image_max_dimension'] ) : 0;

		if ( ! $enabled || ! $max_dimension ) {
			return $metadata;
		}

		$mime_type = get_post_mime_type( $attachment_id );

		if ( ! $mime_type || 0 !== strpos( $mime_type, 'image/' ) ) {
			return $metadata;
		}

		/**
		 * If the image was scaled or rotated, the real original stays next to the "full" size.
		 * `wp_get_original_image_path` returns that original, `get_attached_file` would return the "full" size.
		 */
		$image_path = wp_get_original_image_path( $attachment_id );

		if ( ! $image_path || ! file_exists( $image_path ) ) {
			return $metadata;
		}

		$editor = wp_get_image_editor( $image_path );

		if ( is_wp_error( $editor ) ) {
			return $metadata;
		}

		// Smaller images return an error here and stay as they are.
		if ( is_wp_error( $editor->resize( $max_dimension, $max_dimension, false ) ) ) {
			return $metadata;
		}

		$result = $editor->save( $image_path, $mime_type );

		if ( is_wp_error( $result ) ) {
			return $metadata;
		}

		// The original is the "full" size, so its dimensions & file size changed.
		if ( empty( $metadata['original_image'] ) ) {
			$metadata['width']  = $result['width'];
			$metadata['height'] = $result['height'];

			if ( isset( $metadata['filesize'] ) ) {
				$metadata['filesize'] = wp_filesize( $image_path );
			}
		}

		do_action( 'mediatoolkit_after_replace_original_image', $attachment_id, $metadata );

		return $metadata;

	}

}
