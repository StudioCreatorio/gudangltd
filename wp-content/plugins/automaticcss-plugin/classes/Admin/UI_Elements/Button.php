<?php
/**
 * Automatic.css Button UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Elements;

use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;

/**
 * Button UI class.
 */
class Button {

	/**
	 * Render this input.
	 *
	 * @param string $button_id The button ID.
	 * @param array  $button_options The button options.
	 * @param array  $values The current variable values.
	 * @return void
	 */
	public static function render( $button_id, $button_options, $values ) {
		$render_wrapper = array_key_exists( 'render_wrapper', $button_options ) ? (bool) $button_options['render_wrapper'] : true;
		$button_title = array_key_exists( 'title', $button_options ) ? $button_options['title'] : '';
		$button_label = array_key_exists( 'label', $button_options ) ? $button_options['label'] : $button_title;
		$button_tooltip = array_key_exists( 'tooltip', $button_options ) ? $button_options['tooltip'] : '';
		?>
		<?php if ( $render_wrapper ) : ?>
		<div class="acss-button">
			<header class="acss-button__header">
			<?php if ( ! empty( $button_title ) ) : ?>
				<h4 class="acss-button__title"><?php echo esc_html( $button_title ); ?></h4>
		<?php endif; ?>
			<?php if ( ! empty( $button_tooltip ) ) : ?>
				<?php Tooltip::render( $button_tooltip ); ?>
		<?php endif; ?>
			</header>
		<?php endif; ?>
			<div class="acss-button__wrapper">
				<button type="button" class="acss-button__button" name="<?php esc_attr( $button_id ); ?>" id="<?php echo esc_attr( $button_id ); ?>">
					<?php echo esc_html( $button_label ); ?>
				</button>
			</div>
		<?php if ( $render_wrapper ) : ?>
		</div><!-- .acss-button -->
		<?php endif; ?>
		<?php
	}
}
