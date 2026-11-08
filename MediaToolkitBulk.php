<?php
/**
 * Bulk optimize existing images.
 *
 * Files are overwritten in place: filenames & URLs stay the same and no files are deleted.
 *
 * @package MediaToolkit
 */

namespace MediaToolkit;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Bulk optimize existing images.
 */
class MediaToolkitBulk {

	/**
	 * Check the capability of a bulk request.
	 */
	private function check_capability() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to do this.', 'media-toolkit' ), 403 );
		}

	}

	/**
	 * Send the IDs of all images that can be optimized.
	 */
	public function ajax_get_ids() {

		check_ajax_referer( 'mediatoolkit_bulk', 'nonce' );
		$this->check_capability();

		$ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => array( 'image/jpeg', 'image/png', 'image/webp', 'image/avif' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);

		wp_send_json_success( array_map( 'absint', $ids ) );

	}

	/**
	 * Optimize a batch of images.
	 */
	public function ajax_optimize() {

		check_ajax_referer( 'mediatoolkit_bulk', 'nonce' );
		$this->check_capability();

		// Finish the batch even if the visitor leaves the page, so no image is left half-done.
		ignore_user_abort( true );

		$ids     = isset( $_POST['ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['ids'] ) ) : array();
		$results = array(
			'optimized' => 0,
			'skipped'   => 0,
			'failed'    => 0,
			'images'    => array(),
		);

		foreach ( array_slice( array_filter( $ids ), 0, 10 ) as $id ) {
			$result = $this->optimize( $id );

			++$results[ $result['status'] ];

			if ( 'skipped' !== $result['status'] ) {
				$results['images'][] = $result;
			}
		}

		wp_send_json_success( $results );

	}

	/**
	 * Get the files of an attachment: the "full" size, the original & all image sizes.
	 *
	 * @param string $full_path     The path of the "full" size.
	 * @param string $original_path The path of the original image.
	 * @param array  $metadata      The attachment metadata.
	 *
	 * @return array The paths.
	 */
	private function get_files( $full_path, $original_path, $metadata ) {

		$dir   = dirname( $full_path );
		$files = array( $full_path, $original_path );

		if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
			foreach ( $metadata['sizes'] as $size ) {
				if ( ! empty( $size['file'] ) ) {
					$files[] = $dir . '/' . wp_basename( $size['file'] );
				}
			}
		}

		return array_unique( array_filter( $files ) );

	}

	/**
	 * Get the image editor to create the sizes with.
	 *
	 * Like WordPress, the sizes are created from the original upload (rotated by its EXIF data),
	 * so they get the same filenames & dimensions as before. If the original can't be used
	 * (e.g. it lost its rotation when it was downscaled), the "full" size is used instead.
	 *
	 * @param string $full_path     The path of the "full" size.
	 * @param string $original_path The path of the original image.
	 * @param int    $attachment_id The attachment ID.
	 *
	 * @return array The editor (or WP_Error) & the source path.
	 */
	private function get_resize_editor( $full_path, $original_path, $attachment_id ) {

		if ( $original_path && $original_path !== $full_path && file_exists( $original_path ) ) {
			$editor    = wp_get_image_editor( $original_path );
			$full_size = wp_getimagesize( $full_path );

			if ( ! is_wp_error( $editor ) && $full_size ) {
				$editor->maybe_exif_rotate();

				$size = $editor->get_size();

				// Same aspect ratio means the same orientation as the "full" size.
				if ( $size['height'] && $full_size[1] && abs( $size['width'] / $size['height'] - $full_size[0] / $full_size[1] ) < 0.01 ) {
					return array( $editor, $original_path );
				}
			}
		}

		$source = $this->get_resize_source( $full_path, $original_path, $attachment_id );
		$editor = $source ? wp_get_image_editor( $source ) : new \WP_Error( 'mediatoolkit_source', 'The image could not be copied.' );

		return array( $editor, $source );

	}

	/**
	 * Get a copy of the "full" size to create the sizes from.
	 *
	 * WordPress names the sizes after the original upload ("photo-300x200.jpg"), even if the "full" size
	 * is "photo-scaled.jpg", "photo-rotated.jpg" or "photo.webp". To keep the same filenames, the "full" size
	 * is copied to a temporary folder under the original's name, with the format of the "full" size.
	 *
	 * @param string $full_path     The path of the "full" size.
	 * @param string $original_path The path of the original image.
	 * @param int    $attachment_id The attachment ID.
	 *
	 * @return string|false The source path or false on failure.
	 */
	private function get_resize_source( $full_path, $original_path, $attachment_id ) {

		if ( ! $original_path || $original_path === $full_path ) {
			return $full_path;
		}

		$tmp_dir = dirname( $full_path ) . '/mediatoolkit-tmp-' . absint( $attachment_id );
		$source  = $tmp_dir . '/' . pathinfo( $original_path, PATHINFO_FILENAME ) . '.' . pathinfo( $full_path, PATHINFO_EXTENSION );

		if ( ! wp_mkdir_p( $tmp_dir ) || ! copy( $full_path, $source ) ) {
			return false;
		}

		return $source;

	}

	/**
	 * Remove the temporary source image & folder.
	 *
	 * @param string|false $source    The source path.
	 * @param string       $full_path The path of the "full" size.
	 */
	private function remove_resize_source( $source, $full_path ) {

		if ( ! $source || $source === $full_path || false === strpos( $source, '/mediatoolkit-tmp-' ) ) {
			return;
		}

		$tmp_dir = dirname( $source );

		foreach ( (array) glob( $tmp_dir . '/*' ) as $file ) {
			wp_delete_file( $file );
		}

		if ( is_dir( $tmp_dir ) ) {
			rmdir( $tmp_dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
		}

	}

	/**
	 * Move the created sizes from the temporary folder next to the image.
	 *
	 * @param string|false $source    The source path.
	 * @param string       $dir       The upload directory of the image.
	 * @param array        $new_sizes The created image sizes.
	 *
	 * @return array The image sizes that were moved.
	 */
	private function move_sizes( $source, $dir, $new_sizes ) {

		$tmp_dir = dirname( $source );

		if ( $tmp_dir === $dir ) {
			return $new_sizes;
		}

		foreach ( $new_sizes as $name => $size ) {
			$file = wp_basename( $size['file'] );

			if ( ! copy( $tmp_dir . '/' . $file, $dir . '/' . $file ) ) {
				unset( $new_sizes[ $name ] );
			}
		}

		return $new_sizes;

	}

	/**
	 * Copy the existing image sizes, so they can be restored if the new ones are bigger.
	 *
	 * @param string $dir       The upload directory of the image.
	 * @param array  $old_sizes The image sizes from the metadata.
	 *
	 * @return array Backup paths, keyed by the size's filename.
	 */
	private function backup_sizes( $dir, $old_sizes ) {

		$backups = array();

		foreach ( $old_sizes as $size ) {
			if ( empty( $size['file'] ) ) {
				continue;
			}

			$file = wp_basename( $size['file'] );
			$path = $dir . '/' . $file;

			if ( file_exists( $path ) && copy( $path, $path . '.mediatoolkit-backup' ) ) {
				$backups[ $file ] = $path . '.mediatoolkit-backup';
			}
		}

		return $backups;

	}

	/**
	 * Restore old image sizes that were smaller than the new ones (same dimensions, same filename),
	 * e.g. when the quality is now higher than when the image was uploaded. Then remove the backups.
	 *
	 * @param string $dir       The upload directory of the image.
	 * @param array  $new_sizes The re-created image sizes.
	 * @param array  $backups   Backup paths, keyed by the size's filename.
	 *
	 * @return array The image sizes with updated file sizes.
	 */
	private function keep_smaller_sizes( $dir, $new_sizes, $backups ) {

		clearstatcache();

		foreach ( $new_sizes as $name => $size ) {
			$file = wp_basename( $size['file'] );
			$path = $dir . '/' . $file;

			if ( isset( $backups[ $file ] ) && file_exists( $path ) && filesize( $backups[ $file ] ) < filesize( $path ) ) {
				copy( $backups[ $file ], $path );
				clearstatcache();

				$new_sizes[ $name ]['filesize'] = filesize( $path );
			}
		}

		foreach ( $backups as $backup ) {
			wp_delete_file( $backup );
		}

		return $new_sizes;

	}

	/**
	 * Get the total size of files in bytes.
	 *
	 * @param array $files The paths.
	 * @return int
	 */
	private function get_bytes( $files ) {

		clearstatcache();

		$bytes = 0;

		foreach ( $files as $file ) {
			if ( file_exists( $file ) ) {
				$bytes += (int) filesize( $file );
			}
		}

		return $bytes;

	}

	/**
	 * The result of an image that couldn't be optimized.
	 *
	 * @param int    $attachment_id The attachment ID.
	 * @param string $reason        Why it failed & what to do about it.
	 *
	 * @return array
	 */
	private function failed( $attachment_id, $reason ) {

		return array(
			'status' => 'failed',
			'id'     => $attachment_id,
			'title'  => get_the_title( $attachment_id ),
			'edit'   => get_edit_post_link( $attachment_id, 'raw' ),
			'reason' => $reason,
		);

	}

	/**
	 * Downscale an image to the max image size & re-create its sizes with the current quality.
	 *
	 * @param int $attachment_id The attachment ID.
	 *
	 * @return array {
	 *     @type string $status "optimized", "skipped" or "failed".
	 *     Optimized images also contain the ID, title, thumbnail URL and their size before & after.
	 * }
	 */
	public function optimize( $attachment_id ) {

		if ( ! is_resizable_image( $attachment_id ) ) {
			return array( 'status' => 'skipped' );
		}

		$metadata  = wp_get_attachment_metadata( $attachment_id );
		$full_path = get_attached_file( $attachment_id );

		if ( ! $full_path || ! file_exists( $full_path ) ) {
			return $this->failed( $attachment_id, __( 'The image file is missing on the server. Upload the image again or delete it.', 'media-toolkit' ) );
		}

		if ( ! is_array( $metadata ) ) {
			return $this->failed( $attachment_id, __( 'WordPress has no image data for this file. Upload the image again.', 'media-toolkit' ) );
		}

		$original_path = wp_get_original_image_path( $attachment_id );
		$files_before  = $this->get_files( $full_path, $original_path, $metadata );
		$before        = array(
			'width'  => isset( $metadata['width'] ) ? absint( $metadata['width'] ) : 0,
			'height' => isset( $metadata['height'] ) ? absint( $metadata['height'] ) : 0,
			'bytes'  => $this->get_bytes( $files_before ),
		);

		$output        = new MediaToolkitOutput();
		$max_dimension = get_max_dimension();

		if ( $max_dimension ) {
			$metadata = $output->downscale_attachment( $attachment_id, $metadata, $max_dimension );
		}

		// Always store the real dimensions, this also repairs an earlier run that was interrupted.
		$image_size = wp_getimagesize( $full_path );

		if ( $image_size ) {
			$metadata['width']  = $image_size[0];
			$metadata['height'] = $image_size[1];

			if ( isset( $metadata['filesize'] ) ) {
				$metadata['filesize'] = wp_filesize( $full_path );
			}
		}

		$dir       = dirname( $full_path );
		$sizes     = apply_filters( 'intermediate_image_sizes_advanced', wp_get_registered_image_subsizes(), $metadata, $attachment_id );
		$old_sizes = isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ? $metadata['sizes'] : array();

		list( $editor, $source ) = $this->get_resize_editor( $full_path, $original_path, $attachment_id );

		if ( is_wp_error( $editor ) ) {
			$this->remove_resize_source( $source, $full_path );
			wp_update_attachment_metadata( $attachment_id, $metadata );

			if ( 'mediatoolkit_source' === $editor->get_error_code() ) {
				return $this->failed( $attachment_id, __( 'The image couldn\'t be saved. Please check that your uploads folder is writable.', 'media-toolkit' ) );
			}

			return $this->failed( $attachment_id, __( 'The image can\'t be opened. It may be damaged, or your server doesn\'t support this image format.', 'media-toolkit' ) );
		}

		$backups = $this->backup_sizes( $dir, $old_sizes );

		limit_quality( $editor, $source, get_saved_quality( $attachment_id ) );

		// The sizes get the format of the "full" size, e.g. WebP if the image was converted on upload.
		$source_type = wp_check_filetype( $source );
		$full_type   = wp_check_filetype( $full_path );
		$formats     = $source_type['type'] !== $full_type['type'] ? array( $source_type['type'] => $full_type['type'] ) : array();

		$new_sizes = save_without_conversion(
			function () use ( $editor, $sizes ) {
				return $editor->multi_resize( $sizes );
			},
			$formats
		);

		$new_sizes = $this->move_sizes( $source, $dir, $new_sizes );

		$this->remove_resize_source( $source, $full_path );

		$new_sizes = $this->keep_smaller_sizes( $dir, $new_sizes, $backups );

		// Keep sizes that are no longer registered (e.g. from a previous theme). Old files are never deleted.
		$metadata['sizes'] = array_merge( array_diff_key( $old_sizes, wp_get_registered_image_subsizes() ), $new_sizes );

		wp_update_attachment_metadata( $attachment_id, $metadata );

		// Files that are no longer used stay on the server, so they're counted too.
		$files_after = array_unique( array_merge( $files_before, $this->get_files( $full_path, $original_path, $metadata ) ) );
		$thumbnail   = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );

		return array(
			'status'    => 'optimized',
			'id'        => $attachment_id,
			'title'     => get_the_title( $attachment_id ),
			'edit'      => get_edit_post_link( $attachment_id, 'raw' ),
			'thumbnail' => $thumbnail ? add_query_arg( 'mediatoolkit', time(), $thumbnail ) : '',
			'before'    => $before,
			'after'     => array(
				'width'  => isset( $metadata['width'] ) ? absint( $metadata['width'] ) : 0,
				'height' => isset( $metadata['height'] ) ? absint( $metadata['height'] ) : 0,
				'bytes'  => $this->get_bytes( $files_after ),
			),
		);

	}

}
