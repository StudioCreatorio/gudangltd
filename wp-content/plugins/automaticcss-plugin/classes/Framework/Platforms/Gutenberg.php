<?php
/**
 * Automatic.css Gutenberg class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework\Platforms;

use Automatic_CSS\CSS_Engine\CSS_File;
use Automatic_CSS\Framework\Base;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Model\Database_Settings;
use Automatic_CSS\Traits\Disableable;

/**
 * Automatic.css Gutenberg class.
 */
class Gutenberg extends Base implements Platform {

	/**
	 * Allow the Gutenberg module to be disabled while running.
	 */
	use Disableable;

	/**
	 * Instance of the overrides CSS file
	 *
	 * @var CSS_File
	 */
	private $overrides_css_file;

	/**
	 * Instance of the editor CSS file
	 *
	 * @var CSS_File
	 */
	private $editor_css_file;

	/**
	 * Instance of the color palette CSS file
	 *
	 * @var CSS_File
	 */
	private $color_palette_css_file;

	/**
	 * Stores the root font size.
	 *
	 * @var string
	 */
	private $root_font_size;

	/**
	 * Stores whether the styling for the backend is enabled or not.
	 *
	 * @var boolean
	 */
	private $is_load_styling_backend_enabled;

	/**
	 * Stores whether the color palette is enabled or not.
	 *
	 * @var boolean
	 */
	private $is_generate_color_palette_enabled;

	/**
	 * Stores whether the other colors in the palette should be replaced or not.
	 *
	 * @var boolean
	 */
	private $is_replace_color_palette_enabled;

