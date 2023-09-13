<?php
/**
 * Automatic.css Select UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Elements;

use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;

/**
 * Select UI class.
 */
class Select extends Base {

	/**
	 * Render this input.
	 *
	 * @return void
	 */
	public function render() {
		$this->render_wrapper_open();
		printf(
			'<select class="acss-value__input acss-value__input--dropdown" name="%2$s" id="%2$s" data-default="%3$s" %4$s>',
			esc_attr( $this->render_options['base_name'] ),
			esc_attr( $this->render_options['id'] ),
			esc_attr( $this->render_options['default'] ),
			$this->render_options['required'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
		if ( ! empty( $this->render_options['options'] ) && is_array( $this->render_options['options'] ) ) {
			foreach ( $this->render_options['options'] as $option_key => $option_value ) {
				// TODO: this needs to happen when saving the value to the database.
				$compare_value = $this->render_options['value'];
				switch ( gettype( $option_value ) ) {
					case 'double':
						$compare_value = floatval( $this->render_options['value'] );
						break;
					case 'integer':
						$compare_value = intval( $this->render_options['value'] );
						break;
				}
				$selected = $option_value === $compare_value ? ' selected="selected"' : '';
				printf(
					'<option class="acss-value__input--option" value="%1$s" data-option-name="%2$s" %3$s>%2$s</option>',
					esc_attr( $option_value ),
					esc_html( $option_key ),
					$selected // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
			}
		}
		echo '</select>';
		$this->render_wrapper_close();
	}
}
