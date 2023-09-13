<?php
/**
 * Automatic.css Oxygen class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework\Platforms;

use Automatic_CSS\CSS_Engine\CSS_File;
use Automatic_CSS\Framework\Base;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Model\Config\Classes;

/**
 * Automatic.css Oxygen class.
 */
class Oxygen extends Base implements Platform {

	/**
	 * Instance of the CSS file
	 *
	 * @var CSS_File
	 */
	private $css_file;

	/**
	 * Are we in the Oxygen builder?
	 * I.e. is $_GET['ct_builder'] true but $_GET['oxygen_iframe'] false?
	 *
	 * @var bool
	 */
	private $is_builder;

	/**
	 * Are we in the Oxygen iframe?
	 * I.e. is $_GET['oxygen_iframe'] true?
	 *
	 * @var bool
	 */
	private $is_iframe;

	/**
	 * Constructor
	 *
	 * @param array $database_settings The database settings.
	 */
	public function __construct( $database_settings ) {
		// oxygen-universal-styles is enqueued in the frontend, but NOT in the iframe's context, and only when Oxygen cache is enabled.
		// So we have to change our dependencies.
		$deps = $this->oxygen_cache_enabled() && ! $this->in_iframe_context() ? array( 'oxygen-universal-styles' ) : array();
		$this->css_file = $this->add_css_file(
			new CSS_File(
				'automaticcss-oxygen',
				'automatic-oxygen.css',
				array(
					'source_file' => 'platforms/oxygen/automatic-oxygen.scss',
					'imports_folder' => 'platforms/oxygen'
				),
				array(
					'deps' => apply_filters( 'automaticcss_oxygen_deps', $deps )
				)
			)
		);
		if ( is_admin() ) {
			add_action( 'automaticcss_activate_plugin_end', array( $this, 'update_selectors' ) );
			add_action( 'automaticcss_update_plugin_end', array( $this, 'update_selectors' ) );
			add_action( 'automaticcss_delete_plugin_data_end', array( $this, 'delete_selectors' ) );
			add_filter( 'automaticcss_tab_viewport_warnings', array( $this, 'add_oxygen_viewport_warning' ) );
			add_filter( 'automaticcss_tab_typography_warnings', array( $this, 'add_oxygen_typography_warning' ) );
			add_filter( 'automaticcss_framework_variables', array( $this, 'inject_scss_enabler_option' ) ); // no need for now?
		} else {
			// Oxygen enqueues universal.css and page / template CSS in a separate \WP_Styles queue using 'wp_head' priority 999999.
			add_action( 'wp_head', array( $this, 'enqueue_oxygen_resets' ), 1000000 );
			// Allow for Core stylesheet to be removed in the builder context.
			add_action( 'wp_enqueue_scripts', array( $this, 'remove_framework_stylesheet_from_builder' ), 20 );
		}
	}

	/**
	 * Enqueue the Oxygen reset stylesheet. Skip in the builder context.
	 *
	 * @return void
	 */
	public function enqueue_oxygen_resets() {
		if ( $this->in_builder_context() ) {
			return;
		}
		global $oxygen_vsb_css_styles;
		if ( is_a( $oxygen_vsb_css_styles, '\WP_Styles' ) ) {
			// We're probably in the frontend, where $oxygen_vsb_css_styles is set.
			// We enqueue there.
			$this->css_file->set_queue( $oxygen_vsb_css_styles );
			$this->css_file->enqueue_stylesheet();
			$this->css_file->process_stylesheets();
		} else {
			// We're probably in the builder's iframe, where $oxygen_vsb_css_styles is NOT SET for some damn reason.
			// We enqueue in the regular queue (and don't need to ask to process stylesheets).
			$this->css_file->enqueue_stylesheet();
		}
	}

	/**
	 * Remove ACSS scripts & styles if we the builder context.
	 *
	 * @return void
	 */
	public function remove_framework_stylesheet_from_builder() {
		if ( ! $this->in_builder_context() ) {
			return;
		}
		Logger::log( sprintf( '%s: builder context detected, triggering automaticcss_in_builder_context', __METHOD__ ) );
		do_action( 'automaticcss_in_builder_context' );
		do_action( 'acss/oxygen/in_builder_context' );
	}

