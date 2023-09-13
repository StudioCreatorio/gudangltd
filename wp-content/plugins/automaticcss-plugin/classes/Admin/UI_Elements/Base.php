<?php
/**
 * Automatic.css Base UI file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Admin\UI_Elements;

use Automatic_CSS\Exceptions\Invalid_Variable;
use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Model\Config\Variables;

/**
 * Base UI class.
 */
abstract class Base {

	/**
	 * Enable rendering the wrapper around the element.
	 *
	 * @var boolean
	 */
	private $enable_outside_wrapper_rendering = false;

	/**
	 * Render options.
	 *
	 * @var array
	 */
	protected $render_options = array();

	/**
	 * Constructor
	 *
	 * @param string $var_id Variable ID.
	 * @param array  $var_options Variable's options.
	 * @param string $var_value Variable's current value.
	 */
	public function __construct( $var_id, $var_options, $var_value = null ) {
		$this->render_options = $var_options;
		$this->render_options['base_name'] = 'automatic_css_settings';
		$this->render_options['id'] = $var_id;
		$this->render_options['value'] = null !== $var_value ? $var_value : '';
		$this->render_options['default'] = array_key_exists( 'default', $var_options ) ? $var_options['default'] : '';
		$this->render_options['placeholder'] = array_key_exists( 'placeholder', $var_options ) ? $var_options['placeholder'] : '';
		$this->render_options['var_classes'] = array_key_exists( 'var_classes', $var_options ) ? $var_options['var_classes'] : '';
		// Additional classes.
		$is_hidden = array_key_exists( 'hidden', $var_options ) ? (bool) $var_options['hidden'] : false;
		$is_changed = $var_value != $var_options['default'];
		$this->render_options['var_classes'] .= $is_hidden ? ' hidden' : '';
		$this->render_options['var_classes'] .= $is_changed ? ' acss-var--changed' : '';
		$this->render_options['var_classes'] = apply_filters( 'automatic_css_render_' . $var_id . '_classes', $this->render_options['var_classes'], $var_id, $var_options, $var_value );
		// Required logic.
		$is_required_by_attribute = array_key_exists( 'required', $var_options ) ? (bool) $var_options['required'] : true;
		$is_required_by_condition = array_key_exists( 'required_condition', $var_options ) ? (bool) $var_options['required_condition'] : false;
		$this->render_options['required'] = $is_required_by_attribute || ( $is_hidden && $is_required_by_condition ) ? 'required="required"' : '';
	}

	/**
	 * Enable the wrapper.
	 *
	 * @return Base
	 */
	public function with_wrapper() {
		$this->enable_outside_wrapper_rendering = true;
		return $this;
	}

	/**
	 * Render the wrapper opening.
	 *
	 * @return void
	 */
	public function render_wrapper_open() {
		?>
		<?php if ( $this->enable_outside_wrapper_rendering ) : ?>
			<div class="acss-var <?php echo esc_attr( $this->render_options['var_classes'] ); ?>" id="acss-var-<?php echo esc_attr( $this->render_options['id'] ); ?>">
				<header class="acss-var__header">

					<div class="acss-var__info">
						<?php if ( ! empty( $this->render_options['title'] ) ) : ?>
							<h4 class="acss-var__title"><?php echo esc_html( $this->render_options['title'] ); ?></h4>
						<?php endif; ?>
						<?php if ( ! empty( $this->render_options['tooltip'] ) ) : ?>
							<?php Tooltip::render( $this->render_options['tooltip'] ); ?>
						<?php endif; ?>
					</div>

				</header> <!-- .acss-var__heading -->


				<p class="acss-var__error_message"></p>
				</header> <!-- .acss-var__heading -->
			<?php endif; ?>
			<div class="acss-value">
				<div class="acss-value__input-wrapper">
				<?php
	}

	/**
	 * Render the wrapper closing.
	 *
	 * @return void
	 */
	public function render_wrapper_close() {
		$can_reset = isset( $this->render_options['no_reset'] ) && true === (bool) $this->render_options['no_reset'] ? false : true;
		?>
		<?php if ( $can_reset ) : ?>
			<?php
			$disable_reset_button = $this->render_options['value'] == $this->render_options['default'] ? ' disabled="disabled"' : ''; // TODO: improve check by considering what type "value" is.
			?>
						<input type="image" src="<?php echo esc_url( ACSS_ASSETS_URL . '/img/reset.svg' ); ?>" class="acss-reset-button" <?php echo $disable_reset_button; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
						<div class="acss-tooltip">
							<p>Are you sure?</p>
							<button class="acss-tooltip__accept">
								<svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M9 0.25C4.00781 0.25 0 4.29297 0 9.25C0 14.2422 4.00781 18.25 9 18.25C13.957 18.25 18 14.2422 18 9.25C18 4.29297 13.957 0.25 9 0.25ZM13.043 7.70312L8.54297 12.2031C8.36719 12.4141 8.12109 12.4844 7.875 12.4844C7.59375 12.4844 7.34766 12.4141 7.17188 12.2031L4.92188 9.95312C4.53516 9.56641 4.53516 8.96875 4.92188 8.58203C5.30859 8.19531 5.90625 8.19531 6.29297 8.58203L7.875 10.1289L11.6719 6.33203C12.0586 5.94531 12.6562 5.94531 13.043 6.33203C13.4297 6.71875 13.4297 7.31641 13.043 7.70312Z" fill="#80BE7E" />
								</svg>
							</button>
						</div>
		<?php endif; ?>
				</div> <!-- .acss-value__input-wrapper -->
			</div> <!-- .acss-value -->
		<?php if ( $this->enable_outside_wrapper_rendering ) : ?>
			</div> <!-- .acss-var -->
		<?php endif; ?>
		<?php
	}

