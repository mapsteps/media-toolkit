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
	 * Convert new JPEG & PNG uploads to WebP.
	 * WordPress saves the converted image as the "full" size and keeps the upload as the original.
	 *
	 * @param array $formats Mime type mappings (source => output).
	 * @return array
	 */
	public function convert_to_webp( $formats ) {

		$settings = get_plugin_settings();

		if ( empty( $settings['convert_to_webp'] ) || ! webp_supported() ) {
			return $formats;
		}

		$formats = is_array( $formats ) ? $formats : array();

		$formats['image/jpeg'] = 'image/webp';
		$formats['image/png']  = 'image/webp';

		return $formats;

	}

	/**
	 * Downscale an image file in place (same filename & format) if it's larger than the max dimension.
	 * The image's EXIF data will be removed.
	 *
	 * @param string $path          The image path.
	 * @param int    $max_dimension The max width or height.
	 *
	 * @return array|false The saved image data (path, width, height ...) or false if nothing was changed.
	 */
	public function downscale_image( $path, $max_dimension ) {

		if ( ! $path || ! file_exists( $path ) ) {
			return false;
		}

		$editor = wp_get_image_editor( $path );

		if ( is_wp_error( $editor ) ) {
			return false;
		}

		$size = $editor->get_size();

		if ( $size['width'] <= $max_dimension && $size['height'] <= $max_dimension ) {
			return false;
		}

		if ( is_wp_error( $editor->resize( $max_dimension, $max_dimension, false ) ) ) {
			return false;
		}

		limit_quality( $editor, $path );

		$filetype  = wp_check_filetype( $path );
		$old_bytes = (int) filesize( $path );
		$tmp_path  = dirname( $path ) . '/' . pathinfo( $path, PATHINFO_FILENAME ) . '-mediatoolkit-tmp.' . pathinfo( $path, PATHINFO_EXTENSION );
		$lossy     = in_array( $filetype['type'], array( 'image/jpeg', 'image/webp', 'image/avif' ), true );

		/*
		 * Fewer pixels must give a smaller file. If it's bigger, the image was saved with a lower quality
		 * than the current setting (WebP & AVIF don't tell us theirs), so lower the quality step by step.
		 */
		do {
			$result = save_without_conversion(
				function () use ( $editor, $tmp_path, $filetype ) {
					return $editor->save( $tmp_path, $filetype['type'] );
				}
			);

			if ( is_wp_error( $result ) ) {
				wp_delete_file( $tmp_path );
				return false;
			}

			clearstatcache();

			$quality = $editor->get_quality();
			$retry   = $lossy && (int) filesize( $tmp_path ) >= $old_bytes && $quality > 50;

			if ( $retry ) {
				$editor->set_quality( max( 50, $quality - 10 ) );
			}
		} while ( $retry );

		// Replace the image with the downscaled one.
		if ( ! copy( $tmp_path, $path ) ) {
			wp_delete_file( $tmp_path );
			return false;
		}

		wp_delete_file( $tmp_path );

		$result['path'] = $path;
		$result['file'] = wp_basename( $path );

		return $result;

	}

	/**
	 * Downscale new uploads to the max image size.
	 *
	 * This runs on `wp_generate_attachment_metadata` instead of `wp_handle_upload`,
	 * so the sub-sizes are already generated from the full-quality original.
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

		$max_dimension = get_max_dimension();

		if ( ! $max_dimension || ! is_resizable_image( $attachment_id ) ) {
			return $metadata;
		}

		$metadata = $this->downscale_attachment( $attachment_id, $metadata, $max_dimension );

		do_action( 'mediatoolkit_after_replace_original_image', $attachment_id, $metadata );

		return $metadata;

	}

	/**
	 * Downscale the original & the "full" size of an attachment and update its metadata.
	 *
	 * If the image was scaled, rotated or converted, the original stays next to the "full" size.
	 * `wp_get_original_image_path` returns the original, `get_attached_file` the "full" size.
	 *
	 * @param int   $attachment_id The attachment ID.
	 * @param array $metadata      The attachment metadata.
	 * @param int   $max_dimension The max width or height.
	 *
	 * @return array The updated metadata.
	 */
	public function downscale_attachment( $attachment_id, $metadata, $max_dimension ) {

		$full_path     = get_attached_file( $attachment_id );
		$original_path = wp_get_original_image_path( $attachment_id );

		if ( $original_path && $original_path !== $full_path ) {
			$this->downscale_image( $original_path, $max_dimension );
		}

		$result = $this->downscale_image( $full_path, $max_dimension );

		if ( $result ) {
			$metadata['width']  = $result['width'];
			$metadata['height'] = $result['height'];

			if ( isset( $metadata['filesize'] ) ) {
				$metadata['filesize'] = wp_filesize( $full_path );
			}
		}

		return $metadata;

	}

}
