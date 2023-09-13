<?php
/**
 * Table of Contents Widget.
 *
 * @package Frames_Client
 */

namespace Frames_Client\Widgets\Table_Of_Contents;

use Frames_Client\Widget_Manager;
use Frames_Client\Widgets\Table_Of_Contents\Inc\Table_Of_Contents_Functions;

/**
 * Table of Contents class.
 */
class Table_Of_Contents_Widget extends \Bricks\Element {



	/**
	 * Element properties
	 *
	 * @since 1.0.0
	 * @access public
	 */

	/**
	 * Use predefined element category 'general'.
	 *
	 * @var string
	 */
	public $category     = 'Frames';

	/**
	 * Make sure to prefix your elements.
	 *
	 * @var string
	 */
	public $name         = 'fr-table-of-contents';

	/**
	 * Themify icon font class.
	 *
	 * @var string
	 */
	public $icon         = 'fas fa-list-ol';

	/**
	 * Default CSS selector.
	 *
	 * @var string
	 */
	public $css_selector = '.table-of-contents-wrapper';

	/**
	 * Scripts to be enqueued.
	 *
	 * @var array
	 */
	public $scripts = array( 'table_of_contents_script' );

	/**
	 * Is nestable.
	 *
	 * @var boolean
	 */
	public $nestable = false;

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function get_methods() {
		 include( 'inc/table-of-contents-functions.php' );
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
		return esc_html__( 'Table of Contents', 'frames' );
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

		$this->control_groups['frTocSettings'] = array(
			'title' => esc_html__( 'Settings', 'frames' ),
			'tab' => 'content',
		);

		$this->control_groups['frTocAccordionSettings'] = array(
			'title' => esc_html__( 'Accordion Settings', 'frames' ),
			'tab' => 'content',
		);

		$this->control_groups['frTocAccordionHeaderStyling'] = array(
			'title' => esc_html__( 'Header Styling', 'frames' ),
			'tab' => 'content',
		);

		$this->control_groups['frTocAccordionBodyStyling'] = array(
			'title' => esc_html__( 'Table of Contents Styling', 'frames' ),
			'tab' => 'content',
		);

		$this->control_groups['frTocActiveHeadingStyling'] = array(
			'title' => esc_html__( 'Active Heading Styling', 'frames' ),
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

		$this->controls['frTocContentSelector'] =
			array(
				'group' => 'frTocSettings',
				'label' => __( 'Content Target (Class or ID)', 'frames' ),
				'type' => 'text',
				'default' => '',
				'inlineEditing' => true,
			);

		$this->controls['frTocHeaderText'] =
			array(
				'group' => 'frTocSettings',
				'label' => __( 'TOC Header Text', 'frames' ),
				'type' => 'text',
				'default' => 'Table of Contents',
				'inlineEditing' => true,
			);

		$this->controls['frTocShowHeadingUpTo'] = array(
			'group' => 'frTocSettings',
			'label' => esc_html__( 'Show Heading up to', 'frames' ),
			'type' => 'select',
			'options' => array(
				'h2'  => 'h2',
				'h3'  => 'h3',
				'h4'  => 'h4',
				'h5'  => 'h5',
				'h6'  => 'h6',
			),
			'inline' => true,
			'default' => 'h3',
		);

		$this->controls['frTocListType'] = array(
			'group' => 'frTocSettings',
			'label' => esc_html__( 'List type', 'frames' ),
			'type' => 'select',
			'options' => array(
				'decimal'  => '1, 2',
				'lower-alpha'  => 'a, b',
				'none'  => 'None',
			),
			'inline' => true,
			'default' => 'decimal',
		);

		$this->controls['frTocSubListType'] = array(
			'group' => 'frTocSettings',
			'label' => esc_html__( 'Sub-list type', 'frames' ),
			'type' => 'select',
			'options' => array(
				'decimal'  => '1, 2',
				'lower-alpha'  => 'a, b',
				'none'  => 'None',
			),
			'inline' => true,
			'default' => 'lower-alpha',
		);

		$this->controls['frTocScrollOffset'] =
			array(
				'group' => 'frTocSettings',
				'label' => __( 'Scroll Offset', 'frames' ),
				'type' => 'number',
				'min' => 0,
				'max' => 10,
				'units' => true,
				'step' => 1,
				'inline' => true,
				'default' => '50'
			);

		$this->controls['frTocUseAccordion'] =
			array(
				'group' => 'frTocAccordionSettings',
				'label' => __( 'Use accordion', 'frames' ),
				'type' => 'checkbox',
				'inline' => true,
				'default' => true
			);

		$this->controls['frTocAccordionIsOpen'] =
			array(
				'group' => 'frTocAccordionSettings',
				'label' => __( 'Accordion is open', 'frames' ),
				'type' => 'checkbox',
				'inline' => true,
				'default' => true
			);

		$this->controls['frTocAccHeaderBackgroundColor'] =
			array(
				'group' => 'frTocAccordionHeaderStyling',
				'label' => __( 'Background Color', 'frames' ),
				'type' => 'color',
				'default' => array(
					'rgb' => 'var(--shade-ultra-dark)',
				),
				'css'   => array(
					array(
						'property' => 'background-color',
						'selector' => '.fr-toc__header',
					),
				),
			);

		$this->controls['frTocAccHeaderTextColor'] =
			array(
				'group' => 'frTocAccordionHeaderStyling',
				'label' => __( 'Text Color', 'frames' ),
				'type' => 'color',
				'default' => array(
					'rgb' => 'var(--shade-white)',
				),
				'css'   => array(
					array(
						'property' => 'color',
						'selector' => '.fr-toc__heading',
					),
				),
			);

		$this->controls['frTocAccHeaderTypography'] =
			array(
				'group' => 'frTocAccordionHeaderStyling',
				'label' => __( 'Typography', 'frames' ),
				'type' => 'typography',
				'css' => array(
					array(
						'property' => 'typography',
						'selector' => '.fr-toc__heading',
					),
				),
				'inline' => true,
			);

		$this->controls['frTocAccHeaderBorder'] =
			array(
				'group' => 'frTocAccordionHeaderStyling',
				'label' => __( 'Border', 'frames' ),
				'type' => 'border',
				'default' => '',
				'inlineEditing' => true,
				'css'   => array(
					array(
						'property' => 'border',
						'selector' => '.fr-toc__header',
					),
				),
			);

		$this->controls['frTocAccHeaderPadding'] =
			array(
				'group' => 'frTocAccordionHeaderStyling',
				'label' => __( 'Padding', 'frames' ),
				'type' => 'dimensions',
				'css' => array(
					array(
						'property' => 'padding',
						'selector' => '.fr-toc__header',
					),
				),
				'default' => array(
					'top' => 'var(--space-xs)',
					'right' => 'var(--space-xs)',
					'bottom' => 'var(--space-xs)',
					'left' => 'var(--space-xs)',
				),
			);

		$this->controls['frTocAccordionArrowIcon'] =
			array(
				'group' => 'frTocAccordionHeaderStyling',
				'label' => __( 'Arrow Icon', 'frames' ),
				'type' => 'icon',
				'default' => array(
					'library' => 'themify',
					'icon' => 'ti-angle-down',
				),
			);

		$this->controls['frTocAccHeaderIconColor'] =
			array(
				'group' => 'frTocAccordionHeaderStyling',
				'label' => __( 'Icon Color', 'frames' ),
				'type' => 'color',
				'default' => array(
					'rgb' => 'var(--base-ultra-light)',
				),
				'css'   => array(
					array(
						'property' => 'color',
						'selector' => '.fr-toc__icon',
					),
				),
			);

		$this->controls['frTocAccHeaderIconBackgroundColor'] =
			array(
				'group' => 'frTocAccordionHeaderStyling',
				'label' => __( 'Icon Background Color', 'frames' ),
				'type' => 'color',
				'default' => array(
					'rgb' => 'rgba(0,0,0,.0)',
				),
				'css'   => array(
					array(
						'property' => 'background-color',
						'selector' => '.fr-toc__icon',
					),
				),
			);

		$this->controls['frTocAccHeaderIconSize'] =
			array(
				'group' => 'frTocAccordionHeaderStyling',
				'label' => __( 'Icon Size', 'frames' ),
				'type' => 'number',
				'min' => 0,
				'max' => 99999,
				'step' => 1,
				'units' => false,
				'inline' => true,
				'default' => 'var(--text-m)',
				'css'   => array(
					array(
						'property' => 'font-size',
						'selector' => '.fr-toc__icon',
					),
				),
			);

		$this->controls['frTocAccHeaderIconPadding'] =
			array(
				'group' => 'frTocAccordionHeaderStyling',
				'label' => __( 'Icon Background Size', 'frames' ),
				'type' => 'number',
				'min' => 0,
				'max' => 9999999,
				'step' => 1,
				'units' => true,
				'inline' => true,
				'default' => '',
				'css'   => array(
					array(
						'property' => 'width',
						'selector' => '.fr-toc__icon',
					),
				),
			);

		$this->controls['frTocAccHeaderIconBorder'] =
			array(
				'group' => 'frTocAccordionHeaderStyling',
				'label' => __( 'Icon Border', 'frames' ),
				'type' => 'border',
				'default' => '',
				'inlineEditing' => true,
				'css'   => array(
					array(
						'property' => 'border',
						'selector' => '.fr-toc__icon',
					),
				),
			);

		$this->controls['frTocBodyBackgroundColor'] =
			array(
				'group' => 'frTocAccordionBodyStyling',
				'label' => __( 'Background Color', 'frames' ),
				'type' => 'color',
				'default' => array(
					'rgb' => 'var(--shade-ultra-light)',
				),
				'css'   => array(
					array(
						'property' => 'background-color',
						'selector' => '.fr-toc__list-wrapper',
					),
				),
			);

		$this->controls['frTocBodyPadding'] =
			array(
				'group' => 'frTocAccordionBodyStyling',
				'label' => __( 'Padding', 'frames' ),
				'type' => 'dimensions',
				'css' => array(
					array(
						'property' => 'padding',
						'selector' => '.fr-toc__list-wrapper',
					),
				),
				'default' => array(
					'top' => 'var(--space-xs)',
					'right' => 'var(--space-xs)',
					'bottom' => 'var(--space-xs)',
					'left' => 'var(--space-xs)',
				),
			);

		$this->controls['frTocBodyItemColor'] =
			array(
				'group' => 'frTocAccordionBodyStyling',
				'label' => __( 'Item Color', 'frames' ),
				'type' => 'color',
				'default' => array(
					'rgb' => 'var(--shade-ultra-dark)',
				),
				'css'   => array(
					array(
						'property' => 'color',
						'selector' => '.fr-toc__list-link',
					),
				),
			);

		$this->controls['frTocBodyItemColorHover'] =
			array(
				'group' => 'frTocAccordionBodyStyling',
				'label' => __( 'Item Color Hover', 'frames' ),
				'type' => 'color',
				'default' => array(
					'rgb' => 'var(--action-hover)',
				),
				'css'   => array(
					array(
						'property' => 'color',
						'selector' => '.fr-toc__list-link:hover',
					),
				),
			);

		$this->controls['frTocBodyItemTypography'] =
			array(
				'group' => 'frTocAccordionBodyStyling',
				'label' => __( 'Item Typography', 'frames' ),
				'type' => 'typography',
				'css' => array(
					array(
						'property' => 'typography',
						'selector' => '.fr-toc__list-link',
					),
				),
				'inline' => true,
			);

		$this->controls['frTocBodyItemPadding'] =
			array(
				'group' => 'frTocAccordionBodyStyling',
				'label' => __( 'Item Padding', 'frames' ),
				'type' => 'dimensions',
				'css' => array(
					array(
						'property' => 'padding',
						'selector' => '.fr-toc__list-link',
					),
				),
				'default' => array(
					'top' => '.5em',
					'right' => '.5em',
					'bottom' => '.5em',
					'left' => '.5em',
				),
			);

		$this->controls['frTocBodyItemGap'] =
			array(
				'group' => 'frTocAccordionBodyStyling',
				'label' => __( 'Gap', 'frames' ),
				'type' => 'number',
				'min' => 0,
				'max' => 9999,
				'step' => 1,
				'units' => true,
				'inline' => true,
				'default' => 'var(--space-xs)',
				'css'   => array(
					array(
						'property' => 'gap',
						'selector' => '.fr-toc__list',
					),
				),
			);

		$this->controls['frTocActiveBackgroundColor'] =
			array(
				'group' => 'frTocActiveHeadingStyling',
				'label' => __( 'Background Color', 'frames' ),
				'type' => 'color',
				'default' => array(
					'rgb' => 'var(--action-trans-20)',
				),
				'css'   => array(
					array(
						'property' => 'background-color',
						'selector' => '.fr-toc__list-link--active',
					),
				),
			);

		$this->controls['typographyfrTocActiveTypography'] =
			array(
				'group' => 'frTocActiveHeadingStyling',
				'label' => __( 'Typography', 'frames' ),
				'type' => 'typography',
				'css' => array(
					array(
						'property' => 'typography',
						'selector' => '.fr-toc__list-link--active',
					),
				),
				'inline' => true,
			);

		$this->controls['frTocActiveBorder'] =
			array(
				'group' => 'frTocActiveHeadingStyling',
				'label' => __( 'Border', 'frames' ),
				'type' => 'border',
				'default' => '',
				'inlineEditing' => true,
				'css'   => array(
					array(
						'property' => 'border',
						'selector' => '.border',
					),
				),
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
		$filename = 'table-of-contents';
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
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	public function render() {
		$this->get_methods();

		$settings = $this->settings;
		$uniqueID = 'bwc-' . uniqid();
		echo "<div {$this->render_attributes('_root')}>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$table_of_contents_functions = new Table_Of_Contents_Functions();
		?>
		<?php if ( isset( $settings[ str_replace( ' ', '', 'frTocContentSelector' ) ] ) && true === (bool) $settings[ str_replace( ' ', '', 'frTocContentSelector' ) ] ) : ?>
			<nav class="fr-toc" aria-label="<?php echo isset( $settings[ str_replace( ' ', '', 'frTocHeaderText' ) ] ) ? wp_kses_post( $settings[ str_replace( ' ', '', 'frTocHeaderText' ) ] ) : ''; ?>" data-fr-toc-content-selector="<?php echo isset( $settings[ str_replace( ' ', '', 'frTocContentSelector' ) ] ) ? wp_kses_post( $settings[ str_replace( ' ', '', 'frTocContentSelector' ) ] ) : ''; ?>" data-fr-toc-scroll-offset="<?php echo isset( $settings[ str_replace( ' ', '', 'frTocScrollOffset' ) ] ) ? wp_kses_post( $settings[ str_replace( ' ', '', 'frTocScrollOffset' ) ] ) : ''; ?>" data-fr-toc-list-type="<?php echo isset( $settings[ str_replace( ' ', '', 'frTocListType' ) ] ) ? wp_kses_post( $settings[ str_replace( ' ', '', 'frTocListType' ) ] ) : ''; ?>" data-fr-toc-sublist-type="<?php echo isset( $settings[ str_replace( ' ', '', 'frTocSubListType' ) ] ) ? wp_kses_post( $settings[ str_replace( ' ', '', 'frTocSubListType' ) ] ) : ''; ?>" data-fr-toc-accordion="<?php echo isset( $settings[ str_replace( ' ', '', 'frTocUseAccordion' ) ] ) ? 'true' : 'false'; ?>" data-fr-toc-heading="<?php echo isset( $settings[ str_replace( ' ', '', 'frTocShowHeadingUpTo' ) ] ) ? wp_kses_post( $settings[ str_replace( ' ', '', 'frTocShowHeadingUpTo' ) ] ) : ''; ?>">
				<?php if ( isset( $settings[ str_replace( ' ', '', 'frTocUseAccordion' ) ] ) && true === (bool) $settings[ str_replace( ' ', '', 'frTocUseAccordion' ) ] ) : ?>
					<button class="fr-toc__header fr-toc__header"
					<?php
					if ( isset( $settings[ str_replace( ' ', '', 'frTocAccordionIsOpen' ) ] ) && true === (bool) $settings[ str_replace( ' ', '', 'frTocAccordionIsOpen' ) ] ) :
						?>
																												 aria-expanded="true"
																												 <?php
																												else :
																													?>
																		 aria-expanded="false" <?php endif; ?>>
						<span class="fr-toc__heading"><?php echo isset( $settings[ str_replace( ' ', '', 'frTocHeaderText' ) ] ) ? wp_kses_post( $settings[ str_replace( ' ', '', 'frTocHeaderText' ) ] ) : ''; ?></span>
						<div class="fr-toc__icon">
							<?php
							if ( isset( $this->settings[ str_replace( ' ', '', 'frTocAccordionArrowIcon' ) ] ) ) {
								echo self::render_icon( $settings[ str_replace( ' ', '', 'frTocAccordionArrowIcon' ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</div>
					</button>
				<?php else : ?>
					<div class="fr-toc__header fr-toc__header">
						<span class="fr-toc__heading"><?php echo isset( $settings[ str_replace( ' ', '', 'frTocHeaderText' ) ] ) ? wp_kses_post( $settings[ str_replace( ' ', '', 'frTocHeaderText' ) ] ) : ''; ?></span>
					</div>
				<?php endif; ?>
				<div class="fr-toc__body fr-toc__body">
					<div class="fr-toc__list-wrapper fr-toc__list-wrapper">
						<ol class="fr-toc__list fr-toc__list">
							<li class="fr-toc__item fr-toc__list-item">
								<a class="fr-toc__link fr-toc__list-link"></a>
							</li>
						</ol>
					</div>

				</div>
			</nav>
		<?php else : ?>
			<p class="width--full text--l bg--shade-ultra-light text--black center--all pad--xl">Choose a selector</p>
		<?php endif; ?>


		<?php

		echo '</div>';
	}
}
