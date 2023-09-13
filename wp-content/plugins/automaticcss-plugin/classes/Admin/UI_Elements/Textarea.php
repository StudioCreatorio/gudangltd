<?php
/**
 * Automatic.css Textarea UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Elements;

use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;

/**
 * Textarea UI class.
 */
class Textarea extends Base {

	/**
	 * Constructor
	 *
	 * @param string $var_id Variable ID.
	 * @param array  $var_options Variable's options.
	 * @param string $var_value Variable's current value.
	 */
	public function __construct( $var_id, $var_options, $var_value = null ) {
		$var_options['var_classes'] = 'acss-var--textarea'; // Needed for styling textarea wrappers on two rows.
		parent::__construct( $var_id, $var_options, $var_value );
	}

	/**
	 * Render this input.
	 *
	 * @return void
	 */
	public function render() {
		$this->render_wrapper_open();
		printf(
			'<textarea class="acss-value__input acss-value__input--textarea" name="%2$s" id="%2$s" placeholder="%6$s" data-default="%4$s" %5$s>%3$s</textarea>',
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
