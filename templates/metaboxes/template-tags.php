<?php
/**
 * Template tags metabox.
 *
 * @package MediaToolkit
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

$mediatoolkit_tags = array(
	'{author_first_name}',
	'{author_last_name}',
	'{author_full_name}',
	'{original_file_name}',
	'{upload_timestamp}',
	'{upload_date}',
	'{random_id}',
);
?>

<div class="heatbox tags-heatbox">
	<h2>
		<?php esc_html_e( 'Template tags', 'media-toolkit' ); ?>
		<span class="action-status"><?php esc_html_e( 'Copied!', 'media-toolkit' ); ?></span>
	</h2>
	<div class="heatbox-content">
		<p class="description"><?php esc_html_e( 'Use these tags in the filename structure. Click a tag to copy it.', 'media-toolkit' ); ?></p>
		<div class="tags-wrapper">
			<?php foreach ( $mediatoolkit_tags as $mediatoolkit_tag ) : ?>
				<code tabindex="0" role="button"><?php echo esc_html( $mediatoolkit_tag ); ?></code>
			<?php endforeach; ?>
		</div>
	</div>
</div>
