<?php
/**
 * Template tags metabox.
 *
 * @package MediaToolkit
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );
?>

<div class="heatbox mediatk-tags-metabox">
	<h2><?php _e( 'Template tags', 'media-toolkit' ); ?></h2>
	<div class="heatbox-content">
		<p><?php _e( 'Use the template tags below in "Rename uploaded image" field to build the uploaded file name.', 'media-toolkit' ); ?></p>
		<p class="tags-wrapper">
			<code>{author_first_name}</code>, <code>{author_last_name}</code>, <code>{author_full_name}</code>, <code>{original_file_name}</code>, <code>{upload_timestamp}</code>, <code>{upload_date}</code>, <code>{random_id}</code>
		</p>
	</div>
</div>
