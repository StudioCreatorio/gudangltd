<?php
/**
 * Modal Widget.
 *
 * @package Frames_Client
 */

namespace Frames_Client\Widgets\Modal;

use Frames_Client\Widgets\Modal\Inc\Modal_Functions;
use \Frames_Client\Widget_Manager;
use BricksExtras\Helpers;

/**
 * Modal class.
 */
class Modal_Widget extends \Bricks\Element {

	/**
	 * Use predefined element category 'general'.
	 *
	 * @var string
	 */
	public $category = 'Frames';

	/**
	 * I Might have create waaay to little fo a specific name... It might collide with live projects... (need to discuss I want to change it to fr-modal)
	 *
	 * @var string
	 */
	public $name = 'fr-modal';

	/**
	 * Themify icon font class.
	 *
	 * @var string
	 */
	public $icon = 'fas fa-copy';

	/**
	 * Default CSS selector.
	 *
	 * @var string
	 */
	public $css_selector = '.modal-wrapper';

	/**
	 * Scripts to be enqueued.
	 *
	 * @var array
	 */
	public $scripts = array( 'modal_script' );

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
	// public function get_methods()
	// {
	// include('inc/modal-functions.php');
	// } // TODO: check if this is needed.


	/**
	 * Get widget label.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget Label.
	 */
	public function get_label() {
		return esc_html__( 'Frames Modal', 'frames' );
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
			'tab'   => 'content',
		);

		$this->control_groups['overlay'] = array(
			'title' => esc_html__( 'Overlay', 'frames' ),
			'tab'   => 'content',
		);

		$this->control_groups['positioning'] = array(
			'title' => esc_html__( 'Modal Positioning', 'frames' ),
			'tab'   => 'content',
		);

		$this->control_groups['body'] = array(
			'title' => esc_html__( 'Body Container', 'frames' ),
			'tab'   => 'content',
		);

