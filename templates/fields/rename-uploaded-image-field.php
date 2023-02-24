<?php
/**
 * Change image filename field.
 *
 * @package MediaToolkit
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Output "Change image filename" field.
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
		class="mediatk-text-field is-large"
		value="<?php echo esc_attr( $rename_structure ); ?>" placeholder=""
	/>

	<p class="description">
		<?php _e( 'Format the uploaded image name. You can use the template tags here.', 'media-toolkit' ); ?>
	</p>

	<?php
};
