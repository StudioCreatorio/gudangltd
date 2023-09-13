<?php
/**
 * Automatic.css Panel UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Modules;

use Automatic_CSS\Admin\UI_Elements\Base;
use Automatic_CSS\Admin\UI_Elements\Button;
use Automatic_CSS\Admin\UI_Elements\Tooltip;
use Automatic_CSS\Helpers\Logger;

/**
 * Panel UI class.
 */
class Panel {

	/**
	 * Render this input.
	 *
	 * @param string $panel_id The panel ID.
	 * @param array  $panel_options The panel options.
	 * @param array  $values The current variable values.
	 * @return void
	 */
	public static function render( $panel_id, $panel_options, $values ) {
		$variable_class = ! empty( $panel_options['variable'] ) ? ' acss-panel-inner--has-variable' : '';
		?>
		<div class="acss-panel" id="acss-panel-<?php echo esc_attr( $panel_id ); ?>">
			<div class="acss-panel-inner<?php echo esc_attr( $variable_class ); ?>">
				<header class="acss-panel__header acss-var__header">

					<div class="acss-var__info">
						<?php if ( ! empty( $panel_options['title'] ) ) : ?>
							<h3 class="acss-panel__title"><?php echo esc_html( $panel_options['title'] ); ?></h3>
						<?php endif; ?>
						<?php if ( ! empty( $panel_options['tooltip'] ) ) : ?>
							<?php Tooltip::render( $panel_options['tooltip'] ); ?>
						<?php endif; ?>
					</div>

				</header> <!-- .acss-panel__header -->
				<?php if ( ! empty( $panel_options['description'] ) ) : ?>
					<p class="acss-var__info__description"><?php echo wp_kses_post( $panel_options['description'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $panel_options['variable'] ) ) : ?>
					<?php
					// Content is a variable here. Naming like these for clarity.
					$var_id = $panel_options['variable'];
					Base::render_variable( $var_id, array(), $values );
					?>
				<?php endif; ?>
			</div> <!-- .acss-panel-inner -->
			<?php if ( ! empty( $panel_options['content'] ) ) : ?>
				<?php foreach ( $panel_options['content'] as $content_id => $content_options ) : ?>
					<?php
					if ( ! isset( $content_options['type'] ) ) {
						continue; // can't do anything if I don't know the type.
					}
					// Panels can only fit accordions, dividers, groups and variables.
					if ( 'accordion' === $content_options['type'] ) {
						Accordion::render( $content_id, $content_options, $values );
					} else if ( 'divider' === $content_options['type'] ) {
						Divider::render( $content_id, $content_options );
					} else if ( 'group' === $content_options['type'] ) {
						Group::render( $content_id, $content_options, $values );
					} else if ( 'button' === $content_options['type'] ) {
						Button::render( $content_id, $content_options, $values );
					} else if ( 'variable' === $content_options['type'] ) {
						// Content is a variable here. Naming like these for clarity.
						$var_id = $content_id;
						$var_options = $content_options;
						Base::render_variable( $var_id, $var_options, $values );
					}
					?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div> <!-- .acss-panel -->
		<?php
	}
}
