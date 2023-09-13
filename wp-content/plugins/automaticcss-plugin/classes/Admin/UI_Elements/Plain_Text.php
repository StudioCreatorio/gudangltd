<?php
/**
 * Automatic.css Plain_Text UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Elements;

use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;

/**
 * Plain_Text UI class.
 */
class Plain_Text extends Base {

	/**
	 * Render this input.
	 *
	 * @return void
	 */
	public function render() {
		if ( ! array_key_exists( 'content', $this->render_options ) || '' === $this->render_options['content'] ) {
			// TODO: error message?
			return;
		}
		$click_to_copy_class = ! empty( $this->render_options['click_to_copy'] ) && true === (bool) $this->render_options['click_to_copy'] ? 'acss-copy-to-clipboard' : '';
		$this->render_wrapper_open();
		printf(
			'<p class="acss-settings__input %4$s" name="%2$s" id="%2$s" data-content="%3$s">%3$s</p>',
			esc_attr( $this->render_options['base_name'] ),
			esc_attr( $this->render_options['id'] ),
			wp_kses_post( $this->render_options['content'] ),
			esc_attr( $click_to_copy_class )
		);
		$this->render_wrapper_close();
	}
}
