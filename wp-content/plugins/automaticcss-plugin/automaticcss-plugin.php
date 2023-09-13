<?php
/**
 * Automatic.css Main file.
 *
 * @package Automatic_CSS
 */

/**
 * Plugin Name:       Automatic.css
 * Plugin URI:        https://automaticcss.com/
 * Description:       The #1 Utility Framework for WordPress Page Builders.
 * Version:           2.6.2
 * Requires at least: 5.9
 * Requires PHP:      7.3
 * Author:            Kevin Geary, Matteo Greco
 * Author URI:        https://automaticcss.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://automaticcss.com/
 * Text Domain:       automatic-css
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Define plugin directories and urls.
 */
$upload_dir = wp_upload_dir();
define( 'ACSS_PLUGIN_FILE', __FILE__ );
define( 'ACSS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ACSS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ACSS_ASSETS_URL', plugin_dir_url( __FILE__ ) . 'assets' );
define( 'ACSS_ASSETS_DIR', plugin_dir_path( __FILE__ ) . 'assets' );
define( 'ACSS_CONFIG_DIR', plugin_dir_path( __FILE__ ) . 'config' );
define( 'ACSS_FEATURES_URL', plugin_dir_url( __FILE__ ) . 'classes/Features' );
define( 'ACSS_FEATURES_DIR', plugin_dir_path( __FILE__ ) . 'classes' . DIRECTORY_SEPARATOR . 'Features' );
define( 'ACSS_FRAMEWORK_URL', plugin_dir_url( __FILE__ ) . 'classes/Framework' );
define( 'ACSS_FRAMEWORK_DIR', plugin_dir_path( __FILE__ ) . 'classes' . DIRECTORY_SEPARATOR . 'Framework' );
define( 'ACSS_DYNAMIC_CSS_DIR', trailingslashit( $upload_dir['basedir'] ) . 'automatic-css' );
define( 'ACSS_DYNAMIC_CSS_URL', trailingslashit( set_url_scheme( $upload_dir['baseurl'] ) ) . 'automatic-css' );

/**
 * Load the plugin.
 */
require_once ACSS_PLUGIN_DIR . '/classes/Autoloader.php';
\Automatic_CSS\Autoloader::register();
\Automatic_CSS\Plugin::get_instance()->init();
