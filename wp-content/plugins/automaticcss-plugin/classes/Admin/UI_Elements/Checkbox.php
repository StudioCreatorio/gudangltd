<?php
/**
 * Automatic.css Checkbox UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Elements;

use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;

/**
 * Checkbox UI class.
 */
class Checkbox extends Base {

	/**
	 * Render this input.
	 *
	 * @return void
	 */
	public function render() {
		$value_on = 'on';
		$checked = $this->render_options['value'] === $value_on ? 'checked' : '';
		$this->render_wrapper_open();
		printf(
			'<input class="acss-settings__input" type="checkbox" name="%2$s" id="%2$s" value="%3$s" %4$s %5$s/>',
			esc_attr( $this->render_options['base_name'] ),
			esc_attr( $this->render_options['id'] ),
			esc_attr( $value_on ),
			$this->render_options['required'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$checked // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
		$this->render_wrapper_close();
	}
}
