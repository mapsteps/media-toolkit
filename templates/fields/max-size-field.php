<?php
/**
 * Max size field.
 *
 * @package MediaToolkit
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Output "max size" field.
 *
 * @param array $settings The setting values.
 */
return function ( $settings ) {

	$is_checked = isset( $settings['replace_original_image'] ) ? true : false;
	$max_width  = isset( $settings['image_max_width'] ) ? $settings['image_max_width'] : '';
	$max_height = isset( $settings['image_max_height'] ) ? $settings['image_max_height'] : '';
	?>

	<div>
		<input
			type="number"
			step="1"
			min="0"
			id="mediatoolkit_settings--image-max-width"
			name="mediatoolkit_settings[image_max_width]"
			class="mediatk-size-field"
			value="<?php echo esc_attr( $max_width ); ?>" placeholder="Max width"
		/>

		<input
			type="number"
			step="1"
			min="0"
			id="mediatoolkit_settings--image-max-height"
			name="mediatoolkit_settings[image_max_height]"
			class="mediatk-size-field"
			value="<?php echo esc_attr( $max_height ); ?>" placeholder="Max height"
		/>
	</div>

	<br />

	<label for="mediatoolkit_settings[replace_original_image]" class="label checkbox-label">
		Replace the original image with the resized one using the max size above.

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
