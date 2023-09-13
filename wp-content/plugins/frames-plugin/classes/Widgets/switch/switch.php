<?php
/**
 * Switch Widget.
 *
 * @package Frames_Client
 */

namespace Frames_Client\Widgets\FramesSwitch;

use \Frames_Client\Widget_Manager;

/**
 * Switch class.
 */
class Fr_Switch_Widget extends \Bricks\Element {

	/**
	 * Widget category.
	 *
	 * @var string
	 */
	public $category = 'Frames';

	/**
	 * Widget name.
	 *
	 * @var string
	 */
	public $name = 'fr-switch';

	/**
	 * Widget icon.
	 *
	 * @var string
	 */
	public $icon = 'fas fa-toggle-on';

	/**
	 * Widget scripts.
	 *
	 * @var string
	 */
	public $scripts = array( 'switch_script' );

	/**
	 * Is widget nestable?
	 *
	 * @var string
	 */
	public $nestable = false;

	/**
	 * Get the widget label.
	 *
	 * @return string
	 */
	public function get_label() {
		return esc_html__( 'Frames Switch', 'frames' );
	}

	/**
	 * Enqueue Scripts and Styles for the widget
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	public function enqueue_scripts() {
		$filename = 'switch';
		wp_enqueue_style(
			"frames-{$filename}",
			FRAMES_WIDGETS_URL . "/{$filename}/css/{$filename}.css",
			array(),
			filemtime( FRAMES_WIDGETS_DIR . "/{$filename}/css/{$filename}.css" )
		);
		wp_enqueue_script(
			"frames-{$filename}",
			FRAMES_WIDGETS_URL . "/{$filename}/js/{$filename}.js",
			array(),
			filemtime( FRAMES_WIDGETS_DIR . "/{$filename}/js/{$filename}.js" ),
			true
		);
	}


	/**
	 * Register widget control groups.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	public function set_control_groups() {

		/**
		 *  Here you can add your control groups and assign them to different tabs.
		 *  Check this: https://academy.bricksbuilder.io/article/create-your-own-elements/
		 */

		$this->control_groups['settings'] = array(
			'title' => esc_html__( 'Settings', 'frames' ),
			'tab' => 'content',
		);

		$this->control_groups['labelStyling'] = array(
			'title' => esc_html__( 'Labels Styling', 'frames' ),
			'tab' => 'content',
		);

		$this->control_groups['sliderStyling'] = array(
			'title' => esc_html__( 'Slider Styling', 'frames' ),
			'tab' => 'content',
		);

