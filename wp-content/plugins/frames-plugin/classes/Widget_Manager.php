<?php
/**
 * Frames Widget Manager class file.
 *
 * @package Frames_Client
 */

namespace Frames_Client;

use Frames_Client\Helpers\Logger;
use Frames_Client\Traits\Singleton;

/**
 * Widget Manager class.
 */
final class Widget_Manager {

	use Singleton;

	/**
	 * Array of widgets.
	 *
	 * @var array
	 */
	private $widgets = array(
		/**
		 * 'widget-name' => array(
		 *   'option' => 'option-name',
		 *   'label' => 'Widget Label'
		 * )
		 */
		'table-of-contents' => array(
			'option' => 'option-frames-toc-widget',
			'label' => 'Table of Contents (RC)'
		),
		'modal'             => array(
			'option' => 'option-frames-modal-widget',
			'label' => 'Modal (RC)'
		),
		'trigger'           => array(
			'option' => 'option-frames-trigger-widget',
			'label' => 'Trigger (RC)'
		),
		'slider'            => array(
			'option' => 'option-frames-slider-widget',
			'label' => 'Slider (Beta)'
		),
		'slider-controls'   => array(
			'option' => 'option-frames-slider-controls-widget',
			'label' => 'Slider Controls (Beta)'
		),
		'accordion'         => array(
			'option' => 'option-frames-accordion-widget',
			'label' => 'Accordion (Beta)'
		),
		'tabs'              => array(
			'option' => 'option-frames-tabs-widget',
			'label' => 'Tabs (Beta)'
		),
		'switch'            => array(
			'option' => 'option-frames-switch-widget',
			'label' => 'Switch (Beta)'
		)
	);

	/**
	 * Initialize the Widgets.
	 *
	 * @return Widget_Manager
	 */
	public function init() {
		add_action( 'init', array( $this, 'init_widgets' ), 11 );
		add_filter( 'acss/config/variables.json', array( $this, 'inject_acss_variables' ) );
		add_filter( 'acss/config/ui.json', array( $this, 'inject_acss_ui_elements' ) );
		return $this;
	}

	/**
	 * Inject a toggle variable to enable / disable each widget into the ACSS dashboard.
	 *
	 * @param array $config The config options.
	 * @return array
	 */
	public function inject_acss_variables( $config ) {
		$var_config = &$config['variables'];
		foreach ( $this->widgets as $widget_name => $widget_options ) {
			$option = $widget_options['option'];
			if ( empty( $var_config[ $option ] ) ) {
				$var_config[ $option ] = array();
			}
			$new_settings = array(
				'type' => 'toggle',
				'valueon' => 'on',
				'valueoff' => 'off',
				'default' => 'off'
			);
			$var_config[ $option ] = array_merge( $var_config[ $option ], $new_settings );
		}
		return $config;
	}

	/**
	 * Inject a UI toggle for each widget into the ACSS dashboard.
	 *
	 * @param array $config The config options.
	 * @return array
	 */
	public function inject_acss_ui_elements( $config ) {
		$frames_config = &$config['tabs']['frames']['content']['frames-components']['content'];
		foreach ( $this->widgets as $widget_name => $widget_options ) {
			$option = $widget_options['option'];
			if ( empty( $frames_config[ $option ] ) ) {
				$frames_config[ $option ] = array();
			}
			$component_name = ucwords( str_replace( '-', ' ', $widget_name ) );
			$label = $widget_options['label'];
			$new_settings = array(
				'type' => 'variable',
				'title' => $label,
				'tooltip' => "Load the Frames {$component_name} Widget."
			);
			$frames_config[ $option ] = array_merge( $frames_config[ $option ], $new_settings );
		}
		return $config;
	}

