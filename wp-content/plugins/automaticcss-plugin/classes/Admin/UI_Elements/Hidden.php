<?php
/**
 * Automatic.css Hidden UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Elements;

use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;

/**
 * Hidden UI class.
 */
class Hidden extends Base {

	/**
	 * Render this input.
	 *
	 * @return void
	 */
	public function render() {
		// No wrapper call here because this element does not need showing.
		printf(
			'<input class="acss-settings__input" type="hidden" name="%2$s" id="%2$s" value="%3$s"/>',
			esc_attr( $this->render_options['base_name'] ),
			esc_attr( $this->render_options['id'] ),
			esc_attr( $this->render_options['value'] )
		);
		// No wrapper call here because this element does not need showing.
	}
}
