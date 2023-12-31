<?php
/**
 * Automatic.css Tooltip UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Elements;

use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;

/**
 * Tooltip UI class.
 */
class Tooltip {
	/**
	 * Render an info tooltip with icon
	 *
	 * @param string $message The message for the tooltip.
	 * @param array  $render_options Additional optional render options.
	 * @return void
	 */
	public static function render( $message, $render_options = array() ) {
		$icon_classes = array_key_exists( 'icon_classes', $render_options ) ? $render_options['icon_classes'] : '';
		$text_classes = array_key_exists( 'text_classes', $render_options ) ? $render_options['text_classes'] : '';
		?>
		<div class="acss-info-tooltip">
			<svg class="acss-info-tooltip__icon <?php echo esc_attr( $icon_classes ); ?>" width="9" height="10" viewBox="0 0 9 10" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M7.5 0.375H1.25C0.546875 0.375 0 0.941406 0 1.625V7.875C0 8.57812 0.546875 9.125 1.25 9.125H7.5C8.18359 9.125 8.75 8.57812 8.75 7.875V1.625C8.75 0.941406 8.18359 0.375 7.5 0.375ZM4.375 2.25C4.70703 2.25 5 2.54297 5 2.875C5 3.22656 4.70703 3.5 4.375 3.5C4.02344 3.5 3.75 3.22656 3.75 2.875C3.75 2.54297 4.02344 2.25 4.375 2.25ZM5.15625 7.25H3.59375C3.32031 7.25 3.125 7.05469 3.125 6.78125C3.125 6.52734 3.32031 6.3125 3.59375 6.3125H3.90625V5.0625H3.75C3.47656 5.0625 3.28125 4.86719 3.28125 4.59375C3.28125 4.33984 3.47656 4.125 3.75 4.125H4.375C4.62891 4.125 4.84375 4.33984 4.84375 4.59375V6.3125H5.15625C5.41016 6.3125 5.625 6.52734 5.625 6.78125C5.625 7.05469 5.41016 7.25 5.15625 7.25Z" fill="#707070" />
			</svg>
			<p class="acss-info-tooltip__text <?php echo esc_attr( $text_classes ); ?>"><?php echo wp_kses_post( $message ); ?></p>
		</div>
		<?php
	}
}
