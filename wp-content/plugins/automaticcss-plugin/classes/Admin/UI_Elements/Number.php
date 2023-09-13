<?php
/**
 * Automatic.css Number UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Elements;

use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;

/**
 * Number UI class.
 */
class Number extends Base {

	/**
	 * Render this input.
	 *
	 * @return void
	 */
	public function render() {
		$min_str = array_key_exists( 'min', $this->render_options ) && '' !== $this->render_options['min'] ? ' min="' . esc_attr( $this->render_options['min'] ) . '" ' : '';
		$max_str = array_key_exists( 'max', $this->render_options ) && '' !== $this->render_options['max'] ? ' max="' . esc_attr( $this->render_options['max'] ) . '" ' : '';
		$decimals = array_key_exists( 'decimals', $this->render_options ) && '' !== $this->render_options['decimals'] ? (int) $this->render_options['decimals'] : 0;
		$step_str = $decimals > 0 ? ' step="0.' . str_repeat( '0', $decimals - 1 ) . '1" ' : ' step="1" ';
		$this->render_options['extra'] = $step_str . $min_str . $max_str;
		$this->render_wrapper_open();
		printf(
			'<input class="acss-value__input acss-value__input--number" type="number" name="%2$s" id="%2$s" value="%3$s" placeholder="%7$s" data-default="%4$s" %5$s %6$s />',
			esc_attr( $this->render_options['base_name'] ),
			esc_attr( $this->render_options['id'] ),
			esc_attr( $this->render_options['value'] ),
			esc_attr( $this->render_options['default'] ),
			$this->render_options['required'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$this->render_options['extra'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_attr( $this->render_options['placeholder'] )
		);
		$this->render_unit();
		$this->render_wrapper_close();
	}
}