	/**
	 * Constructor
	 *
	 * @param boolean $is_enabled Is the Gutenberg module enabled or not.
	 */
	public function __construct( $is_enabled ) {
		$this->set_enabled( $is_enabled );
		// Grab the settings.
		$database_settings = Database_Settings::get_instance();
		$this->root_font_size = $database_settings->get_var( 'root-font-size' );
		$this->is_load_styling_backend_enabled = $is_enabled && $database_settings->get_var( 'option-gutenberg-load-styling-backend' ) === 'on' ? true : false;
		$this->is_generate_color_palette_enabled = $is_enabled && $database_settings->get_var( 'option-gutenberg-color-palette-generate' ) === 'on' ? true : false;
		$this->is_replace_color_palette_enabled = $is_enabled && $database_settings->get_var( 'option-gutenberg-color-palette-replace' ) === 'on' ? true : false;
		// Add the CSS files.
		$this->overrides_css_file = $this->add_css_file(
			new CSS_File(
				'automaticcss-gutenberg',
				'automatic-gutenberg.css',
				array(
					'source_file' => 'platforms/gutenberg/automatic-gutenberg.scss',
					'imports_folder' => 'platforms/gutenberg'
				),
				array(
					'deps' => array( 'automaticcss-core' )
				)
			)
		);
		$this->overrides_css_file->set_enabled( $is_enabled );
		$this->editor_css_file = $this->add_css_file(
			new CSS_File(
				'automaticcss-core-for-block-editor',
				'automatic-core-for-block-editor.css',
				array(
					'source_file' => 'platforms/gutenberg/automatic-core-for-block-editor.scss',
					'imports_folder' => 'platforms/gutenberg'
				)
			)
		);
		$this->editor_css_file->set_enabled( $this->is_load_styling_backend_enabled );
		$this->color_palette_css_file = $this->add_css_file(
			new CSS_File(
				'automaticcss-gutenberg-color-palette',
				'automatic-gutenberg-color-palette.css',
				array(
					'source_file' => 'platforms/gutenberg/automatic-gutenberg-color-palette.scss',
					'imports_folder' => 'platforms/gutenberg'
				)
			)
		);
		$this->color_palette_css_file->set_enabled( $this->is_generate_color_palette_enabled );
		// Add the hooks.
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'after_setup_theme', array( $this, 'add_color_palette' ), 11 );
		add_action( 'automaticcss_in_builder_context', array( $this, 'dequeue_block_editor_assets' ) );
		if ( is_admin() ) {
			add_action( 'current_screen', array( $this, 'inject_core_stylesheets_in_block_editor' ) );
			// Update the module's status before generating the framework's CSS.
			add_action( 'automaticcss_before_generate_framework_css', array( $this, 'update_status' ) );
		}
	}

	/**
	 * Update the enabled / disabled status of the Gutenberg module
	 *
	 * @param array $variables The values for the framework's variables.
	 * @return void
	 */
	public function update_status( $variables ) {
		// Main enable / disable.
		$is_enabled = isset( $variables['option-gutenberg-enable'] ) && 'on' === $variables['option-gutenberg-enable'] ? true : false;
		Logger::log( sprintf( '%s: setting the Gutenberg module to %s', __METHOD__, $is_enabled ? 'on' : 'off' ) );
		$this->set_enabled( $is_enabled );
		$this->overrides_css_file->set_enabled( $is_enabled );
		// Backend stylesheet enable / disable.
		$this->is_load_styling_backend_enabled = $is_enabled && isset( $variables['option-gutenberg-load-styling-backend'] ) && 'on' === $variables['option-gutenberg-load-styling-backend'] ? true : false;
		$this->editor_css_file->set_enabled( $this->is_load_styling_backend_enabled );
		// Color palette enable / disable.
		$this->is_generate_color_palette_enabled = $is_enabled && isset( $variables['option-gutenberg-color-palette-generate'] ) && 'on' === $variables['option-gutenberg-color-palette-generate'] ? true : false;
		$this->color_palette_css_file->set_enabled( $this->is_generate_color_palette_enabled );
	}

	/**
	 * Enqueue assets to load both on frontend and editor view.
	 *
	 * @return void
	 */
	public function enqueue_block_assets() {
		if ( ! $this->is_enabled() || ! $this->is_load_styling_backend_enabled ) {
			return;
		}
		if ( ! is_admin() ) {
			Logger::log( sprintf( '%s: enqueueing Gutenberg block assets', __METHOD__ ) );
			$this->overrides_css_file->enqueue_stylesheet();
			$this->color_palette_css_file->enqueue_stylesheet();
		}
	}

	/**
	 * Enqueue assets to load only in editor view.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		if ( ! $this->is_enabled() || ! $this->is_load_styling_backend_enabled ) {
			return;
		}
		$post_type = get_post_type();
		$allowed_post_types = apply_filters( 'acss/gutenberg/allowed_post_types', array( 'page' ) );
		if ( ! in_array( $post_type, $allowed_post_types, true ) ) {
			Logger::log( sprintf( '%s: enqueuing Gutenberg blocks on post_type %s is not allowed', __METHOD__, $post_type ) );
			return;
		}
		Logger::log( sprintf( '%s: enqueueing Gutenberg block editor assets', __METHOD__ ) );
		do_action( 'automaticcss_in_builder_context' );
		do_action( 'acss/gutenberg/in_builder_context' );
		// Enqueue the root font size fixer.
		$filename = 'fix-block-editor-rfs';
		$fix_rfs_path = "/Platforms/Gutenberg/js/{$filename}.js";
		wp_enqueue_script(
			"acss-{$filename}",
			ACSS_FRAMEWORK_URL . $fix_rfs_path,
			array(), // wp-blocks works when not using FSE.
			filemtime( ACSS_FRAMEWORK_DIR . $fix_rfs_path ),
			true
		);
		wp_localize_script(
			"acss-{$filename}",
			'automatic_css_block_editor_options',
			array(
				'root_font_size' => $this->root_font_size
			)
		);
		// Enqueue the Metabox WYSIWYG fixer.
		$filename_metabox = 'fix-metabox-wysiwyg';
		$fix_metabox_path = "/Platforms/Gutenberg/js/{$filename_metabox}.js";
		wp_enqueue_script(
			"acss-{$filename_metabox}",
			ACSS_FRAMEWORK_URL . $fix_metabox_path,
			array(), // wp-blocks works when not using FSE.
			filemtime( ACSS_FRAMEWORK_DIR . $fix_metabox_path ),
			true
		);
		wp_localize_script(
			"acss-{$filename_metabox}",
			'automatic_css_block_editor_options',
			array(
				'root_font_size' => $this->root_font_size
			)
		);
	}

	/**
	 * Inject the core stylesheets in the block editor.
	 *
	 * @return void
	 */
	public function inject_core_stylesheets_in_block_editor() {
		if ( ! $this->is_enabled() ) {
			return;
		}
		$current_screen = \get_current_screen();
		$post_type = $current_screen->post_type;
		$allowed_post_types = apply_filters( 'acss/gutenberg/allowed_post_types', array( 'page' ) );
		if ( ! in_array( $post_type, $allowed_post_types, true ) ) {
			Logger::log( sprintf( '%s: injecting core stylesheets on post_type %s is not allowed', __METHOD__, $post_type ) );
			return;
		}
		if ( null !== $current_screen && method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
			Logger::log( sprintf( '%s: injecting core stylesheets in Gutenberg block editor.', __METHOD__ ) );
			add_theme_support( 'editor-styles' ); // supposed to be not necessary, but it is when not using a FSE theme.
			add_editor_style( $this->editor_css_file->file_url );
			add_editor_style( $this->overrides_css_file->file_url );
			if ( ! in_array( 'wp-reset-editor-styles', wp_styles()->done, true ) ) {
				wp_styles()->done[] = 'wp-reset-editor-styles';
			}
			// $this->color_palette_css_file is not needed because WP inlines those styles in the editor.
		}
	}

	/**
	 * Dequeue assets to load only in editor view.
	 *
	 * @return void
	 */
	public function dequeue_block_editor_assets() {
		$this->overrides_css_file->dequeue_stylesheet();
		$this->color_palette_css_file->dequeue_stylesheet();
	}

	/**
	 * Add the color palette to the block editor.
	 *
	 * @return void
	 */
	public function add_color_palette() {
		if ( ! $this->is_enabled() || ! $this->is_generate_color_palette_enabled ) {
			return;
		}
		// STEP: get all colors and add them to the new palette.
		$acss_db = Database_Settings::get_instance();
		$acss_color_palettes = Database_Settings::get_color_palettes(
			array(
				'contextual_colors' => 'on' === $acss_db->get_var( 'option-contextual-colors' ),
				'deprecated_colors' => 'on' === $acss_db->get_var( 'option-deprecated' ),
			)
		);
		$gb_color_palette = array();
		foreach ( $acss_color_palettes as $acss_palette_id => $acss_palette_options ) {
			$acss_palette_colors = array_key_exists( 'colors', $acss_palette_options ) ? $acss_palette_options['colors'] : array();
			foreach ( $acss_palette_colors as $acss_color_name => $acss_color_value ) {
				$gb_color_palette[] = array(
					'name' => $acss_color_name,
					'slug' => $acss_color_name,
					'color' => $acss_color_value, // this is already a valid CSS color.
				);
			}
		}
		// STEP: merge in the current color palette, if the option to replace is not enabled.
		if ( ! $this->is_replace_color_palette_enabled ) {
			// Try to get the current theme default color palette.
			$gb_current_color_palette = current( (array) get_theme_support( 'editor-color-palette' ) );
			if ( false === $gb_current_color_palette && class_exists( 'WP_Theme_JSON_Resolver' ) ) {
				$settings = \WP_Theme_JSON_Resolver::get_core_data()->get_settings();
				if ( isset( $settings['color']['palette']['default'] ) ) {
					$gb_current_color_palette = $settings['color']['palette']['default'];
				}
			}
			if ( ! empty( $gb_current_color_palette ) ) {
				$gb_color_palette = array_merge( $gb_current_color_palette, $gb_color_palette );
			}
		}
		// STEP: save the color palette.
		add_theme_support( 'editor-color-palette', $gb_color_palette );
	}

	/**
	 * Check if the plugin is installed and activated.
	 *
	 * @return boolean
	 */
	public static function is_active() {
		return true;
	}

}