	/**
	 * Initialize the Widgets.
	 *
	 * @return void
	 */
	public function init_widgets() {
		Logger::log( sprintf( '%s: Initializing widgets', __METHOD__ ) );
		if ( empty( $this->widgets ) || ! is_array( $this->widgets ) || ! class_exists( '\Bricks\Elements' ) ) {
			Logger::log( sprintf( '%s: No widgets to initialize', __METHOD__ ) );
			return;
		}
		if ( ! class_exists( '\Automatic_CSS\Model\Database_Settings' ) ) {
			Logger::log( sprintf( '%s: Automatic CSS database class not found', __METHOD__ ) );
			return;
		}
		$acss_database = \Automatic_CSS\Model\Database_Settings::get_instance();
		foreach ( $this->widgets as $widget_name => $widget_options ) {
			$option = $widget_options['option'];
			$setting = $acss_database->get_var( $option );
			if ( null !== $setting && 'on' === $setting ) {
				$element_file = FRAMES_WIDGETS_DIR . "/{$widget_name}/{$widget_name}.php";
				if ( file_exists( $element_file ) ) {
					Logger::log( sprintf( '%s: Registering element %s', __METHOD__, $widget_name ) );
					\Bricks\Elements::register_element( $element_file );
				} else {
					Logger::log( sprintf( '%s: Element file %s does not exist', __METHOD__, $element_file ) );
				}
			}
		}
	}

	/**
	 * Helper functions to control the output of the widget.
	 *
	 * @param array  $settings The settings array.
	 * @param string $control The control name.
	 * @param string $outputTrue The output if the control is true.
	 * @param string $outputFalse The output if the control is false.
	 * @since 1.2.0
	 */
	public static function ifControlIsTrue( $settings, $control, $outputTrue, $outputFalse ) { // phpcs:ignore
		if ( isset( $settings[ str_replace( ' ', '', $control ) ] ) && true === $settings[ str_replace( ' ', '', $control ) ] ) {
			echo $outputTrue; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo $outputFalse; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	public static function ifControlIsEmpty( $settings, $control, $outputTrue, $outputFalse ) { // phpcs:ignore
		if ( isset( $settings[ str_replace( ' ', '', $control ) ] ) && '' === $settings[ str_replace( ' ', '', $control ) ] ) {
			echo $outputTrue; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo $outputFalse; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param array  $settings The settings array.
	 * @param string $control The control name.
	 * @return string
	 * @since 1.2.0
	 */
	public static function control( $settings, $control ) {
		return isset( $settings[ str_replace( ' ', '', $control ) ] ) ? wp_kses_post( $settings[ str_replace( ' ', '', $control ) ] ) : '';
	}

	/**
	 * Takes a string of HTML attributes and merges them with an array of attributes.
	 *
	 * @param string $render_attributes The root attributes.
	 * @param array  $new_attributes The new attributes.
	 * @return string
	 */
	public static function render_attributes( $render_attributes, $new_attributes = array() ) {
		// Logger::log( sprintf( '%s: attributes=%s', __METHOD__, print_r( $render_attributes, true ) ) ); // TODO: remove.
		$attributes = array();
		// STEP: parse the attributes in an array.
		preg_match_all( '/([\w-]+)\s*=\s*"([^"]*)"/', $render_attributes, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {
			$attributes[ $match[1] ] = explode( ' ', $match[2] );
		}
		// STEP: merge the new attributes.
		$attributes = array_merge_recursive( $attributes, $new_attributes );
		// Logger::log( sprintf( '%s: attributes=%s', __METHOD__, print_r( $attributes, true ) ) ); // TODO: remove.
		// STEP: build the new attributes string.
		$render_attributes = '';
		foreach ( $attributes as $attribute_key => $attribute_value ) {
			if ( is_array( $attribute_value ) ) {
				$attribute_value = implode( ' ', array_unique( $attribute_value ) );
			}
			$render_attributes .= sprintf( ' %s="%s"', esc_attr( $attribute_key ), esc_attr( $attribute_value ) );
		}
		// Logger::log( sprintf( '%s: render_attributes=%s', __METHOD__, $render_attributes ) ); // TODO: remove.
		return $render_attributes;
	}

	/**
	 * Check if we're loading the frontend or the builder iframe.
	 *
	 * @return boolean
	 */
	public static function is_bricks_frontend() {
		if ( ! function_exists( 'bricks_is_frontend' ) || ! function_exists( 'bricks_is_builder_iframe' ) ) {
			Logger::log( sprintf( '%s: Bricks functions not found', __METHOD__ ) );
			return false;
		}
		return bricks_is_frontend() || bricks_is_builder_iframe();
	}

}
