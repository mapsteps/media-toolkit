<?php
/**
 * Rename uploaded image field.
 *
 * @package MediaToolkit
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Output "rename uploaded image" field.
 *
 * @param array $settings The setting values.
 */
return function ( $settings ) {

	$rename_structure = isset( $settings['rename_uploaded_image'] ) ? $settings['rename_uploaded_image'] : '';
	?>

	<input
		type="text"
		id="mediatoolkit_settings--rename-uploaded-image"
		name="mediatoolkit_settings[rename_uploaded_image]"
		class="regular-text"
		value="<?php echo esc_attr( $rename_structure ); ?>" placeholder=""
	/>

	<p class="description">
		<?php _e( 'You can use the template tags here.', 'media-toolkit' ); ?>
	</p>

	<?php
};