	/**
	 * Add the framework's classes to Oxygen for autocomplete.
	 *
	 * @return void
	 */
	public function update_selectors() {
		Logger::log( sprintf( '%s: updating Oxygen\'s selector list', __METHOD__ ) );
		// make sure the 'AutomaticCSS' folder exists.
		$folders = (array) get_option( 'ct_style_folders', array() );
		if ( ! array_key_exists( 'AutomaticCSS', $folders ) ) {
			$folders['AutomaticCSS'] = array(
				'key' => 'AutomaticCSS',
				'status' => 1
			);
			update_option( 'ct_style_folders', $folders );
		}
		// update the classes.
		$classes = ( new Classes() )->load();
		$folder  = 'AutomaticCSS';
		if ( is_array( $classes ) && count( $classes ) > 0 ) {
			$ct_components_classes = (array) get_option( 'ct_components_classes', array() );
			foreach ( $classes as $class ) {
				$values = array(
					'key' => $class,
					'parent' => $folder,
					'original' => array(
						'selector-locked' => 'true'
					)
				);
				if ( array_key_exists( $class, $ct_components_classes ) && is_array( $ct_components_classes[ $class ] ) && ! empty( $ct_components_classes[ $class ] ) ) {
					$values = array_merge( $ct_components_classes[ $class ], $values );
				}
				$ct_components_classes[ $class ] = $values;
			}
			update_option( 'ct_components_classes', $ct_components_classes, get_option( 'oxygen_options_autoload' ) );
		}
		Logger::log( sprintf( '%s: done', __METHOD__ ) );
	}

	/**
	 * Remove the framework's classes to Oxygen for autocomplete.
	 *
	 * @return void
	 */
	public function delete_selectors() {
		Logger::log( sprintf( '%s: deleting Oxygen\'s selector list', __METHOD__ ) );
		$classes = ( new Classes() )->load();
		if ( $classes ) {
			$ct_components_classes = (array) get_option( 'ct_components_classes', array() );
			foreach ( $classes as $class ) {
				if ( ! empty( $ct_components_classes[ $class ] ) ) {
					unset( $ct_components_classes[ $class ] );
				}
			}
			update_option( 'ct_components_classes', $ct_components_classes );
		}
		$folders = (array) get_option( 'ct_style_folders', array() );
		if ( array_key_exists( 'AutomaticCSS', $folders ) ) {
			unset( $folders['AutomaticCSS'] );
			update_option( 'ct_style_folders', $folders );
		}
		Logger::log( sprintf( '%s: done', __METHOD__ ) );
	}

	/**
	 * Inject an SCSS variable in the CSS generation process to enable this module.
	 *
	 * @param array $variables The values for the framework's variables.
	 * @return array
	 */
	public function inject_scss_enabler_option( $variables ) {
		$variables['option-oxygen'] = 'on';
		return $variables;
	}

	/**
	 * Add a warning to the viewport tab if Oxygen is active.
	 *
	 * @param array $warnings The current warnings.
	 * @return array The updated warnings.
	 */
	public function add_oxygen_viewport_warning( $warnings ) {
		$warnings['oxygen_viewport_warning'] = array(
			'text' => 'Oxygen Detected',
			'tooltip' => 'These values need to match the values you chose in Oxygen > Manage > Settings > Global Styles > Widths & Breakpoints'
		);
		return $warnings;
	}

	/**
	 * Add a warning to the typography tab if Oxygen is active.
	 *
	 * @param array $warnings The current warnings.
	 * @return array The updated warnings.
	 */
	public function add_oxygen_typography_warning( $warnings ) {
		$warnings['oxygen_typography_warning'] = array(
			'text' => 'Oxygen Detected',
			'tooltip' => 'You must clear out all default text sizes in Oxygen Global Settings for the ACSS to take effect.'
		);
		return $warnings;
	}

	/**
	 * Check if the plugin is installed and activated.
	 *
	 * @return boolean
	 */
	public static function is_active() {
		// I checked with class_exists( 'CT_Component' ), but it doesn't work here.
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return is_plugin_active( 'oxygen/functions.php' );
	}

	/**
	 * Are we in Oxygen's builder context?
	 * That means we're in the builder, but not in the preview's iframe.
	 *
	 * @return bool
	 */
	public function in_builder_context() {
		if ( ! isset( $this->is_builder ) ) {
			$this->is_builder = (bool) filter_input( INPUT_GET, 'ct_builder' );
		}
		return $this->is_builder && ! $this->in_iframe_context();
	}

	/**
	 * Are we in Oxygen's iframe context?
	 * That means we're in NOT in the builder, just in the preview's iframe.
	 *
	 * @return bool
	 */
	public function in_iframe_context() {
		if ( ! isset( $this->is_iframe ) ) {
			$this->is_iframe = (bool) filter_input( INPUT_GET, 'oxygen_iframe' );
		}
		return $this->is_iframe;
	}

	/**
	 * Is Oxygen's universal cache enabled?
	 *
	 * @return bool
	 */
	public function oxygen_cache_enabled() {
		return (bool) get_option( 'oxygen_vsb_universal_css_cache', false );
	}

}
