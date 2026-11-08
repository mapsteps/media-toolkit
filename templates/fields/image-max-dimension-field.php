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
		min="1"
		id="mediatoolkit_settings--image-max-dimension"
		name="mediatoolkit_settings[image_max_dimension]"
		class="small-text"
		value="<?php echo esc_attr( $max_dimension ); ?>"
		placeholder="2560"
		aria-describedby="mediatoolkit-max-size-description"
	/> px

	<p class="description" id="mediatoolkit-max-size-description">
		<?php esc_html_e( 'The max width or height in pixels. No image on your server will be larger than this, including the sizes WordPress creates. Applies to new uploads only.', 'media-toolkit' ); ?>
	</p>

	<?php
};
