<?php
/**
 * Convert to WebP field.
 *
 * @package MediaToolkit
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Output "Convert to WebP" field.
 *
 * @param array $settings The setting values.
 */
return function ( $settings ) {

	$is_supported = \MediaToolkit\webp_supported();
	$is_checked   = $is_supported && ! empty( $settings['convert_to_webp'] );
	?>

	<label class="toggle-switch">
		<input
			type="checkbox"
			name="mediatoolkit_settings[convert_to_webp]"
			id="mediatoolkit_settings--convert-to-webp"
			value="1"
			aria-describedby="mediatoolkit-webp-description"
			<?php checked( $is_checked ); ?>
			<?php disabled( ! $is_supported ); ?>
		/>
		<span class="switch-track"><span class="switch-thumb"></span></span>
	</label>

	<p class="description" id="mediatoolkit-webp-description">
		<?php if ( $is_supported ) : ?>
			<?php esc_html_e( 'Convert new JPEG & PNG uploads to WebP. WebP images are usually 25-35% smaller at the same quality. Existing images are not converted.', 'media-toolkit' ); ?>
		<?php else : ?>
			<?php esc_html_e( 'Your server doesn\'t support WebP. Ask your host to enable WebP support for Imagick or GD.', 'media-toolkit' ); ?>
		<?php endif; ?>
	</p>

	<?php
};
