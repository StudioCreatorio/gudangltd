<?php
/**
 * Automatic.css Group UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Modules;

use Automatic_CSS\Admin\UI_Elements\Base;
use Automatic_CSS\Admin\UI_Elements\Button;
use Automatic_CSS\Admin\UI_Elements\Tooltip;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Model\Config\Variables;

/**
 * Group UI class.
 */
class Group {

	/**
	 * Render this input.
	 *
	 * @param string $group_id The group ID.
	 * @param array  $group_options The group options.
	 * @param array  $values The current variable values.
	 * @return void
	 */
	public static function render( $group_id, $group_options, $values ) {
		$has_changed_class = '';
		if ( ! empty( $group_options['content'] ) ) {
			foreach ( $group_options['content'] as $var_id => $var_options ) {
				if ( ! array_key_exists( 'type', $var_options ) || 'variable' !== $var_options['type'] ) {
					continue;
				}
				$var_options = ( new Variables() )->get_variable_options( $var_id );
				$default_value = is_array( $var_options ) && array_key_exists( 'default', $var_options ) && array_key_exists( $var_id, $values ) ?
					apply_filters( 'automaticcss_render_options_default', $var_options['default'], $var_id, $var_options, $values ) :
					null;
				if ( null !== $default_value && $default_value != $values[ $var_id ] ) {
					$has_changed_class = ' acss-group--changed';
					break;
				}
			}
		}
		?>
		<div class="acss-group<?php echo esc_attr( $has_changed_class ); ?>" id="acss-group-<?php echo esc_attr( $group_id ); ?>">
			<header class="acss-var__header">

				<div class="acss-var__info">
					<?php if ( ! empty( $group_options['title'] ) ) : ?>
						<h4 class="acss-group__title"><?php echo esc_html( $group_options['title'] ); ?></h4>
					<?php endif; ?>
					<?php if ( ! empty( $group_options['tooltip'] ) ) : ?>
						<?php Tooltip::render( $group_options['tooltip'] ); ?>
					<?php endif; ?>

				</div>

			</header>
			<?php if ( ! empty( $group_options['content'] ) ) : ?>
				<?php foreach ( $group_options['content'] as $content_id => $content_options ) : ?>
					<?php
					if ( ! isset( $content_options['type'] ) ) {
						continue; // can't do anything if I don't know the type.
					}
					if ( 'button' === $content_options['type'] ) {
						// Content is a button here. Naming like these for clarity.
						$button_id = $content_id;
						$button_options = $content_options;
						$button_options['render_wrapper'] = false;
						Button::render( $button_id, $button_options, $values );
					} else if ( 'variable' === $content_options['type'] ) {
						// Content is a variable here. Naming like these for clarity.
						$var_id = $content_id;
						$var_options = $content_options;
						Base::render_variable( $var_id, $var_options, $values, false );
					}
					?>
				<?php endforeach; ?>
			<?php endif; ?>
				<footer>
					<p class="acss-group__error_message"></p>
				</footer>
		</div> <!-- .acss-group -->
		<?php
	}
}
