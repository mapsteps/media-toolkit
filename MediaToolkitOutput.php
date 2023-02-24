<?php
/**
 * The output utility class.
 *
 * @package MediaToolkit
 */

namespace MediaToolkit;

/**
 * The output utility class.
 */
class MediaToolkitOutput {

	/**
	 * Modify attachment filename.
	 *
	 * @param array $file The file info array.
	 * @return string The file info array.
	 */
	public function modify_attachment_filename( $file ) {

		$settings      = get_option( 'mediatoolkit_settings', [] );
		$rename_format = isset( $settings['image_rename_format'] ) ? $settings['image_rename_format'] : '';

		if ( ! $rename_format ) {
			return $file;
		}

		$current_user = wp_get_current_user();

		if ( $current_user && property_exists( $current_user, 'ID' ) ) {
			$first_name = $current_user->first_name;
			$first_name = empty( $first_name ) ? $current_user->display_name : $first_name;
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
		$upload_timestamp  = strtotime( 'now' );

		$upload_date = current_time( 'Y-m-d' );
		$unique_id   = uniqid();

		$filename = sanitize_text_field( $rename_format );
		$filename = str_ireplace( '{author_first_name}', $first_name, $filename );
		$filename = str_ireplace( '{author_last_name}', $last_name, $filename );
		$filename = str_ireplace( '{author_full_name}', $full_name, $filename );
		$filename = str_ireplace( '{original_file_name}', $original_filename, $filename );
		$filename = str_ireplace( '{upload_timestamp}', $upload_timestamp, $filename );
		$filename = str_ireplace( '{upload_date}', $upload_date, $filename );
		$filename = str_ireplace( '{random_id}', $unique_id, $filename );

		// Sanitize the file name.
		$filename = trim( $filename );
		$filename = strtolower( $filename );
		$filename = str_replace( ' ', '-', $filename );
		$filename = sanitize_title( $filename );
		$filename = $filename . $ext;
		$filename = apply_filters( 'mediatoolkit_attachment_filename', $filename );

		$file['name'] = $filename;

		return $file;

	}

	/**
	 * Set compression quality.
	 *
	 * @param int    $quality The current compression quality.
	 * @param string $mime_type The mime type.
	 *
	 * @return int
	 */
	public function set_compression_quality( $quality, $mime_type ) {

		$settings    = get_option( 'mediatoolkit_settings', [] );
		$new_quality = isset( $settings['compression_quality'] ) ? absint( $settings['compression_quality'] ) : 0;

		if ( empty( $new_quality ) ) {
			return $quality;
		}

		return $new_quality;

	}

	/**
	 * Replace the original uploaded image with custom-defined size.
	 * Please be aware that the image exif data will be removed.
	 *
	 * We don't hook into `wp_handle_upload` because it runs before images resizing.
	 * Instead, we hook it into `wp_generate_attachment_metadata` action.
	 * This way, the other resized images won't be affected.
	 *
	 * Thanks to answers in these links, it helps us a lot:
	 *
	 * @see https://wordpress.stackexchange.com/questions/224536/how-to-reduce-original-image-quality-on-upload
	 * @see https://wordpress.stackexchange.com/questions/76602/how-can-you-set-maximum-width-for-original-images
	 *
	 * After following those links, the best way to implement it is to also look at the sources:
	 * @see https://github.com/WordPress/WordPress/blob/be6a91bf581296ce67acf242f9e92e940c908003/wp-admin/includes/media.php
	 * @see https://github.com/WordPress/WordPress/blob/be6a91bf581296ce67acf242f9e92e940c908003/wp-admin/includes/image.php
	 *
	 * @param array  $metadata      An array of attachment meta data.
	 * @param int    $attachment_id Current attachment ID.
	 * @param string $context       Additional context. Can be 'create' when metadata was initially created for new attachment
	 *                              or 'update' when the metadata was updated.
	 */
	public function replace_original_image( $metadata, $attachment_id, $context ) {

		// Stop if the context is not 'create'.
		if ( 'create' !== $context ) {
			return $metadata;
		}

		$settings = get_option( 'mediatoolkit_settings', [] );
		$enabled  = isset( $settings['replace_original_image'] ) ? absint( $settings['replace_original_image'] ) : 0;

		if ( ! $enabled ) {
			return $metadata;
		}

		$max_dimension = isset( $settings['image_max_dimension'] ) ? absint( $settings['image_max_dimension'] ) : 0;

		// Stop if the max dimension is not set.
		if ( empty( $max_dimension ) ) {
			return $metadata;
		}

		/**
		 * If `big_image_size_threshold` is enabled, the real original image will stay
		 * but the "full" size will be scaled (the filename will have "-scaled" suffix).
		 *
		 * That's why we should use `wp_get_original_image_path` instead of `get_attached_file`.
		 * Because `get_attached_file` can return the scaled image path.
		 */
		$image_path = wp_get_original_image_path( $attachment_id );
		$mime_type  = get_post_mime_type( $attachment_id );

		// Stop if the mime type is not an image.
		if ( ! preg_match( '!^image/!', $mime_type ) ) {
			return $metadata;
		}

		$editor = wp_get_image_editor( $image_path );

		if ( is_wp_error( $editor ) ) {
			return $metadata;
		}

		/**
		 * Number of compression quality percentage.
		 *
		 * @var int $quality_target Compression quality of the new original image.
		 */
		$quality_target = isset( $settings['compression_quality'] ) ? absint( $settings['compression_quality'] ) : 0;

		if ( $quality_target ) {
			// Set the new compression quality.
			$result = $editor->set_quality( $quality_target );

			if ( is_wp_error( $result ) ) {
				return $metadata;
			}
		}

		// Resize the image.
		$result = $editor->resize( $max_dimension, $max_dimension, false );

		if ( is_wp_error( $result ) ) {
			return $metadata;
		}

		// Quality & sizes are set, now save it.
		$result = $editor->save( $image_path, $mime_type );

		// If image is saved, let's update the necessary metadata.
		if ( ! is_wp_error( $result ) ) {
			// Might be useful to update some metadata.
			do_action( 'mediatoolkit_after_replace_original_image', $attachment_id, $metadata );
		}

		return $metadata;

	}

}