	/**
	 * Render the unit.
	 *
	 * @return void
	 */
	protected function render_unit() {
		if ( ! empty( $this->render_options['unit'] ) ) {
			printf(
				'<div class="acss-value__unit">%s</div>',
				esc_attr( $this->render_options['unit'] )
			);
		}
	}

	/**
	 * Check if the provided content type is a variable
	 *
	 * @param string $type Content type.
	 * @return boolean
	 */
	public static function is_variable( $type ) {
		return 'variable' === $type;
	}

	/**
	 * Check if the provided content type is a variable
	 *
	 * @param string $type Content type.
	 * @return boolean
	 */
	public static function is_allowed_variable_type( $type ) {
		$input_types = array( 'checkbox', 'color', 'hidden', 'number', 'plain_text', 'select', 'text', 'toggle' );
		return in_array( $type, $input_types, true );
	}

	/**
	 * Render a variable based on its type.
	 *
	 * @param string  $var_id Variable ID.
	 * @param mixed   $ui_options Variable's UI options.
	 * @param array   $values All variables current values.
	 * @param boolean $with_wrapper Whether to render the wrapper or not.
	 * @return void
	 */
	public static function render_variable( $var_id, $ui_options, $values, $with_wrapper = true ) {
		$var_options = ( new Variables() )->get_variable_options( $var_id );
		if ( null === $var_options || ! is_array( $var_options ) || empty( $var_options['type'] ) ) {
			// TODO: error message?
			return;
		}
		$var_options = array_merge( $ui_options, $var_options );
		// STEP: if the unit is set in the ui_options, overwrite the one in the var_options.
		if ( ! empty( $ui_options['unit'] ) ) {
			// The unit set in ui.json overrides the one set in variables.json, because it's UI specific.
			$var_options['unit'] = $ui_options['unit'];
		}
		// STEP: determine the default value.
		$default_value = array_key_exists( 'default', $var_options ) ? $var_options['default'] : '';
		// The next line allows color saturation variables to hook in and change the default value based on their color' saturation.
		$var_options['default'] = apply_filters( 'automaticcss_render_options_default', $default_value, $var_id, $var_options, $values );
		// STEP: set the var_value. If it's null, use the default value.
		$var_value = isset( $values[ $var_id ] ) ? $values[ $var_id ] : ( isset( $var_options['default'] ) ? $var_options['default'] : null );
		// STEP: check for conditionals.
		if ( ! empty( $var_options['condition'] ) && is_array( $var_options['condition'] ) ) {
			// Check if the variable is hidden based on its condition.
			$condition = $var_options['condition'];
			if ( ! empty( $condition['type'] ) && 'show_only_if' === $condition['type'] ) {
				$condition_field = ! empty( $condition['field'] ) ? $condition['field'] : '';
				$condition_value = isset( $condition['value'] ) ? $condition['value'] : null;
				$condition_required = ! empty( $condition['required'] ) ? (bool) $condition['required'] : true;
				if ( '' !== $condition_field && null !== $condition_value ) {
					$actual_value = isset( $values[ $condition_field ] ) ? $values[ $condition_field ] : null;
					if ( null !== $actual_value && null !== $var_value && $var_value !== $actual_value ) {
						// Condition is false, this field should be hidden.
						$var_options['hidden'] = true;
						$var_options['required_condition'] = $condition_required;
					}
				}
			}
		}
		// STEP: determine variable type and instantiate the proper class.
		$var_ui_element = null;
		switch ( $var_options['type'] ) {
			case 'text':
				$var_ui_element = new Text( $var_id, $var_options, $var_value );
				break;
			case 'textarea':
				$var_ui_element = new Textarea( $var_id, $var_options, $var_value );
				break;
			case 'number':
				$var_ui_element = new Number( $var_id, $var_options, $var_value );
				break;
			case 'color':
				$var_ui_element = new Color( $var_id, $var_options, $var_value );
				break;
			case 'select':
				$var_ui_element = new Select( $var_id, $var_options, $var_value );
				break;
			case 'checkbox':
				$var_ui_element = new Checkbox( $var_id, $var_options, $var_value );
				break;
			case 'toggle':
				$var_ui_element = new Toggle( $var_id, $var_options, $var_value );
				break;
			case 'plaintext':
				$var_ui_element = new Plain_Text( $var_id, $var_options, $var_value );
				break;
			case 'hidden':
				$var_ui_element = new Hidden( $var_id, $var_options, $var_value );
				break;
		}
		// STEP: render the variable.
		if ( null !== $var_ui_element ) {
			if ( $with_wrapper ) {
				$var_ui_element->with_wrapper()->render();
			} else {
				$var_ui_element->render();
			}
		}
	}

	/**
	 * Render the UI element.
	 *
	 * @return void
	 */
	abstract public function render();
}
