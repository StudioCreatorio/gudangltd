<?php
/**
 * Tabs Widget.
 *
 * @package Frames_Client
 */

namespace Frames_Client\Widgets\Tabs;

use Frames_Client\Widgets\Tabs\Inc\Tabs_Functions;
use \Frames_Client\Widget_Manager;

/**
 * Tabs class.
 */
class Tabs_Widget extends \Bricks\Element {
	/**
	 * Use predefined element category 'general'.
	 *
	 * @var string
	 */
	public $category = 'Frames';

	/**
	 * Make sure to prefix your elements.
	 *
	 * @var string
	 */
	public $name = 'fr-tabs';

	/**
	 * Themify icon font class.
	 *
	 * @var string
	 */
	public $icon = 'fas fa-th';

	/**
	 * Default CSS selector.
	 *
	 * @var string
	 */
	// public $css_selector = '.tabs-wrapper'; // End comment.

	/**
	 * Scripts to be enqueued.
	 *
	 * @var array
	 */
	public $scripts = array( 'tabs_script' );

	/**
	 * Is nestable.
	 *
	 * @var boolean
	 */
	public $nestable = true;

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function get_methods() {
		 include( 'inc/tabs-functions.php' );
	}

	/**
	 * Get widget label.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget Label.
	 */
	public function get_label() {
		return esc_html__( 'Frames Tabs', 'frames' );
	}

	/**
	 * Register widget control groups.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	public function set_control_groups() {

		// accordion.

		$this->control_groups['accordion'] = array(
			'title' => esc_html__( 'Accordion', 'frames' ),
			'tab' => 'content',
			'required' => array(
				array(
					'changeToAccordion',
					'=',
					true
				),
			),
		);

		// Navigation.

		$this->control_groups['navigation'] = array(
			'title' => esc_html__( 'Navigation', 'frames' ),
			'tab' => 'content',
		);

		// Navigation Items.

		$this->control_groups['navigation_items'] = array(
			'title' => esc_html__( 'Navigation Items', 'frames' ),
			'tab' => 'content',
		);

		$this->control_groups['active_tab'] = array(
			'title' => esc_html__( 'Active Tab', 'frames' ),
			'tab' => 'content',
			'required' => array(
				array(
					'animate',
					'!=',
					true
				),
			),
		);

		$this->control_groups['animated_navigation'] = array(
			'title' => esc_html__( 'Animated Active Tab', 'frames' ),
			'tab' => 'content',
			'required' => array(
				array(
					'animate',
					'=',
					true
				),
			),
		);

		$this->control_groups['accessibility'] = array(
			'title' => esc_html__( 'Accessibility', 'frames' ),
			'tab' => 'content',
		);
	}

	/**
	 * Get dynamic breakpoints.
	 *
	 * @return array
	 */
	public function get_dynamic_breakpoints() {
		$breakpoints = array();
		foreach ( \Bricks\Breakpoints::$breakpoints as $breakpoint ) {
			$breakpoints[ $breakpoint['key'] ] = $breakpoint['label'];
		}
		return $breakpoints;
	}