		$this->control_groups['closeIcon'] = array(
			'title' => esc_html__( 'Close Icon', 'frames' ),
			'tab'   => 'content',
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

		$this->controls['hideModal'] = array(
			'label'   => __( 'Hide Modal in Builder', 'frames' ),
			'type'    => 'checkbox',
			'inline'  => true,
			'default' => false,
		);

		$this->controls['triggerSelector'] = array(
			'group'         => 'settings',
			'label'         => __( 'Trigger Selector', 'frames' ),
			'type'          => 'text',
			'default'       => '.fr-trigger',
			'inlineEditing' => true,
		);

		$this->controls['closeSelector'] = array(
			'group'         => 'settings',
			'label'         => __( 'Close Selector', 'frames' ),
			'type'          => 'text',
			'default'       => '',
			'inlineEditing' => true,
		);

		$this->controls['fadeTime'] = array(
			'group'   => 'settings',
			'label'   => __( 'Fade Time', 'frames' ),
			'type'    => 'number',
			'min'     => 0,
			'max'     => 999,
			'units'   => false,
			'step'    => 1,
			'inline'  => true,
			'default' => '300',
		);

		$this->controls['videoIsAutoplay'] = array(
			'group'   => 'settings',
			'label'   => __( 'Autoplay video on open', 'frames' ),
			'type'    => 'checkbox',
			'inline'  => true,
			'default' => false,
		);

		$this->controls['overlayColor'] = array(
			'group'   => 'overlay',
			'label'   => __( 'Overlay color', 'frames' ),
			'type'    => 'color',
			'default' => array(
				'raw' => 'var(--shade-trans-40)',
			),
			'css'     => array(
				array(
					'property' => 'background-color',
					'selector' => '.fr-modal__overlay',
				),
			),
		);

		// Positioning Controls.
		 $this->controls['positionFor'] = array(
			 'tab'        => 'content',
			 'group'      => 'positioning',
			 'label'      => __( 'Place in relation to: ', 'frames' ),
			 'type'       => 'select',
			 'options'    => array(
				 'page'    => 'Page',
				 'trigger' => 'Trigger',
			 ),
			 'inline'     => true,
			 'multiple'   => false,
			 'searchable' => false,
			 'clearable'  => false,
			 'default'    => 'page',
		 );

		 $this->controls['positionRelatedToTrigger'] = array(
			 'tab'        => 'content',
			 'group'      => 'positioning',
			 'label'      => __( 'Is should be on: ', 'frames' ),
			 'type'       => 'select',
			 'options'    => array(
				 'top'    => 'Top',
				 'bottom' => 'Bottom',
			 ),
			 'info'       => __( 'If you choose top but there is no place for it it will automatically act like bottom and vice versa', 'frames' ),
			 'inline'     => true,
			 'multiple'   => false,
			 'searchable' => false,
			 'default'    => 'bottom',
			 'required'   => array(
				 'positionFor',
				 '=',
				 'trigger',
			 ),
		 );

		 $this->controls['placeFromTriggers'] = array(
			 'tab'        => 'content',
			 'group'      => 'positioning',
			 'label'      => __( 'Place from triggers: ', 'frames' ),
			 'type'       => 'select',
			 'options'    => array(
				 'left'      => 'Left side',
				 'right'     => 'Right side',
				 'center'    => 'Center',
				 'full'      => 'Full page width',
				 'container' => 'Full container width',
			 ),
			 'info'       => __( 'If you choose left but there is no place for it it will automatically act like right and vice versa', 'frames' ),
			 'inline'     => true,
			 'multiple'   => false,
			 'searchable' => false,
			 'default'    => 'bottom',
			 'required'   => array(
				 'positionFor',
				 '=',
				 'trigger',
			 ),
		 );

		 $this->controls['offsetFromTrigger'] = array(
			 'group'    => 'positioning',
			 'label'    => __( 'Offset from trigger', 'frames' ),
			 'type'     => 'number',
			 'min'      => 0,
			 'max'      => 999,
			 'units'    => true,
			 'step'     => 1,
			 'inline'   => true,
			 'required' => array(
				 'positionFor',
				 '=',
				 'trigger',
			 ),
		 );

		 $this->controls['verticalPosition'] = array(
			 'group'    => 'positioning',
			 'label'    => __( 'Horizontal', 'frames' ),
			 'type'     => 'justify-content',
			 'css'      => array(
				 array(
					 'property' => 'justify-content',
					 'selector' => '',
				 ),
			 ),
			 // https://academy.bricksbuilder.io/article/justify-content-control/
			 // HELP: according to the docs, this should work, but it doesn't.
			// 'exclude' => array(
			// 'space-between',
			// 'space-around',
			// 'space-evenly',
			// ), // TODO: check if this is still needed.
			 'exclude'  => 'space',
			 // TODO: I found this workaround poking in the Bricks base.php file. It's not what the docs say.
			 'required' => array(
				 'positionFor',
				 '=',
				 'page',
			 ),
		 );

		 $this->controls['horizontalPosition'] = array(
			 'group'    => 'positioning',
			 'label'    => __( 'Vertical', 'frames' ),
			 // TODO: @wojtekpiskorz - let's add a comment to explain why we're using the word "vertical" here.
			 'type'     => 'align-items',
			 'css'      => array(
				 array(
					 'property' => 'align-items',
					 'selector' => '',
				 ),
			 ),
			 // 'exclude' => array(
			 // 'space-between',
			 // 'space-around',
			 // 'space-evenly',
			 // ), // TODO: check if this is still needed.
			  'exclude'  => 'stretch',
			 // TODO: I found this workaround poking in the Bricks base.php file. It's not what the docs say.
			 'required' => array(
				 'positionFor',
				 '=',
				 'page',
			 ),
		 );

		 $this->controls['modalBodyOffsetVertical'] = array(
			 'group'    => 'positioning',
			 'label'    => __( 'Vertical offset', 'frames' ),
			 'type'     => 'number',
			 'min'      => 0,
			 'max'      => 999,
			 'units'    => true,
			 'step'     => 1,
			 'css'      => array(
				 array(
					 'selector' => '',
					 'property' => '--fr-modal-body-offset-vertical',
				 // 'important' => true,
				 ),
			 ),
			 'inline'   => true,
			 'default'  => '50px',
			 'required' => array(
				 'positionFor',
				 '=',
				 'page',
			 ),
		 );

		 $this->controls['modalBodyOffsetHorizontal'] = array(
			 'group'    => 'positioning',
			 'label'    => __( 'Horizontal offset', 'frames' ),
			 'type'     => 'number',
			 'min'      => 0,
			 'max'      => 999,
			 'units'    => true,
			 'step'     => 1,
			 'css'      => array(
				 array(
					 'selector' => '',
					 'property' => '--fr-modal-body-offset-horizontal',
				 // 'important' => true,
				 ),
			 ),
			 'inline'   => true,
			 'default'  => '50px',
			 'required' => array(
				 'positionFor',
				 '=',
				 'page',
			 ),
		 );

		 $this->controls['stylingInfo'] = array(
			 'group'    => 'body',
			 'content'  => esc_html__( 'Use the Style tab to customize the Modal\'s body container', 'frames' ),
			 'type'     => 'info',
			 'required' => false,
		 );

		 $this->controls['isScroll'] = array(
			 'group'   => 'body',
			 'label'   => __( 'Make modal Scrollable', 'frames' ),
			 'type'    => 'checkbox',
			 'inline'  => true,
			 'default' => false,
		 );

		 $this->controls['scrollMaxHeight'] = array(
			 'group'       => 'body',
			 'label'       => __( 'Max Height', 'frames' ),
			 'type'        => 'number',
			 'min'         => 0,
			 'max'         => 99999,
			 'step'        => 1,
			 'units'       => false,
			 'inline'      => true,
			 'placeholder' => '90vh',
			 'css'         => array(
				 array(
					 'property'  => 'max-height',
					 'selector'  => '.fr-modal__body',
					 'important' => true,
				 ),
			 ),
			 'required'    => array(
				 'isScroll',
				 '=',
				 true,
			 ),
		 );

		 $this->controls['isScrollBar'] = array(
			 'group'    => 'body',
			 'label'    => __( 'Hide Scrollbar', 'frames' ),
			 'type'     => 'checkbox',
			 'inline'   => true,
			 'default'  => false,
			 'required' => array(
				 'isScroll',
				 '=',
				 true,
			 ),

		 );

		 $this->controls['width'] = array(
			 'group'   => 'body',
			 'label'   => __( 'Width', 'frames' ),
			 'type'    => 'number',
			 'min'     => 0,
			 'max'     => 99999,
			 'step'    => 1,
			 'units'   => false,
			 'inline'  => true,
			 'default' => 'var(--width-l)',
			 'css'     => array(
				 array(
					 'property' => 'width',
					 'selector' => '.fr-modal__body',
				 ),
			 ),
		 );

		 $this->controls['backgroundColor'] = array(
			 'group'   => 'body',
			 'label'   => __( 'Background Color', 'frames' ),
			 'type'    => 'color',
			 'default' => array(
				 'rgb' => 'var(--shade-white)',
			 ),
			 'css'     => array(
				 array(
					 'property' => 'background-color',
					 'selector' => '.fr-modal__body',
				 ),
			 ),
		 );

		 $this->controls['padding'] = array(
			 'group'   => 'body',
			 'label'   => __( 'Padding', 'frames' ),
			 'type'    => 'dimensions',
			 'css'     => array(
				 array(
					 'property' => 'padding',
					 'selector' => '.fr-modal__body',
				 ),
			 ),
			 'default' => array(
				 'top'    => 'var(--space-m)',
				 'right'  => 'var(--space-m)',
				 'bottom' => 'var(--space-m)',
				 'left'   => 'var(--space-m)',
			 ),
		 );

		 $this->controls['isCloseButton'] = array(
			 'group'   => 'closeIcon',
			 'label'   => __( 'Use Close Button', 'frames' ),
			 'type'    => 'checkbox',
			 'inline'  => true,
			 'default' => true,
		 );

		 $this->controls['iconPlacement'] = array(
			 'tab'        => 'content',
			 'group'      => 'closeIcon',
			 'label'      => __( 'Icon is: ', 'frames' ),
			 'type'       => 'select',
			 'options'    => array(
				 'outside' => 'Outside',
				 'inside'  => 'Inside',
			 ),
			 'inline'     => true,
			 'multiple'   => false,
			 'searchable' => false,
			 'clearable'  => false,
			 'default'    => 'outside',
			 'required'   => array(
				 'isCloseButton',
				 '=',
				 true,
			 ),
		 );

		 $this->controls['icon'] = array(
			 'group'    => 'closeIcon',
			 'label'    => __( 'Icon', 'frames' ),
			 'type'     => 'icon',
			 'default'  => array(
				 'library' => 'themify',
				 'icon'    => 'ti-close',
			 ),
			 'required' => array(
				 'isCloseButton',
				 '=',
				 true,
			 ),
		 );

		 $this->controls['iconColor'] = array(
			 'group'    => 'closeIcon',
			 'label'    => __( 'Color', 'frames' ),
			 'type'     => 'color',
			 'default'  => array(
				 'rgb' => 'var(--shade-ultra-dark)',
			 ),
			 'css'      => array(
				 array(
					 'property' => 'color',
					 'selector' => '.fr-modal__close-icon',
				 ),
			 ),
			 'required' => array(
				 'isCloseButton',
				 '=',
				 true,
			 ),
		 );

		 $this->controls['iconBackgroundColor'] = array(
			 'group'    => 'closeIcon',
			 'label'    => __( 'Background Color', 'frames' ),
			 'type'     => 'color',
			 'default'  => array(
				 'rgb' => 'var(--shade-white)',
			 ),
			 'css'      => array(
				 array(
					 'property' => 'background-color',
					 'selector' => '.fr-modal__close-icon-wrapper',
				 ),
			 ),
			 'required' => array(
				 'isCloseButton',
				 '=',
				 true,
			 ),
		 );

		 $this->controls['iconSize'] = array(
			 'group'    => 'closeIcon',
			 'label'    => __( 'Icon Size', 'frames' ),
			 'type'     => 'number',
			 'min'      => 0,
			 'max'      => 99999,
			 'step'     => 1,
			 'units'    => false,
			 'inline'   => true,
			 'default'  => 'var(--text-m)',
			 'css'      => array(
				 array(
					 'property' => 'font-size',
					 'selector' => '.fr-modal__close-icon',
				 ),
			 ),
			 'required' => array(
				 'isCloseButton',
				 '=',
				 true,
			 ),
		 );

		 $this->controls['iconBackgroundSize'] = array(
			 'group'    => 'closeIcon',
			 'label'    => __( 'Icon Background Size', 'frames' ),
			 'type'     => 'number',
			 'min'      => 0,
			 'max'      => 99999,
			 'step'     => 1,
			 'units'    => false,
			 'inline'   => true,
			 'default'  => 'var(--space-l)',
			 'css'      => array(
				 array(
					 'property' => 'width',
					 'selector' => '.fr-modal__close-icon',
				 ),
			 ),
			 'required' => array(
				 'isCloseButton',
				 '=',
				 true,
			 ),
		 );

		 $this->controls['iconBorder'] = array(
			 'group'         => 'closeIcon',
			 'label'         => __( 'Border', 'frames' ),
			 'type'          => 'border',
			 'default'       => '',
			 'inlineEditing' => true,
			 'css'           => array(
				 array(
					 'property' => 'border',
					 'selector' => '.fr-modal__close-icon-wrapper',
				 ),
			 ),
			 'required'      => array(
				 'isCloseButton',
				 '=',
				 true,
			 ),
		 );

		 $this->controls['iconPosTop'] = array(
			 'group'    => 'closeIcon',
			 'label'    => __( 'Top', 'frames' ),
			 'type'     => 'number',
			 'min'      => 0,
			 'max'      => 99999,
			 'step'     => 1,
			 'units'    => false,
			 'inline'   => true,
			 'default'  => 'var(--space-m)',
			 'css'      => array(
				 array(
					 'property' => 'top',
					 'selector' => '.fr-modal__close-icon-wrapper',
				 ),
			 ),
			 'required' => array(
				 'isCloseButton',
				 '=',
				 true,
			 ),
		 );

		 $this->controls['iconPosRight'] = array(
			 'group'    => 'closeIcon',
			 'label'    => __( 'Right', 'frames' ),
			 'type'     => 'number',
			 'min'      => 0,
			 'max'      => 99999,
			 'step'     => 1,
			 'units'    => false,
			 'inline'   => true,
			 'default'  => 'var(--space-m)',
			 'css'      => array(
				 array(
					 'property' => 'right',
					 'selector' => '.fr-modal__close-icon-wrapper',
				 ),
			 ),
			 'required' => array(
				 'isCloseButton',
				 '=',
				 true,
			 ),
		 );

		 $this->controls['iconPosBottom'] = array(
			 'group'    => 'closeIcon',
			 'label'    => __( 'Bottom', 'frames' ),
			 'type'     => 'number',
			 'min'      => 0,
			 'max'      => 99999,
			 'step'     => 1,
			 'units'    => false,
			 'inline'   => true,
			 'default'  => 'auto',
			 'css'      => array(
				 array(
					 'property' => 'bottom',
					 'selector' => '.fr-modal__close-icon-wrapper',
				 ),
			 ),
			 'required' => array(
				 'isCloseButton',
				 '=',
				 true,
			 ),
		 );

		 $this->controls['iconPosLeft'] = array(
			 'group'    => 'closeIcon',
			 'label'    => __( 'Left', 'frames' ),
			 'type'     => 'number',
			 'min'      => 0,
			 'max'      => 99999,
			 'step'     => 1,
			 'units'    => false,
			 'inline'   => true,
			 'default'  => 'auto',
			 'css'      => array(
				 array(
					 'property' => 'left',
					 'selector' => '.fr-modal__close-icon-wrapper',
				 ),
			 ),
			 'required' => array(
				 'isCloseButton',
				 '=',
				 true,
			 ),
		 );
	}

