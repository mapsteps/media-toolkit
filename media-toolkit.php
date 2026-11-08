<?php
/**
 * Plugin Name: Media Toolkit
 * Plugin URI: https://mapsteps.com
 * Description: Bulk-optimize your images, convert uploads to WebP, set a max image resolution and change your image filenames.
 * Version: 2.0
 * Requires at least: 5.3
 * Requires PHP: 5.6
 * Author: David Vongries
 * Author URI: https://mapsteps.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: media-toolkit
 *
 * @package MediaToolkit
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

// Helper constants.
define( 'MEDIA_TOOLKIT_PLUGIN_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'MEDIA_TOOLKIT_PLUGIN_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );
define( 'MEDIA_TOOLKIT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'MEDIA_TOOLKIT_PLUGIN_VERSION', '2.0' );

require __DIR__ . '/MediaToolkitSetup.php';
require __DIR__ . '/MediaToolkitOutput.php';
require __DIR__ . '/MediaToolkitBulk.php';
require __DIR__ . '/functions.php';

\MediaToolkit\setup();
\MediaToolkit\output();
