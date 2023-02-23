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

	<div class="mediatk-dimensions">
		<div class="mediatk-dimension">
			<label for="mediatoolkit_settings--image-max-width">Max width</label>
			<input
				type="number"
				step="1"
				min="0"
				id="mediatoolkit_settings--image-max-width"
				name="mediatoolkit_settings[image_max_width]"
				class="mediatk-dimension-field"
				value="<?php echo esc_attr( $max_width ); ?>" placeholder=""
			/>
		</div>

		<div class="mediatk-dimension">
			<label for="mediatoolkit_settings--image-max-height">Max height</label>
			<input
				type="number"
				step="1"
				min="0"
				id="mediatoolkit_settings--image-max-height"
				name="mediatoolkit_settings[image_max_height]"
				class="mediatk-dimension-field"
				value="<?php echo esc_attr( $max_height ); ?>" placeholder=""
			/>
		</div>
	</div>

	<br />

	<label for="mediatoolkit_settings[replace_original_image]" class="label checkbox-label mediatk-checkbox-label">
		Replace the original image with the resized one using the max size above.<br />This will also remove the image exif data.

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