	/**
	 * Convert boolean to string
	 *
	 * @param bool $bool Boolean value.
	 * @return string
	 */
	public function toString( $bool ) {
		return $bool ? 'true' : 'false';
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

		$filename = 'modal';
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
		wp_enqueue_script( // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			'youtube-iframe-api',
			'https://www.youtube.com/iframe_api',
			array(),
			null,
			true
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
		// $this->get_methods();
		$triggerSelector = ! empty( $settings['triggerSelector'] ) ? $settings['triggerSelector'] : '.fr-trigger';
		$closeSelector   = ! empty( $settings['closeSelector'] ) ? $settings['closeSelector'] : '.fr-modal__close';
		$fadeTime        = ! empty( $settings['fadeTime'] ) ? $settings['fadeTime'] : 300;

		$videoIsAutoplay = isset( $settings['videoIsAutoplay'] );
		$isScroll        = isset( $settings['isScroll'] );
		$isScrollBar     = isset( $settings['isScrollBar'] );
		$hideModal       = isset( $settings['hideModal'] );
		$isCloseButton   = isset( $settings['isCloseButton'] );
		$iconPlacement   = ! empty( $settings['iconPlacement'] ) ? $settings['iconPlacement'] : 'outside';
		$icon            = ! empty( $this->settings['icon'] ) ? self::render_icon( $this->settings['icon'] ) : false;

		// Positioning Controls.
		$positionFor              = ! empty( $settings['positionFor'] ) ? $settings['positionFor'] : 'page';
		$positionRelatedToTrigger = ! empty( $settings['positionRelatedToTrigger'] ) ? $settings['positionRelatedToTrigger'] : 'bottom';
		$placeFromTriggers        = ! empty( $settings['placeFromTriggers'] ) ? $settings['placeFromTriggers'] : 'center';
		$offsetFromTrigger        = ! empty( $settings['offsetFromTrigger'] ) ? $settings['offsetFromTrigger'] : '0';

		$queryID = null;

		// Query.
		if ( method_exists( '\Bricks\Query', 'is_any_looping' ) ) {
			$query = \Bricks\Query::is_any_looping();

			if ( $query ) {
				$this->set_attribute( '_root', 'data-fr-modal-inside-query', 'true' );
			} else {
				$this->set_attribute( '_root', 'data-fr-modal-inside-query', 'false' );
			}

			$count = 0;

			if ( class_exists( '\Bricks\Query' ) ) {
				$queryObj = \Bricks\Query::get_query_object();
				if ( $queryObj ) {
					$queryID = $queryObj->element_id;
					$queryID = 'brxe-' . $queryID;

					$this->set_attribute( '_root', 'data-fr-modal-query-id', $queryID );
				}

				if ( $queryObj ) {
					$count = ( intval( $queryObj::get_loop_index() ) );
					$count++;
					// add 1 to count
					// change count to string.
				}
			}
		}//end if

		if ( $count ) {
			$dynamicID = $this->id . '-' . $count;

			/*
			 * 2023-03-13 - MG
			 * Bricks has already declared $this->attributes['_root']['id'] as a string
			 * and the call to $this->set_attribute() is trying to set it as an array.
			 * This causes a Fatal error [] operator not supported for strings.
			 */
			// $this->set_attribute( '_root', 'id', $dynamicID ); // TODO: find a way to set the ID attribute that respects encapsulation.
			$this->attributes['_root']['id'] = $dynamicID;
		}

		$this->set_attribute( '_root', 'class', 'fr-modal' );
		$this->set_attribute( '_root', 'class', 'fr-modal--hide' );
		$this->set_attribute( '_root', 'aria-hidden', 'true' );
		$this->set_attribute( '_root', 'data-fr-modal-trigger', $triggerSelector );
		$this->set_attribute( '_root', 'data-fr-modal-close', $closeSelector );
		$this->set_attribute( '_root', 'data-fr-modal-fade-time', $fadeTime );
		$this->set_attribute( '_root', 'data-fr-modal-video-autoplay', $this->toString( $videoIsAutoplay ) );
		$this->set_attribute( '_root', 'data-fr-modal-scroll', $this->toString( $isScroll ) );
		$this->set_attribute( '_root', 'data-fr-modal-scrollbar', $this->toString( $isScrollBar ) );

		if ( isset( $positionFor ) && 'trigger' === $positionFor ) {
			$this->set_attribute( '_root', 'data-fr-modal-position-for', $positionFor );
			$this->set_attribute( '_root', 'data-fr-modal-position-related-to-trigger', $positionRelatedToTrigger );
			$this->set_attribute( '_root', 'data-fr-modal-place-from-triggers', $placeFromTriggers );
			$this->set_attribute( '_root', 'data-fr-modal-trigger-offset', $offsetFromTrigger );
		}

		if ( $hideModal ) {
			$this->set_attribute( '_root', 'fr-builder', 'hide' );
		}

		// Icon settings.
		if ( ! $icon ) {
			return $this->render_element_placeholder(
				array(
					'title' => esc_html__( 'No icon selected.', 'bricks' ),
				)
			);
		}

		// OUTPUT.
		$output  = "<div {$this->render_attributes( '_root' )}>";
		$output .= '<div class="fr-modal__overlay"></div> <!-- .fr-modal__overlay -->';

		// Icon outside.
		if ( $isCloseButton ) {
			if ( 'outside' === $iconPlacement ) {
				$output .= '<div class="fr-modal__close-icon-wrapper">';
				$output .= '<div class="fr-modal__close-icon" tabindex="0">';
				if ( $icon ) {
					$output .= $icon;
				}

				$output .= '</div> <!-- .fr-modal__close-icon -->';
				$output .= '</div> <!-- .fr-modal__close-icon-wrapper -->';
			}
		}

		$output .= '<div class="fr-modal__body modal-wrapper">';
		$output .= \Bricks\Frontend::render_children( $this ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Icon Inside.
		if ( $isCloseButton ) {
			if ( 'inside' === $iconPlacement ) {
				$output .= '<div class="fr-modal__close-icon-wrapper">';
				$output .= '<div class="fr-modal__close-icon" tabindex="0">';
				if ( $icon ) {
					$output .= $icon;
				}

				$output .= '</div> <!-- .fr-modal__close-icon -->';
				$output .= '</div> <!-- .fr-modal__close-icon-wrapper -->';
			}
		}

		$output .= '</div> <!-- .fr-modal__body -->';
		$output .= '</div> <!-- .fr-modal -->';

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		// log queryID.
	}
}
