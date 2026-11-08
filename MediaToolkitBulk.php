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

		$ids     = isset( $_POST['ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['ids'] ) ) : array();
		$results = array(
			'optimized' => 0,
			'skipped'   => 0,
			'failed'    => 0,
		);

		foreach ( array_slice( array_filter( $ids ), 0, 10 ) as $id ) {
			++$results[ $this->optimize( $id ) ];
		}

		wp_send_json_success( $results );

	}

	/**
	 * Downscale an image to the max image size & re-create its sizes with the current quality.
	 *
	 * @param int $attachment_id The attachment ID.
	 * @return string "optimized", "skipped" or "failed".
	 */
	public function optimize( $attachment_id ) {

		if ( ! is_resizable_image( $attachment_id ) ) {
			return 'skipped';
		}

		$metadata  = wp_get_attachment_metadata( $attachment_id );
		$full_path = get_attached_file( $attachment_id );

		if ( ! is_array( $metadata ) || ! $full_path || ! file_exists( $full_path ) ) {
			return 'failed';
		}

		$output        = new MediaToolkitOutput();
		$max_dimension = get_max_dimension();

		if ( $max_dimension ) {
			$metadata = $output->downscale_attachment( $attachment_id, $metadata, $max_dimension );
		}

		// Re-create the sizes from the "full" size, in the same format.
		$editor = wp_get_image_editor( $full_path );

		if ( is_wp_error( $editor ) ) {
			return 'failed';
		}

		$sizes = apply_filters( 'intermediate_image_sizes_advanced', wp_get_registered_image_subsizes(), $metadata, $attachment_id );

		$new_sizes = save_without_conversion(
			function () use ( $editor, $sizes ) {
				return $editor->multi_resize( $sizes );
			}
		);

		$old_sizes = isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ? $metadata['sizes'] : array();

		// Keep sizes that are no longer registered (e.g. from a previous theme). Old files are never deleted.
		$metadata['sizes'] = array_merge( array_diff_key( $old_sizes, wp_get_registered_image_subsizes() ), $new_sizes );

		wp_update_attachment_metadata( $attachment_id, $metadata );

		return 'optimized';

	}

}
