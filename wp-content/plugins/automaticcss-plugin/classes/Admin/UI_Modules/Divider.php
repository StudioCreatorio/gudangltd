<?php
/**
 * Automatic.css Divider UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Modules;

use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;

/**
 * Divider UI class.
 */
class Divider {

	/**
	 * Render this input.
	 *
	 * @param string $divider_id The divider ID.
	 * @param array  $divider_options The divider options.
	 * @return void
	 */
	public static function render( $divider_id, $divider_options ) {
		?>
		<div class="acss-divider" id="acss-divider-<?php echo esc_attr( $divider_id ); ?>">
		<?php if ( ! empty( $divider_options['content'] ) ) : ?>
			<?php echo wp_kses_post( $divider_options['content'] ); ?>
		<?php endif; ?>
		</div> <!-- .acss-divider -->
		<?php
	}
}
