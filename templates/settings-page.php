<?php
/**
 * Settings page template.
 *
 * @package MediaToolkit
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

?>

<div class="wrap heatbox-wrap mediatk-settings-page">

	<div class="heatbox-header heatbox-margin-bottom">

		<div class="heatbox-container heatbox-container-center">

			<div class="logo-container">

				<div>
					<span class="title">
						<?php echo esc_html( get_admin_page_title() ); ?>
						<span class="version"><?php echo esc_html( MEDIA_TOOLKIT_PLUGIN_VERSION ); ?></span>
					</span>
					<p class="subtitle"><?php esc_html_e( 'A utility plugin for the WordPress media library.', 'media-toolkit' ); ?></p>
				</div>

				<div>
					<img src="<?php echo esc_url( MEDIA_TOOLKIT_PLUGIN_URL ); ?>/assets/images/logo.png" alt="">
				</div>

			</div>

		</div>

	</div>

	<div class="heatbox-container heatbox-container-center heatbox-column-container">

		<div class="heatbox-main">

			<!-- WordPress places admin notices after this marker -->
			<hr class="wp-header-end">

			<?php settings_errors(); ?>

			<form method="post" action="options.php" class="mediatoolkit-settings-form">

				<?php settings_fields( 'mediatoolkit-settings-group' ); ?>

				<div class="heatbox mediatk-settings-metabox">
					<?php do_settings_sections( 'mediatoolkit-rename-settings' ); ?>
				</div>

				<div class="heatbox mediatk-settings-metabox">
					<?php do_settings_sections( 'mediatoolkit-quality-settings' ); ?>
				</div>

				<div class="heatbox mediatk-settings-metabox max-size-settings">
					<?php do_settings_sections( 'mediatoolkit-replace-original-settings' ); ?>
				</div>

				<?php submit_button( '', 'button button-primary button-larger', 'submit', false ); ?>

			</form>

		</div>

		<div class="heatbox-sidebar">

			<?php
			require __DIR__ . '/metaboxes/template-tags.php';
			require __DIR__ . '/metaboxes/review.php';
			?>

		</div>

		<div class="heatbox-divider"></div>

	</div>

	<div class="heatbox-container heatbox-container-wide heatbox-container-center featured-products">

		<h2><?php esc_html_e( 'Check out our other free WordPress products!', 'media-toolkit' ); ?></h2>

		<ul class="products">
			<li class="heatbox">
				<a href="https://wordpress.org/plugins/ultimate-dashboard/" target="_blank">
					<img src="<?php echo esc_url( MEDIA_TOOLKIT_PLUGIN_URL ); ?>/assets/images/ultimate-dashboard.jpg" alt="">
				</a>
				<div class="heatbox-content">
					<h3><?php esc_html_e( 'Ultimate Dashboard', 'media-toolkit' ); ?></h3>
					<p class="subheadline"><?php esc_html_e( 'Fully customize your WordPress dashboard.', 'media-toolkit' ); ?></p>
					<p><?php esc_html_e( 'Ultimate Dashboard is the #1 plugin to create a custom WordPress dashboard for you and your clients. It also comes with multisite support which makes it the perfect plugin for your WaaS network.', 'media-toolkit' ); ?></p>
					<a href="https://wordpress.org/plugins/ultimate-dashboard/" target="_blank" class="button"><?php esc_html_e( 'Learn more', 'media-toolkit' ); ?></a>
				</div>
			</li>
			<li class="heatbox">
				<a href="https://wordpress.org/themes/page-builder-framework/" target="_blank">
					<img src="<?php echo esc_url( MEDIA_TOOLKIT_PLUGIN_URL ); ?>/assets/images/page-builder-framework.jpg" alt="">
				</a>
				<div class="heatbox-content">
					<h3><?php esc_html_e( 'Page Builder Framework', 'media-toolkit' ); ?></h3>
					<p class="subheadline"><?php esc_html_e( 'The only theme you\'ll ever need.', 'media-toolkit' ); ?></p>
					<p class="description"><?php esc_html_e( 'With its minimalistic design the Page Builder Framework theme is the perfect foundation for your next project. Build blazing fast websites with a theme that is easy to use, lightweight & highly customizable.', 'media-toolkit' ); ?></p>
					<a href="https://wordpress.org/themes/page-builder-framework/" target="_blank" class="button"><?php esc_html_e( 'Learn more', 'media-toolkit' ); ?></a>
				</div>
			</li>
			<li class="heatbox">
				<a href="https://wordpress.org/plugins/better-admin-bar/" target="_blank">
					<img src="<?php echo esc_url( MEDIA_TOOLKIT_PLUGIN_URL ); ?>/assets/images/better-admin-bar.jpg" alt="">
				</a>
				<div class="heatbox-content">
					<h3><?php esc_html_e( 'Better Admin Bar', 'media-toolkit' ); ?></h3>
					<p class="subheadline"><?php esc_html_e( 'Replace the boring WordPress admin bar!', 'media-toolkit' ); ?></p>
					<p><?php esc_html_e( 'Better Admin Bar is the plugin that make your clients love WordPress. It drastically improves the user experience when working with WordPress and allows you to replace the boring WordPress admin bar with your own navigation panel.', 'media-toolkit' ); ?></p>
					<a href="https://wordpress.org/plugins/better-admin-bar/" target="_blank" class="button"><?php esc_html_e( 'Learn more', 'media-toolkit' ); ?></a>
				</div>
			</li>
		</ul>

		<p class="credit"><?php esc_html_e( 'Made with ❤ in Torsby, Sweden', 'media-toolkit' ); ?></p>

	</div>

</div>