		$this->control_groups['indicatorStyling'] = array(
			'title' => esc_html__( 'Slider Indicator', 'frames' ),
			'tab' => 'content',
		);

	}

	/**
	 * Register widget controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	public function set_controls() {

		/**
		 *  Here you can add your controls for your widget.
		 *  Check this: https://academy.bricksbuilder.io/topic/controls/
		 */

		 // type info with 'description' set to 'create a block element with 2 children and give it an unique class'.

		$this->controls['switcherContentInfo'] =
			array(
				'type' => 'info',
				'content' => 'Create a block element with 2 children and give it an unique class f.e .fr-switcher',
				'required' => array( 'switcherSelector', '=', '' )
			);

		 $this->controls['switcherSelector'] =
		 array(
			 'label' => __( 'Content Wrapper Selector', 'frames' ),
			 'info' => __( 'Enter the selector of the element that wraps the content you want to switch.', 'frames' ),
			 'type' => 'text',
			 'inlineEditing' => true,
		 );

		 $this->controls['defaultActive'] = array(
			 'group' => 'settings',
			 'label' => esc_html__( 'Activated by default', 'frames' ),
			 'type' => 'checkbox',
			 'default' => false,
		 );

		 $this->controls['firstLabel'] =
			array(
				'group' => 'settings',
				'label' => __( 'First Label', 'frames' ),
				'type' => 'text',
				'default' => 'Option 1',
				'inlineEditing' => true,
			);

		 $this->controls['secondLabel'] =
			array(
				'group' => 'settings',
				'label' => __( 'Second Label', 'frames' ),
				'type' => 'text',
				'default' => 'Option 2',
				'inlineEditing' => true,
			);

		 $this->controls['ariaLabel'] =
			array(
				'group' => 'settings',
				'label' => __( 'Accessible Description', 'frames' ),
				'type' => 'text',
				'default' => 'Descriptive text for screen readers',
				'inlineEditing' => true,
			);

		 $this->controls['labelTypography'] =
			array(
				'group' => 'labelStyling',
				'label' => __( 'Label Typography', 'frames' ),
				'type' => 'typography',
				'css' => array(
					array(
						'property' => 'typography',
						'selector' => '.fr-switch__label',
					),
				),
				'inline' => true,
			);

			// for color.

		 $this->controls['labelColor'] =
			array(
				'group' => 'labelStyling',
				'label' => __( 'Label Color', 'frames' ),
				'type' => 'color',
				'css' => array(
					array(
						'property' => 'color',
						'selector' => '.fr-switch__label',
					),
				),
				'inline' => true,
			);

		 $this->controls['labelPadding'] =
			array(
				'group' => 'labelStyling',
				'label' => __( 'Padding', 'frames' ),
				'type' => 'dimensions',
				'css' => array(
					array(
						'property' => 'padding',
						'selector' => '.fr-switch__label',
					),
				),
				'default' => array(
					'top' => '',
					'right' => 'var(--space-xs)',
					'bottom' => '',
					'left' => 'var(--space-xs)',
				),
			);

		 $this->controls['labelMargin'] =
			array(
				'group' => 'labelStyling',
				'label' => __( 'Margin', 'frames' ),
				'type' => 'dimensions',
				'css' => array(
					array(
						'property' => 'margin',
						'selector' => '.fr-switch__label',
					),
				),
				'default' => array(
					'top' => '',
					'right' => '',
					'bottom' => '',
					'left' => '',
				),
			);

		 // Slider Controls.

		 $this->controls['sliderWidth'] =
			array(
				'group' => 'sliderStyling',
				'label' => __( 'Width', 'frames' ),
				'type' => 'number',
				'min' => 0,
				'max' => 9999,
				'step' => 1,
				'units' => true,
				'inline' => true,
				'default' => '3.4em',
				'css'   => array(
					array(
						'property' => 'width',
						'selector' => '.fr-switch__slider',
					),
				),
			);

		 $this->controls['sliderHeight'] =
			array(
				'group' => 'sliderStyling',
				'label' => __( 'Height', 'frames' ),
				'type' => 'number',
				'min' => 0,
				'max' => 9999,
				'step' => 1,
				'units' => true,
				'inline' => true,
				'default' => '1.9em',
				'css'   => array(
					array(
						'property' => 'height',
						'selector' => '.fr-switch__slider',
					),
				),
			);

		 $this->controls['sliderBackgroundColor'] =
			array(
				'group' => 'sliderStyling',
				'label' => __( 'Background Color', 'frames' ),
				'type' => 'color',
				'default' => array(
					'rgb' => 'var(--primary-ultra-light)',
				),
				'css'   => array(
					array(
						'property' => 'background-color',
						'selector' => '.fr-switch__slider',
					),
				),
			);

		 $this->controls['sliderBorder'] =
			array(
				'group' => 'sliderStyling',
				'label' => __( 'Border', 'frames' ),
				'type' => 'border',
				'default' => '',
				'inlineEditing' => true,
				'css'   => array(
					array(
						'property' => 'border',
						'selector' => '.fr-switch__slider',
					),
				),
			);

			// Slider Indicator Controls.

		 $this->controls['sliderIndicatorHeight'] =
			array(
				'group' => 'indicatorStyling',
				'label' => __( 'Height', 'frames' ),
				'type' => 'number',
				'min' => 0,
				'max' => 9999,
				'units' => true,
				'step' => 1,
				'inline' => true,
				'default' => '1.5em',
				'css'   => array(
					array(
						'property' => '--fr-switch-indicatorHeight',
						'selector' => '',
					),
				),
			);

		 $this->controls['sliderIndicatorWidth'] =
			array(
				'group' => 'indicatorStyling',
				'label' => __( 'Width', 'frames' ),
				'type' => 'number',
				'min' => 0,
				'max' => 10,
				'units' => true,
				'step' => 1,
				'inline' => true,
				'default' => '1.5em',
				'css'   => array(
					array(
						'property' => '--fr-switch-indicatorWidth',
						'selector' => '',
					),
				),
			);

		 $this->controls['sliderIndicatorPadding'] =
			array(
				'group' => 'indicatorStyling',
				'label' => __( 'Space from edge', 'frames' ),
				'type' => 'number',
				'min' => 0,
				'max' => 10,
				'units' => true,
				'step' => 1,
				'inline' => true,
				'default' => '.2em',
				'css'   => array(
					array(
						'property' => '--fr-switch-indicatorPadding',
						'selector' => '',
					),
				),
			);

		 $this->controls['disabledIndicatorColor'] =
			array(
				'group' => 'indicatorStyling',
				'label' => __( 'Disabled Color', 'frames' ),
				'type' => 'color',
				'default' => array(
					'rgb' => 'var(--primary-light)',
				),
				'css'   => array(
					array(
						'property' => '--fr-switch-disabledColor',
						'selector' => '',
					),
				),
			);

		 $this->controls['enabledIndicatorColor'] =
			array(
				'group' => 'indicatorStyling',
				'label' => __( 'Enabled Color', 'frames' ),
				'type' => 'color',
				'default' => array(
					'rgb' => 'var(--primary)',
				),
				'css'   => array(
					array(
						'property' => '--fr-switch-enabledColor',
						'selector' => '',
					),
				),
			);

		 $this->controls['indicatorBorder'] =
			array(
				'group' => 'indicatorStyling',
				'label' => __( 'Border', 'frames' ),
				'type' => 'border',
				'default' => '',
				'inlineEditing' => true,
				'css'   => array(
					array(
						'property' => 'border',
						'selector' => '.fr-switch__slider-indicator',
					),
				),
			);

		 $this->controls['indicatorTransition'] =
			array(
				'group' => 'indicatorStyling',
				'label' => __( 'Transition', 'frames' ),
				'type' => 'text',
				'default' => 'all .3s ease',
				'inlineEditing' => true,
				'css'   => array(
					array(
						'property' => '--fr-switch-indicatorTransition',
						'selector' => '',
					),
				),
			);

	}


	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	public function render() {
		$settings = $this->settings;

		// $defaultActive = ! empty( $settings['defaultActive'] );
		// $defaultActive = (int) $defaultActive;
		// $firstItemOpened = isset( $settings['firstItemOpened'] );
		$defaultActive  = isset( $settings['defaultActive'] );
		$contentSelector = ! empty( $settings['switcherSelector'] ) ? $settings['switcherSelector'] : '';
		$firstLabel = ! empty( $settings['firstLabel'] ) ? $settings['firstLabel'] : '';
		$secondLabel = ! empty( $settings['secondLabel'] ) ? $settings['secondLabel'] : '';
		$ariaLabel = ! empty( $settings['ariaLabel'] ) ? $settings['ariaLabel'] : '';

		$options = array(
			'defaultActive' => $defaultActive,
			'contentSelector' => $contentSelector,
		);

		if ( is_array( $options ) ) {
			$options = wp_json_encode( $options );
		}

		$options = str_replace( array( "\r", "\n", ' ' ), '', $options );

		$this->set_attribute( '_root', 'class', 'fr-switch' );
		$this->set_attribute( '_root', 'aria-label', $ariaLabel );
		$this->set_attribute( '_root', 'type', 'button' );
		$this->set_attribute( '_root', 'data-fr-switch-options', trim( $options ) );

		$output = '<div>';
		$output .= "<button {$this->render_attributes('_root')}>";

		if ( ! empty( $firstLabel ) ) {
			$output .= '<span class="fr-switch__label fr-switch__label--first">' . $firstLabel . '</span>';
		}

		$output .= '<span class="fr-switch__slider">';
		$output .= '<span class="fr-switch__slider-indicator"></span>';
		$output .= '</span>';

		if ( ! empty( $secondLabel ) ) {
			$output .= '<span class="fr-switch__label fr-switch__label--second">' . $secondLabel . '</span>';
		}

		$output .= '</ button>';
		$output .= '</div>';

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

}
