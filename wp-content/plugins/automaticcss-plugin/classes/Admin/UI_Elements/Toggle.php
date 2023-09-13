<?php
/**
 * Automatic.css Toggle UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Elements;

use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;

/**
 * Toggle UI class.
 */
class Toggle extends Base {

	/**
	 * Render this input.
	 *
	 * @return void
	 */
	public function render() {
		if ( empty( $this->render_options['valueon'] ) || empty( $this->render_options['valueoff'] ) ) {
			Logger::log( sprintf( '%s: no valueon (%s) or valueoff (%s) for var_id %s. Quitting.', __METHOD__, $this->render_options['valueon'], $this->render_options['valueoff'], $this->render_options['id'] ), Logger::LOG_LEVEL_ERROR );
			// TODO: error message?
			return;
		}
		$checked_on = $this->render_options['value'] === $this->render_options['valueon'] ? 'checked' : '';
		$checked_off = $this->render_options['value'] === $this->render_options['valueoff'] ? 'checked' : '';
		$this->render_wrapper_open();
		printf(
			'<div class="acss-value__input-wrapper--toggle" data-default="%3$s" data-toggle-name="%2$s">
				<input class="acss-value__input acss-value__input--toggle acss-value__input--toggle-on" type="radio" name="%2$s" id="%2$s-%4$s" value="%4$s" %5$s %8$s/>
				<label class="acss-value__label acss-value__label--toggle acss-value__label--toggle-on" for="%2$s-%4$s">%4$s</label>
				<input class="acss-value__input acss-value__input--toggle acss-value__input--toggle-off" type="radio" name="%2$s" id="%2$s-%6$s" value="%6$s" %7$s %8$s/>
				<label class="acss-value__label acss-value__label--toggle acss-value__label--toggle-off" for="%2$s-%6$s">%6$s</label>
			</div>',
			esc_attr( $this->render_options['base_name'] ),
			esc_attr( $this->render_options['id'] ),
			esc_attr( $this->render_options['default'] ),
			esc_attr( $this->render_options['valueon'] ),
			$checked_on, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_attr( $this->render_options['valueoff'] ),
			$checked_off, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$this->render_options['required'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
		$this->render_wrapper_close();
	}
}
