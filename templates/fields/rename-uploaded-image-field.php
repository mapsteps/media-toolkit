<?php
/**
 * Change filename structure field.
 *
 * @package MediaToolkit
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Output "Change filename structure" field.
 *
 * @param array $settings The setting values.
 */
return function ( $settings ) {

	$rename_structure = isset( $settings['image_rename_format'] ) ? $settings['image_rename_format'] : '';
	?>

	<input
		type="text"
		id="mediatoolkit_settings--rename-uploaded-image"
		name="mediatoolkit_settings[image_rename_format]"
		class="regular-text"
		value="<?php echo esc_attr( $rename_structure ); ?>"
		placeholder="{original_file_name}-{upload_date}"
		aria-describedby="mediatoolkit-rename-description"
	/>

	<p class="description" id="mediatoolkit-rename-description">
		<?php esc_html_e( 'Rename new image uploads. Other files like PDFs or videos keep their name. Use the template tags from the sidebar, or leave empty to keep the original filename.', 'media-toolkit' ); ?>
	</p>

	<?php
};
