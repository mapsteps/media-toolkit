<?php
/**
 * Plugin Name: Media Toolkit
 * Plugin URI: https://mediatoolkit.com/
 * Description: Toolkit for media in WordPress.
 * Version: 1.0
 * Author: David Vongries
 * Author URI: https://davidvongries.com/
 * Text Domain: media-toolkit
 *
 * @package MediaToolkit
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

// Helper constants.
define( 'MEDIA_TOOLKIT_PLUGIN_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'MEDIA_TOOLKIT_PLUGIN_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );
define( 'MEDIA_TOOLKIT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'MEDIA_TOOLKIT_PLUGIN_VERSION', '1.0' );

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions.php';

\MediaToolkit\setup();
\MediaToolkit\output();
