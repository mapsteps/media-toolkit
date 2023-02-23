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
	$max_width  = isset( $settings['max_image_width'] ) ? $settings['max_image_width'] : '';
	$max_height = isset( $settings['max_image_height'] ) ? $settings['max_image_height'] : '';
	?>

	<div>
		<input
			type="number"
			step="1"
			min="0"
			id="mediatoolkit_settings--max-image-width"
			name="mediatoolkit_settings[max_image_width]"
			class="mediatk-size-field"
			value="<?php echo esc_attr( $max_width ); ?>" placeholder="Max width"
		/>

		<input
			type="number"
			step="1"
			min="0"
			id="mediatoolkit_settings--max-image-height"
			name="mediatoolkit_settings[max_image_height]"
			class="mediatk-size-field"
			value="<?php echo esc_attr( $max_height ); ?>" placeholder="Max height"
		/>
	</div>

	<br />

	<label for="mediatoolkit_settings[replace_original_image]" class="label checkbox-label">
		Replace the original image with the one that has the max size.

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
