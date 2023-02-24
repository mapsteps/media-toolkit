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
	$is_checked    = isset( $settings['replace_original_image'] ) ? true : false;
	?>

	<input
		type="number"
		step="1"
		min="0"
		id="mediatoolkit_settings--image-max-dimension"
		name="mediatoolkit_settings[image_max_dimension]"
		class="mediatk-dimension-field"
		value="<?php echo esc_attr( $max_dimension ); ?>" placeholder="2560"
	/>

	<br /><br />

	<label for="mediatoolkit_settings[replace_original_image]" class="label checkbox-label mediatk-checkbox-label">
		Replace the original image with the resized one using the max size above.<br />
		<span class="description">
			This will disable the <code>big_image_size_threshold</code> filter and removes the image exif data.
		</span>

		<input
			type="checkbox"
			name="mediatoolkit_settings[replace_original_image]"
			id="mediatoolkit_settings[replace_original_image]"
			value="1"
			<?php checked( $is_checked, true ); ?>
		/>

		<div class="indicator"></div>
	</label>

	<?php
};
