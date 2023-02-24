<?php
/**
 * Image max dimension field.
 *
 * @package MediaToolkit
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Output "image max dimension" field.
 *
 * @param array $settings The setting values.
 */
return function ( $settings ) {

	$max_dimension = isset( $settings['image_max_dimension'] ) ? $settings['image_max_dimension'] : '';
	?>

	<input
		type="number"
		step="1"
		min="0"
		id="mediatoolkit_settings--image-max-dimension"
		name="mediatoolkit_settings[image_max_dimension]"
		class="mediatk-dimension-field"
		value="<?php echo esc_attr( $max_dimension ); ?>" placeholder="2560"
	/> <code>px</code>

	<?php
};
