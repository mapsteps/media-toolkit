<?php
/**
 * Replace original image field.
 *
 * @package MediaToolkit
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Output "replace original image" field.
 *
 * @param array $settings The setting values.
 */
return function ( $settings ) {

	$is_checked = isset( $settings['replace_original_image'] ) ? true : false;
	?>

	<label for="mediatoolkit_settings[replace_original_image]" class="toggle-switch">
		<input
			type="checkbox"
			name="mediatoolkit_settings[replace_original_image]"
			id="mediatoolkit_settings[replace_original_image]"
			value="1"
			<?php checked( $is_checked, true ); ?>
		/>
		<div class="switch-track">
			<div class="switch-thumb"></div>
		</div>
	</label>

	<?php
};
