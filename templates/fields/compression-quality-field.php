<?php
/**
 * Compression quality field.
 *
 * @package MediaToolkit
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Output "Quality" field.
 *
 * @param array $settings The setting values.
 */
return function ( $settings ) {

	$quality = isset( $settings['compression_quality'] ) ? $settings['compression_quality'] : '';
	?>

	<input
		type="number"
		min="1"
		max="100"
		step="1"
		id="mediatoolkit_settings--compression-quality"
		name="mediatoolkit_settings[compression_quality]"
		class="small-text"
		value="<?php echo esc_attr( $quality ); ?>"
		placeholder="82"
		aria-describedby="mediatoolkit-quality-description"
	/> %

	<p class="description" id="mediatoolkit-quality-description">
		<?php esc_html_e( 'The quality of generated images, from 1 to 100. Leave empty to use the WordPress default (82). Raising the quality doesn\'t improve existing images, it only makes new files bigger.', 'media-toolkit' ); ?>
	</p>

	<?php
};
