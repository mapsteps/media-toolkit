<?php
/**
 * Bulk optimize metabox.
 *
 * @package MediaToolkit
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );
?>

<div class="heatbox mediatk-bulk-metabox">
	<h2><?php esc_html_e( 'Optimize existing images', 'media-toolkit' ); ?></h2>
	<div class="heatbox-content">
		<p class="description"><?php esc_html_e( 'Apply your saved settings to the images you already uploaded:', 'media-toolkit' ); ?></p>
		<ul class="mediatk-bulk-list">
			<li><?php esc_html_e( 'Images larger than the max image size are downscaled (if "Limit image size" is enabled).', 'media-toolkit' ); ?></li>
			<li><?php esc_html_e( 'All image sizes (thumbnails etc.) are re-created with the quality set above.', 'media-toolkit' ); ?></li>
			<li><?php esc_html_e( 'Filenames & URLs stay the same and no files are deleted. Images are not renamed or converted to WebP.', 'media-toolkit' ); ?></li>
		</ul>
		<p class="mediatk-bulk-warning">
			<strong><?php esc_html_e( 'Files are overwritten. Please back up your uploads folder first.', 'media-toolkit' ); ?></strong>
		</p>
		<p class="mediatk-bulk-actions">
			<button type="button" class="button mediatk-bulk-button"><?php esc_html_e( 'Optimize existing images', 'media-toolkit' ); ?></button>
			<span class="mediatk-bulk-status" role="status" aria-live="polite"></span>
		</p>
		<p class="mediatk-bulk-running" hidden>
			<?php esc_html_e( 'Please keep this page open until it\'s done. If you leave or reload the page, the process stops and you\'ll have to start it again.', 'media-toolkit' ); ?>
		</p>
	</div>
</div>
