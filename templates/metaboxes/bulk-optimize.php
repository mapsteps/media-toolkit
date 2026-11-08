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
			<li><?php esc_html_e( 'Images and all their sizes (thumbnails etc.) are saved again with the quality set above, but never with a higher quality than they already have, and never replaced by a bigger file.', 'media-toolkit' ); ?></li>
			<li><?php esc_html_e( 'Filenames & URLs stay the same and no files are deleted. Images are not renamed or converted to WebP.', 'media-toolkit' ); ?></li>
		</ul>
		<p class="mediatk-bulk-warning">
			<strong><?php esc_html_e( 'Files are overwritten. Please back up your uploads folder first.', 'media-toolkit' ); ?></strong>
		</p>

		<div class="mediatk-bulk-actions">
			<button type="button" class="button mediatk-bulk-button"><?php esc_html_e( 'Optimize existing images', 'media-toolkit' ); ?></button>
			<button type="button" class="button button-primary mediatk-bulk-resume" hidden><?php esc_html_e( 'Resume', 'media-toolkit' ); ?></button>
			<span class="mediatk-bulk-status" role="status" aria-live="polite"></span>
		</div>

		<div class="mediatk-bulk-confirm" hidden>
			<p><strong><?php esc_html_e( 'Did you back up your uploads folder?', 'media-toolkit' ); ?></strong></p>
			<div class="mediatk-bulk-actions">
				<button type="button" class="button button-primary mediatk-bulk-confirm-yes"><?php esc_html_e( 'Yes, start optimizing', 'media-toolkit' ); ?></button>
				<button type="button" class="button mediatk-bulk-confirm-no"><?php esc_html_e( 'Cancel', 'media-toolkit' ); ?></button>
			</div>
		</div>

		<div class="mediatk-bulk-progress" hidden>
			<div class="mediatk-bulk-summary">
				<div class="mediatk-stat">
					<span class="mediatk-stat-label"><?php esc_html_e( 'Images', 'media-toolkit' ); ?></span>
					<span class="mediatk-stat-value mediatk-bulk-count"></span>
				</div>
				<div class="mediatk-stat">
					<span class="mediatk-stat-label"><?php esc_html_e( 'Saved so far', 'media-toolkit' ); ?></span>
					<span class="mediatk-stat-value mediatk-bulk-saved"></span>
				</div>
			</div>

			<div class="mediatk-progress" role="progressbar" aria-label="<?php esc_attr_e( 'Optimizing images', 'media-toolkit' ); ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
				<div class="mediatk-progress-bar"></div>
			</div>

			<div class="mediatk-bulk-current" hidden>
				<img class="mediatk-bulk-current-image" src="" alt="">
				<div class="mediatk-bulk-current-info">
					<span class="mediatk-bulk-current-title"></span>
					<dl class="mediatk-bulk-compare">
						<div>
							<dt><?php esc_html_e( 'Dimensions', 'media-toolkit' ); ?></dt>
							<dd><span class="mediatk-before mediatk-bulk-dimensions-before"></span> <span class="mediatk-arrow" aria-hidden="true">→</span> <strong class="mediatk-bulk-dimensions-after"></strong></dd>
						</div>
						<div>
							<dt><?php esc_html_e( 'File size', 'media-toolkit' ); ?></dt>
							<dd><span class="mediatk-before mediatk-bulk-bytes-before"></span> <span class="mediatk-arrow" aria-hidden="true">→</span> <strong class="mediatk-bulk-bytes-after"></strong> <span class="mediatk-badge mediatk-bulk-percent"></span></dd>
						</div>
					</dl>
				</div>
			</div>
		</div>

		<div class="mediatk-bulk-done" hidden>
			<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
			<div>
				<strong class="mediatk-bulk-done-title"><?php esc_html_e( 'All done!', 'media-toolkit' ); ?></strong>
				<span class="mediatk-bulk-done-saved"></span>
				<span class="mediatk-bulk-done-counts"></span>
				<span class="mediatk-bulk-done-note" hidden></span>
			</div>
		</div>

		<div class="mediatk-bulk-log" hidden>
			<ul class="mediatk-bulk-log-list" aria-label="<?php esc_attr_e( 'Optimized images', 'media-toolkit' ); ?>"></ul>
			<p class="mediatk-bulk-log-limit" hidden></p>
		</div>

		<p class="mediatk-bulk-running" hidden>
			<?php esc_html_e( 'Please keep this page open until it\'s done. If you leave, you can resume where it stopped.', 'media-toolkit' ); ?>
		</p>
	</div>
</div>
