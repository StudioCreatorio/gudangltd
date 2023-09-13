<?php
/**
 * Automatic.css Text UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Elements;

use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;

/**
 * Text UI class.
 */
class Text extends Base {

	/**
	 * Render this input.
	 *
	 * @return void
	 */
	public function render() {
		$this->render_wrapper_open();
		printf(
			'<input	class="acss-value__input" type="text" name="%2$s" id="%2$s" value="%3$s" placeholder="%6$s" data-default="%4$s" %5$s />',
			esc_attr( $this->render_options['base_name'] ),
			esc_attr( $this->render_options['id'] ),
			esc_attr( $this->render_options['value'] ),
			esc_attr( $this->render_options['default'] ),
			$this->render_options['required'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_attr( $this->render_options['placeholder'] )
		);
		$this->render_unit();
		$this->render_wrapper_close();
	}
}
