<?php
/**
 * Template tags metabox.
 *
 * @package MediaToolkit
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );
?>

<div class="heatbox mediatk-tags-metabox">
	<h2>
		<?php _e( 'Template tags', 'media-toolkit' ); ?>
		<span class="action-status">📋 Copied</span>
	</h2>
	<div class="heatbox-content">
		<p><?php _e( 'Available template tags to create a custom filename structure. <br><strong>(Click to copy)</strong>', 'media-toolkit' ); ?></p>
		<div class="tags-wrapper">
			<code>{author_first_name}</code>
			<code>{author_last_name}</code>
			<code>{author_full_name}</code>
			<code>{original_file_name}</code>
			<code>{upload_timestamp}</code>
			<code>{upload_date}</code>
			<code>{random_id}</code>
		</div>
	</div>
</div>
