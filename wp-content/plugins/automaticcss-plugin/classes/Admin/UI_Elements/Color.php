<?php
/**
 * Automatic.css Color UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Elements;

use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;

/**
 * Color UI class.
 */
class Color extends Base {

	/**
	 * Render this input.
	 *
	 * @return void
	 */
	public function render() {
		$this->render_wrapper_open();
		printf(
			'<input class="acss-value__input acss-value__input--color color-picker" type="text" name="%2$s" id="%2$s" value="%3$s" data-default="%4$s" %5$s/>',
			esc_attr( $this->render_options['base_name'] ),
			esc_attr( $this->render_options['id'] ),
			esc_attr( $this->render_options['value'] ),
			esc_attr( $this->render_options['default'] ),
			$this->render_options['required'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
		$this->render_wrapper_close();
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public static function hook_in_automaticcss_render_options_default() {
		add_filter( 'automaticcss_render_options_default', array( __CLASS__, 'automaticcss_render_options_default' ), 10, 4 );
	}

	/**
	 * Fix the default hue and saturation for colors.
	 *
	 * @param string $default The default value.
	 * @param string $var_id The variable ID.
	 * @param array  $var_options The variable options.
	 * @param array  $values The values for all variables.
	 * @return mixed
	 */
	public static function automaticcss_render_options_default( $default, $var_id, $var_options, $values ) {
		$colors = array( 'action', 'primary', 'secondary', 'accent', 'base', 'shade' );
		$modifiers = array( 'hover', 'ultra-light', 'light', 'medium', 'dark', 'ultra-dark' );
		$hsl_modifiers = array( 'h', 's' ); // No l modifier.
		$pattern = '/\b(' . implode( '|', $colors ) . ')-\b(' . implode( '|', $modifiers ) . ')-\b(' . implode( '|', $hsl_modifiers ) . ')/i'; // primary-hover-h, secondary-ultra-light-s, etc.
		if ( preg_match( $pattern, $var_id, $matches ) ) {
			$found_color = $matches[1];
			$found_hsl_param = $matches[3];
			$color_variable = 'color-' . $found_color;
			if ( ! empty( $values[ $color_variable ] ) ) {
				$new_value = ( new \Automatic_CSS\Helpers\Color( $values[ $color_variable ] ) )->$found_hsl_param;
				if ( isset( $new_value ) ) {
					$default = $new_value;
				}
			}
		}
		return $default;
	}
}