	/**
	 * Register widget controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	public function set_controls() {

			// Global controls.

			$this->controls['activeTab'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Active Tab', 'frames' ),
				'info'              => esc_html__( 'Set the active tab on load. Remember that first tab = 0', 'frames' ),
				'type'        => 'number',
				'placeholder' => '0',
				'default'     => 0,
			);

			$this->controls['contentOutside'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Content Outside', 'frames' ),
				'info'              => esc_html__( 'Display content outside of the tabs, anywhere you want', 'frames' ),
				'type'        => 'checkbox',
				'inline'      => true,
				'default'     => false,
			);

			$this->controls['contentOutsideSelectorInfo'] = array(
				'tab' => 'content',
				'description' => esc_html__( 'Grab Tabs Content Wrapper and move it to desired location within structure panel. Set up an unique class for it', 'frames' ),
				'type' => 'info',
				'required' => array(
					array(
						'contentOutside',
						'=',
						true
					),
				),
			);

			$this->controls['contentOutsideSelector'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Content Outside Selector', 'frames' ),
				'info'              => esc_html__( 'Set the selector for the content outside of the tabs', 'frames' ),
				'type'        => 'text',
				'placeholder' => '.fr-tabs__content--outside',
				'default'     => '',
				'required'    => array(
					array(
						'contentOutside',
						'=',
						true
					),
				),
			);

			$this->controls['animate'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Animated navigation', 'frames' ),
				'type'        => 'checkbox',
				'inline'      => true,
				'default'     => true,
			);

			$this->controls['changeToAccordion'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Change to Accordion', 'frames' ),
				'info'              => esc_html__( 'Change tabs to accordion on smaller devices', 'frames' ),
				'type'        => 'checkbox',
				'inline'      => true,
				'default'     => false,
			);

			$this->controls['accordionOnDevice'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Change to Accordion on', 'frames' ),
				'type'        => 'select',
				'group'       => 'accordion',
				'options'     => $this->get_dynamic_breakpoints(),
				'inline'      => true,
				'default'     => 'mobile_portrait',
				'required'    => array(
					array(
						'changeToAccordion',
						'=',
						true
					),
				),
			);

			$this->controls['closePreviousAccordion'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Close Previous Accordion', 'frames' ),
				'info'              => esc_html__( 'Close previous accordion when opening new one', 'frames' ),
				'type'        => 'checkbox',
				'group'       => 'accordion',
				'inline'      => true,
				'required'    => array(
					array(
						'changeToAccordion',
						'=',
						true
					),
				),
			);

			$this->controls['accordionDuration'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Slide Duration', 'frames' ),
				'type'        => 'number',
				'group'       => 'accordion',
				'placeholder' => '0',
				'default'     => 300,
				'required'    => array(
					array(
						'changeToAccordion',
						'=',
						true
					),
				),
			);

			$this->controls['accordionHeaderBackgroundColor'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Header Background Color', 'frames' ),
				'type'        => 'color',
				'group'       => 'accordion',
				'breakpoints' => true,
				'css'         => array(
					array(
						'property' => 'background-color',
						'selector' => '.fr-tabs__list--accordion .fr-tabs__link'
					),
				),
			);

			// Header Color.

			$this->controls['headerColor'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Header Color', 'frames' ),
				'type'        => 'color',
				'group'       => 'accordion',
				'breakpoints' => true,
				'css'         => array(
					array(
						'property' => 'color',
						'selector' => '.fr-tabs__list--accordion .fr-tabs__link',
					),
				),
			);

			// for navigation group set navigationLayout control.

			$this->controls['navigationDirection'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Layout', 'frames' ),
				'type'        => 'direction',
				'group'       => 'navigation',
				'breakpoints' => true,
				'css'         => array(
					array(
						'property' => 'flex-direction',
						'selector' => '.fr-tabs__list'
					),
				),
				'default'     => 'row',
			);

			$this->controls['navigationJustifyContent'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Justify Content', 'frames' ),
				'type'        => 'justify-content',
				'group'       => 'navigation',
				'breakpoints' => true,
				'css'         => array(
					array(
						'property' => 'justify-content',
						'selector' => '.fr-tabs__list'
					),
				),
			);

			$this->controls['navigationAlignItems'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Align Items', 'frames' ),
				'type'        => 'align-items',
				'group'       => 'navigation',
				'breakpoints' => true,
				'css'         => array(
					array(
						'property' => 'align-items',
						'selector' => '.fr-tabs__list'
					),
				),
			);

			$this->controls['navigationBackground'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Background', 'frames' ),
				'type'        => 'color',
				'group'       => 'navigation',
				'breakpoints' => true,
				'css'         => array(
					array(
						'property' => 'background-color',
						'selector' => '.fr-tabs__list'
					),
				),
			);

			$this->controls['navigationBorder'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Border', 'frames' ),
				'type'        => 'border',
				'group'       => 'navigation',
				'breakpoints' => true,
				'css'         => array(
					array(
						'property' => 'border',
						'selector' => '.fr-tabs__list'
					),
				),
			);

			$this->controls['navigationPadding'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Padding', 'frames' ),
				'type'        => 'dimensions',
				'group'       => 'navigation',
				'breakpoints' => true,
				'css'         => array(
					array(
						'property' => 'padding',
						'selector' => '.fr-tabs__list'
					),
				),
			);

			// Navigation Items.

			$this->controls['linkPadding'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Padding', 'frames' ),
				'type'        => 'dimensions',
				'group'       => 'navigation_items',
				'css'         => array(
					array(
						'property' => 'padding',
						'selector' => '.fr-tabs__link'
					),
				),
				'default'     => array(
					'top' => 'var(--space-xs)',
					'right' => 'var(--space-m)',
					'bottom' => 'var(--space-xs)',
					'left' => 'var(--space-m)',
				),
			);

			$this->controls['linkTypography'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Typography', 'frames' ),
				'type'        => 'typography',
				'group'       => 'navigation_items',
				'css'         => array(
					array(
						'property' => 'font',
						'selector' => '.fr-tabs__link'
					),
				),
			);

			$this->controls['borderLink'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Border', 'frames' ),
				'type'        => 'border',
				'group'       => 'navigation_items',
				'css'         => array(
					array(
						'property' => 'border',
						'selector' => '.fr-tabs__link'
					),
				),
			);

			// number type control for width.

			$this->controls['linkWidth'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Width', 'frames' ),
				'type'        => 'number',
				'group'       => 'navigation_items',
				'css'         => array(
					array(
						'property' => 'width',
						'selector' => '.fr-tabs__link'
					),
				),
			);

			// min width.

			$this->controls['linkMinWidth'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Min Width', 'frames' ),
				'type'        => 'number',
				'group'       => 'navigation_items',
				'breakpoints'  => true,
				'info'              => ' useful for setting up mobile navigation',
				'css'         => array(
					array(
						'property' => 'min-width',
						'selector' => '.fr-tabs__link'
					),
				),
				'default'     => 'auto',
			);

			$this->controls['linkBgInfo'] = array(
				'tab' => 'content',
				'description' => esc_html__( 'It is recommended not to style a link background color when animated navigation is active to avoid visual glitches.', 'frames' ),
				'type' => 'info',
				'group' => 'navigation_items',
				'required' => array(
					array(
						'animate',
						'=',
						true
					),
				),
			);

			$this->controls['backgroundLink'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Background', 'frames' ),
				'type'        => 'color',
				'group'       => 'navigation_items',
				'css'         => array(
					array(
						'property' => 'background-color',
						'selector' => '.fr-tabs__link'
					),
				),
			);

			// Active Tabs.

			$this->controls['backgroundActiveLink'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Background', 'frames' ),
				'type'        => 'color',
				'group'       => 'active_tab',
				'css'         => array(
					array(
						'property' => 'background-color',
						'selector' => '.fr-tabs__link.active'
					),
				),
				'default'     => array(
					'raw' => 'var(--shade-ultra-light)',
				),
				'required'    => array(
					array(
						'animate',
						'!=',
						true
					),
				),
			);

			$this->controls['fontActiveLink'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Typography', 'frames' ),
				'type'        => 'typography',
				'group'       => 'active_tab',
				'css'         => array(
					array(
						'property' => 'font',
						'selector' => '.fr-tabs__link.active'
					),
				),
				'required'    => array(
					array(
						'animate',
						'!=',
						true
					),
				),
			);

			$this->controls['borderActiveLink'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Border', 'frames' ),
				'type'        => 'border',
				'group'       => 'active_tab',
				'css'         => array(
					array(
						'property' => 'border',
						'selector' => '.fr-tabs__link.active'
					),
				),
				'required'    => array(
					array(
						'animate',
						'!=',
						true
					),
				),
			);

			$this->controls['animationDuration'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Slide Duration', 'frames' ),
				'type'        => 'number',
				'group'       => 'animated_navigation',
				'placeholder' => '0',
				'default'     => 300,
				'required'    => array(
					array(
						'animate',
						'=',
						true
					),
				),
			);

			$this->controls['backgroundAnimatedActiveLink'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Background', 'frames' ),
				'type'        => 'color',
				'group'       => 'animated_navigation',
				'css'         => array(
					array(
						'property' => 'background-color',
						'selector' => '.fr-tabs__animation'
					),
				),
				'default'     => array(
					'raw' => 'var(--shade-ultra-light)',
				),
				'required'    => array(
					array(
						'animate',
						'=',
						true
					),
				),
			);

			$this->controls['borderAnimatedActiveLink'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Border', 'frames' ),
				'type'        => 'border',
				'group'       => 'animated_navigation',
				'css'         => array(
					array(
						'property' => 'border',
						'selector' => '.fr-tabs__animation'
					),
				),
				'required'    => array(
					array(
						'animate',
						'=',
						true
					),
				),
			);

			// font.

			$this->controls['fontAnimatedActiveLink'] = array(
				'tab'         => 'content',
				'label'       => esc_html__( 'Typography', 'frames' ),
				'type'        => 'typography',
				'group'       => 'animated_navigation',
				'css'         => array(
					array(
						'property' => 'font',
						'selector' => '.fr-tabs__link.active'
					),
				),
				'required'    => array(
					array(
						'animate',
						'=',
						true
					),
				),
			);

			// Accessibility controls.

			$this->controls['tag'] = array(
				'group'    => 'accessibility',
				'tab'         => 'content',
				'label'       => esc_html__( 'Tag', 'frames' ),
				'type'        => 'select',
				'options'     => array(
					'div' => 'div',
					'section' => 'section',
					'aside' => 'aside',
					'header' => 'header',
				),
				'inline'      => true,
				'clearable'   => false,
				'default' => 'div',
			);

			$this->controls['scrollToHash'] = array(
				'label'   => esc_html__( 'Scroll To Hash', 'frames' ),
				'type'    => 'checkbox',
				'inline'  => true,
				'group'   => 'accessibility',
			);

	}


	/**
	 * Enqueue Scripts and Styles for the widget
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	public function enqueue_scripts() {
		if ( ! Widget_Manager::is_bricks_frontend() ) {
			return;
		}
		$filename = 'tabs';
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
	 * Get nestable item.
	 *
	 * @param int $tab_number The tab index number.
	 * @return array
	 */
	public function get_navigation_item( $tab_number ) {
		return array(
			'name' => 'div',
			'label' => 'Navigation Item',
			'settings' => array(
				'tag' => 'custom',
				'customTag' => 'li',
				'_hidden' => array(
					'_cssClasses' => 'fr-tabs__link',
				),
			),
			'children' => array(
				array(
					'name' => 'text-basic',
					'label' => "Tab {$tab_number}",
					'settings' => array(
						'text' => "Tab {$tab_number}",
						'_hidden' => array(
							'_cssClasses' => 'fr-tabs__link-text',
						),
					),
				)
			)
		);
	}

