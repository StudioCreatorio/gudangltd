<?php
/**
 * Automatic.css Accordion UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Modules;

use Automatic_CSS\Admin\UI_Elements\Base;
use Automatic_CSS\Helpers\Logger;

/**
 * Accordion UI class.
 */
class Accordion {


	/**
	 * Render this input.
	 *
	 * @param string $accordion_id The accordion ID.
	 * @param array  $accordion_options The accordion options.
	 * @param array  $values The current variable values.
	 * @return void
	 */
	public static function render( $accordion_id, $accordion_options, $values ) {
		?>
		<div class="acss-accordion" id="acss-accordion-<?php echo esc_attr( $accordion_id ); ?>">
			<div class="acss-accordion__header">
				<?php if ( ! empty( $accordion_options['title'] ) ) : ?>
					<h4 class="acss-accordion__title"><?php echo esc_html( $accordion_options['title'] ); ?></h4>
				<?php endif; ?>
				<img class="acss-accordion__icon" src="<?php echo esc_url( ACSS_ASSETS_URL . '/img/arrow-down-2.svg' ); ?>" />
			</div> <!-- .acss-accordion__header -->
			<div class="acss-accordion__content-wrapper">
				<div class="acss-accordion__content">
					<?php if ( ! empty( $accordion_options['description'] ) ) : ?>
						<p class="acss-accordion__description"><?php echo wp_kses_post( $accordion_options['description'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $accordion_options['content'] ) ) : ?>
						<?php foreach ( $accordion_options['content'] as $content_id => $content_options ) : ?>
							<?php
							if ( ! isset( $content_options['type'] ) ) {
								continue; // can't do anything if I don't know the type.
							}
							// Accordions can only fit dividers, groups and variables.
							if ( 'divider' === $content_options['type'] ) {
								Divider::render( $content_id, $content_options );
							} else if ( 'group' === $content_options['type'] ) {
								Group::render( $content_id, $content_options, $values );
							} else if ( 'variable' === $content_options['type'] ) {
								// Content is a variable here. Naming like these for clarity.
								$var_id = $content_id;
								$var_options = $content_options;
								Base::render_variable( $var_id, $var_options, $values );
							}
							?>
						<?php endforeach; ?>
					<?php endif; ?>
				</div> <!-- .acss-accordion__content -->
			</div> <!-- .acss-accordion__content-wrapper -->
		</div> <!-- .acss-accordion -->
		<?php
	}
}
