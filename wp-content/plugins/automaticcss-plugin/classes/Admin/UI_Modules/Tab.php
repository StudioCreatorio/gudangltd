<?php
/**
 * Automatic.css Tab UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Modules;

use Automatic_CSS\Admin\UI_Elements\Checkbox;
use Automatic_CSS\Admin\UI_Elements\Color;
use Automatic_CSS\Admin\UI_Elements\Hidden;
use Automatic_CSS\Admin\UI_Elements\Number;
use Automatic_CSS\Admin\UI_Elements\Plain_Text;
use Automatic_CSS\Admin\UI_Elements\Select;
use Automatic_CSS\Admin\UI_Elements\Text;
use Automatic_CSS\Admin\UI_Elements\Toggle;
use Automatic_CSS\Admin\UI_Elements\Tooltip;
use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;

/**
 * Tab UI class.
 */
class Tab {

	/**
	 * Render this input.
	 *
	 * @param string $tab_id The tab ID.
	 * @param array  $tab_options The tab options.
	 * @param array  $values The current variable values.
	 * @return void
	 */
	public static function render( $tab_id, $tab_options, $values ) {
		?>
		<div class="acss-tab" id="acss-tab-<?php echo esc_attr( $tab_id ); ?>">
			<header class="acss-tab__header">
				<div class="acss-tab__title-wrapper">
					<?php if ( array_key_exists( 'title', $tab_options ) && '' !== $tab_options['title'] ) : ?>
						<h2 class="acss-tab__title"><?php echo esc_html( $tab_options['title'] ); ?></h2>
					<?php endif; ?>
					<?php if ( array_key_exists( 'description', $tab_options ) && '' !== $tab_options['description'] ) : ?>
						<p class="acss-tab__description"><?php echo wp_kses_post( $tab_options['description'] ); ?></p>
					<?php endif; ?>
				</div>
				<?php foreach ( apply_filters( 'automaticcss_tab_' . $tab_id . '_warnings', array() ) as $warning_id => $warning_options ) : ?>
					<div class="acss-tab__warning">


						<div class="acss-var__info">
							<?php if ( ! empty( $warning_options['text'] ) ) : ?>
								<p class="acss-tab__warning__heading"><?php echo wp_kses_post( $warning_options['text'] ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $warning_options['tooltip'] ) ) : ?>
								<?php Tooltip::render( $warning_options['tooltip'], array( 'text_classes' => 'acss-tab__warning__description' ) ); ?>
							<?php endif; ?>
						</div>

					</div>
				<?php endforeach; ?>
			</header> <!-- .acss-panel__header -->
			<?php if ( ! empty( $tab_options['content'] ) ) : ?>
				<?php foreach ( $tab_options['content'] as $content_id => $content_options ) : ?>
					<?php
					if ( ! isset( $content_options['type'] ) || 'panel' !== $content_options['type'] ) {
						continue; // can't do anything if it's not a panel.
					}
					Panel::render( $content_id, $content_options, $values );
					?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div> <!-- .acss-tab -->
		<?php
	}

}
