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

		$max_width  = isset( $settings['image_max_width'] ) ? $settings['image_max_width'] : 0;
		$max_height = isset( $settings['image_max_height'] ) ? $settings['image_max_height'] : 0;

		// Stop if the max width or height is not set.
		if ( empty( $max_width ) || empty( $max_height ) ) {
			return $metadata;
		}

		$image_path = get_attached_file( $attachment_id );
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
		$result = $editor->resize( $max_width, $max_height, false );

		if ( is_wp_error( $result ) ) {
			return $metadata;
		}

		// Quality & sizes are set, now save it.
		$result = $editor->save( $image_path, $mime_type );

		// If image is saved, let's update the necessary metadata.
		if ( ! is_wp_error( $result ) ) {
			// Might be useful to update some metadata.
		}

		return $metadata;

	}

}
