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

	$is_checked = ! empty( $settings['replace_original_image'] );
	?>

	<label class="toggle-switch">
		<input
			type="checkbox"
			name="mediatoolkit_settings[replace_original_image]"
			id="mediatoolkit_settings--replace-original-image"
			value="1"
			aria-describedby="mediatoolkit-replace-description"
			<?php checked( $is_checked ); ?>
		/>
		<span class="switch-track"><span class="switch-thumb"></span></span>
	</label>

	<p class="description" id="mediatoolkit-replace-description">
		<?php esc_html_e( 'Downscale new image uploads to the max image size below. The full-size original is replaced, not kept as a backup.', 'media-toolkit' ); ?>
	</p>

	<?php
};
