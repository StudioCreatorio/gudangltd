<?php
/**
 * Automatic.css Generate class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework\Platforms;

use Automatic_CSS\CSS_Engine\CSS_File;
use Automatic_CSS\Framework\Base;
use Automatic_CSS\Helpers\Logger;

/**
 * Automatic.css Generate class.
 */
class Generate extends Base implements Platform {

	/**
	 * Instance of the CSS file
	 *
	 * @var CSS_File
	 */
	private $css_file;

	/**
	 * Is GeneratePress active?
	 *
	 * @var boolean
	 */
	private $is_generatepress_active = false;

	/**
	 * Is GenerateBlocks active?
	 *
	 * @var boolean
	 */
	private $is_generateblocks_active = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->is_generatepress_active = self::is_generatepress_active();
		$this->is_generateblocks_active = self::is_generateblocks_active();
		$deps = array();
		if ( $this->is_generatepress_active ) {
			$deps[] = 'generate-style';
		}
		if ( $this->is_generateblocks_active ) {
			$deps[] = 'generateblocks';
		}
		$this->css_file = $this->add_css_file(
			new CSS_File(
				'automaticcss-generate',
				'automatic-generate.css',
				array(
					'source_file' => 'platforms/generate/automatic-generate.scss',
					'imports_folder' => 'platforms/generate',
				),
				array(
					'deps' => apply_filters( 'automaticcss_generate_deps', $deps ),
				)
			)
		);
		if ( is_admin() ) {
			add_action( 'current_screen', array( $this, 'inject_stylesheets_in_block_editor' ) );
			// Inform the SCSS compiler that we're using the platform.
			add_filter( 'automaticcss_framework_variables', array( $this, 'inject_scss_enabler_option' ) );
			add_filter( 'acss/gutenberg/allowed_post_types', array( $this, 'enable_loading_in_generatepress_post_types' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_resets' ), 11 );
		}
	}

	/**
	 * Inject an SCSS variable in the CSS generation process to enable this module.
	 *
	 * @param array $variables The values for the framework's variables.
	 * @return array
	 */
	public function inject_scss_enabler_option( $variables ) {
		if ( $this->is_generatepress_active ) {
			$variables['option-generate-press'] = 'on';
		}
		if ( $this->is_generateblocks_active ) {
			$variables['option-generate-blocks'] = 'on';
		}
		return $variables;
	}

	/**
	 * Enqueue the CSS file.
	 */
	public function enqueue_resets() {
		$this->css_file->enqueue_stylesheet();
	}

	/**
	 * Inject the core stylesheets in the block editor.
	 *
	 * @return void
	 */
	public function inject_stylesheets_in_block_editor() {
		$current_screen = \get_current_screen();
		$post_type = $current_screen->post_type;
		$allowed_post_types = apply_filters( 'acss/gutenberg/allowed_post_types', array( 'page' ) );
		if ( ! in_array( $post_type, $allowed_post_types, true ) ) {
			Logger::log( sprintf( '%s: injecting core stylesheets on post_type %s is not allowed', __METHOD__, $post_type ) );
			return;
		}
		if ( null !== $current_screen && method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
			Logger::log( sprintf( '%s: injecting Generate stylesheets in Gutenberg block editor.', __METHOD__ ) );
			add_theme_support( 'editor-styles' ); // supposed to be not necessary, but it is when not using a FSE theme.
			add_editor_style( $this->css_file->file_url );
		}
	}

	/**
	 * Allow the Gutenberg platform to load our stylesheets in the GeneratePress post types.
	 *
	 * @param array $post_types The post types where Gutenberg is allowed.
	 * @return array
	 */
	public function enable_loading_in_generatepress_post_types( $post_types ) {
		$gp_post_types = array( 'gp_elements', 'gblocks_templates', 'gblocks_global_style' );
		return array_merge( $post_types, $gp_post_types );
	}

	/**
	 * Check if the plugin is installed and activated.
	 *
	 * @return boolean
	 */
	public static function is_active() {
		$is_generatepress_active = self::is_generatepress_active();
		$is_generateblocks_active = self::is_generateblocks_active();
		return $is_generatepress_active || $is_generateblocks_active;
	}

	/**
	 * Check if GeneratePress is active.
	 *
	 * @return boolean
	 */
	public static function is_generatepress_active() {
		$theme = wp_get_theme(); // gets the current theme.
		return 'GeneratePress' === $theme->name || 'GeneratePress' === $theme->parent_theme;
	}

	/**
	 * Check if GenerateBlocks is active.
	 *
	 * @return boolean
	 */
	public static function is_generateblocks_active() {
		return is_plugin_active( 'generateblocks/plugin.php' ) || is_plugin_active( 'generateblocks-pro/plugin.php' ) || is_plugin_active( 'generateblocks-release-1.8.0/plugin.php' ); // TODO: remove the 1.8.0 one.
	}
}