	/**
	 * Get the content of the child item.
	 *
	 * @param int $tab_number The tab index number.
	 * @return array
	 */
	public function get_content_child_item( $tab_number ) {
		return array(
			'name' => 'block',
			'label' => 'Tabs Item Wrapper',
			'settings' => array(
				'tag' => 'li',
				'_hidden' => array(
					'_cssClasses' => 'fr-tabs__content-item-wrapper',
				),
			),
			'children' => array(
				array(
					'name' => 'block',
					'label' => 'Tabs Item',
					'settings' => array(
						'tag' => 'div',
						'_hidden' => array(
							'_cssClasses' => 'fr-tabs__content-item',
						),
					),
					'children' => array(
						array(
							'name' => 'text-basic',
							'label' => "Content for tab {$tab_number}",
							'settings' => array(
								'text' => "Content for tab {$tab_number}",
								'_hidden' => array(
									'_cssClasses' => 'fr-tabs__content-text',
								),
							),
						)
					)
				)
			)
		);
	}

	/**
	 * Get nestable children.
	 *
	 * @return array
	 */
	public function get_nestable_children() {

		$navigationItems = array();
		$contentItems = array();
		for ( $i = 1; $i <= 3; $i++ ) {
			$navigationItems[] = $this->get_navigation_item( $i );
			$contentItems[] = $this->get_content_child_item( $i );
		}

		return array(
			array(
				'name'  => 'block',
				'label' => 'Tabs Navigation',
				'settings' => array(
					'tag' => 'nav',
					'_hidden' => array(
						'_cssClasses' => 'fr-tabs__nav',
					),
				),
				'children' => array(
					array(
						'name' => 'block',
						'label' => 'Navigation List',
						'settings' => array(
							'tag' => 'custom',
							'customTag' => 'ul',
							'_hidden' => array(
								'_cssClasses' => 'fr-tabs__list',
							),
						),
						'children' => $navigationItems,
					)
				)
			),
			array(
				'name' => 'block',
				'label' => 'Tabs Content Wrapper',
				'settings' => array(
					'tag' => 'custom',
					'customTag' => 'ul',
					'_hidden' => array(
						'_cssClasses' => 'fr-tabs__content-wrapper',
					),
				),
				'children' => $contentItems,
			)
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

		$animate = isset( $settings['animate'] ) ? $settings['animate'] : false;
		$scrollToHash = isset( $settings['scrollToHash'] );
		$activeTab = isset( $settings['activeTab'] ) ? $settings['activeTab'] : 0;
		$animationTarget = isset( $settings['animationTarget'] ) ? $settings['animationTarget'] : 'background'; // background, bottom-border.
		$animationDuration = isset( $settings['animationDuration'] ) ? $settings['animationDuration'] : 300;
		$isMoveContentOutside = isset( $settings['contentOutside'] ) ? $settings['contentOutside'] : false;
		$contentOutsideSelector = isset( $settings['contentOutsideSelector'] ) ? $settings['contentOutsideSelector'] : '';
		$bottomBorderHeight = isset( $settings['bottomBorderHeight'] ) ? $settings['bottomBorderHeight'] : 5;
		$tabsDirection = $settings['navigationDirection'];
		$isChangeToAccordion = isset( $settings['changeToAccordion'] ) ? $settings['changeToAccordion'] : false;
		$accordionOnDevice = isset( $settings['accordionOnDevice'] ) ? $settings['accordionOnDevice'] : false;
		$closePreviousAccordion = isset( $settings['closePreviousAccordion'] ) ? $settings['closePreviousAccordion'] : false;
		$accordionDuration = isset( $settings['accordionDuration'] ) ? $settings['accordionDuration'] : 300;

		$isHorizontal = 'row' === $tabsDirection || 'row-reverse' === $tabsDirection;

		$options = array(
			'animate' => $animate, // boolean so the tabs will animate on click.
			'scrollToHash' => $scrollToHash, // boolean so the tabs will scroll to the top of the tab content on click.
			'activeTab' => $activeTab, // integer so the tabs will start on the tab with the provided index.
			'contentOutside' => $isMoveContentOutside, // boolean so the tabs will look for the content outside of the tabs wrapper.
			'isHorizontal' => $isHorizontal ? 1 : 0,
		);

		if ( $options['animate'] ) {
			$options['animation'] = array(
				'target' => $animationTarget,
				'duration' => $animationDuration,
			);

			if ( 'bottom-border' === $options['animation']['target'] ) {
				$options['animation']['height'] = $bottomBorderHeight;
			}
		}

		if ( $options['contentOutside'] ) {
			$options['contentOutsideSelector'] = $contentOutsideSelector;
		}

		if ( $isChangeToAccordion ) {

			$breakpoints = array();

			foreach ( \Bricks\Breakpoints::$breakpoints as $breakpoint ) {
				$breakpoints[] = $breakpoint;
			}

			$options['isChangeToAccordion'] = $isChangeToAccordion;
			foreach ( $breakpoints as $breakpoint ) {
				if ( $breakpoint['key'] == $accordionOnDevice ) {
						$accordionOnDevice = $breakpoint['width'];
						break;
				}
			}

			$options['accordionOnDevice'] = $accordionOnDevice;
			$options['closePreviousAccordion'] = $closePreviousAccordion;
			$options['accordionDuration'] = $accordionDuration;
		}

		if ( is_array( $options ) ) {
			$options = wp_json_encode( $options );
		}

		$options = str_replace( array( "\r", "\n", ' ' ), '', $options );

		if ( method_exists( '\Bricks\Query', 'is_any_looping' ) ) {

			$query_id = \Bricks\Query::is_any_looping();

			if ( $query_id ) {
				$this->set_attribute( '_root', 'data-id', $this->id . '_' . \Bricks\Query::get_loop_index() );
			} else {
				$this->set_attribute( '_root', 'data-id', $this->id );
			}
		}

		$tag = isset( $settings['tag'] ) ? esc_html( $settings['tag'] ) : 'div';

		$this->set_attribute( '_root', 'class', 'fr-tabs' );
		$this->set_attribute( '_root', 'data-fr-tabs-options', trim( $options ) );

		$output = '<' . $tag . " {$this->render_attributes('_root')}>";
		$output .= \Bricks\Frontend::render_children( $this );
		$output .= '</' . $tag . '>'; // _root.

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}
}
