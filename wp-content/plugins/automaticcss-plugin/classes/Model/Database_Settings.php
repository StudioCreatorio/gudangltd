<?php
/**
 * Automatic.css Database_Settings file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Model;

use Automatic_CSS\CSS_Engine\CSS_Engine;
use Automatic_CSS\Exceptions\Insufficient_Permissions;
use Automatic_CSS\Exceptions\Invalid_Form_Values;
use Automatic_CSS\Exceptions\Invalid_Variable;
use Automatic_CSS\Helpers\Timer;
use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Model\Config\Variables;
use Automatic_CSS\Traits\Singleton;

/**
 * Automatic.css Database_Settings class.
 */
final class Database_Settings {

	use Singleton;

	/**
	 * Stores the name of the plugin's database option
	 *
	 * @var string
	 */
	public const ACSS_SETTINGS_OPTION = 'automatic_css_settings';

	/**
	 * Stores the current value from the wp_options table.
	 *
	 * @var array|null
	 */
	private $plugin_wp_options = null;

	/**
	 * Capability needed to write settings
	 *
	 * @var string
	 */
	public const CAPABILITY = 'manage_options';

	/**
	 * Initialize the class
	 *
	 * @return Database_Settings The current instance of the class.
	 */
	public function init() {
		if ( is_admin() ) {
			// Handle database changes when the plugin is updated.
			add_filter( 'automaticcss_upgrade_database', array( $this, 'upgrade_database' ), 10, 4 );
			// Handle deleting database options when the plugin is deleted.
			add_action( 'automaticcss_delete_plugin_data_start', array( $this, 'delete_database_options' ) );
		}
		return $this;
	}

	/**
	 * Get the current VARS values from the wp_options database table.
	 *
	 * @return array
	 */
	public function get_vars() {
		if ( ! isset( $this->plugin_wp_options ) ) {
			$this->plugin_wp_options = get_option( self::ACSS_SETTINGS_OPTION, array() );
		}
		return $this->plugin_wp_options;
	}

	/**
	 * Get the value for a specific variable from the wp_options database table.
	 *
	 * @param  string $var The variable name.
	 * @return mixed|null
	 */
	public function get_var( $var ) {
		$vars = $this->get_vars();
		if ( is_array( $vars ) && array_key_exists( $var, $vars ) ) {
			return $vars[ $var ];
		}
		return null;
	}

	/**
	 * Save the plugin's options to the database. Will work even if option doesn't exist (fresh start).
	 *
	 * @see    https://developer.wordpress.org/reference/functions/update_option/
	 * @param  array $values                 The plugin's options.
	 * @param  bool  $trigger_css_generation Trigger the CSS generation process upon saving or not.
	 * @return array Info about the saved options and the generated CSS files.
	 * @throws Invalid_Form_Values If the form values are not valid.
	 * @throws Insufficient_Permissions If the user does not have sufficient permissions to save the plugin settings.
	 */
	public function save_vars( $values, $trigger_css_generation = true ) {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			throw new Insufficient_Permissions( 'You do not have sufficient permissions to save the plugin settings.' );
		}
		$timer = new Timer();
		$return_info = array(
			'has_changed' => false,
			'generated_files' => array(),
			'generated_files_number' => 0,
		);
		$allowed_variables = ( new Variables() )->load();
		$sanitized_values = array();
		$errors = array();
		Logger::log( sprintf( '%s: triggering automaticcss_settings_save', __METHOD__ ) );
		do_action( 'automaticcss_settings_before_save', $values );
		// STEP: validate the form values and get the sanitized values.
		foreach ( $allowed_variables as $var_id => $var_options ) {
			// This makes it so that we ignore non allowed variables coming from the form (i.e. variables not in our config file).
			if ( array_key_exists( $var_id, $values ) ) {
				try {
					$sanitized_values[ $var_id ] = $this->get_validated_var( $var_id, $values[ $var_id ], $var_options, $values );
				} catch ( Invalid_Variable $e ) {
					$errors[ $var_id ] = $e->getMessage();
				}
			}
		}
		// STEP: if there are errors, throw an exception.
		if ( ! empty( $errors ) ) {
			Logger::log( sprintf( "%s: errors found while saving settings:\n%s", __METHOD__, print_r( $errors, true ) ), Logger::LOG_LEVEL_ERROR );
			$error_message = 'The settings you tried to save contain errors.';
			throw new Invalid_Form_Values( $error_message, $errors );
		}
		// STEP: save the sanitized values to the database.
		Logger::log( sprintf( "%s: saving these variables to the database:\n%s", __METHOD__, print_r( $sanitized_values, true ) ), Logger::LOG_LEVEL_NOTICE );
		if ( update_option( self::ACSS_SETTINGS_OPTION, $sanitized_values ) ) {
			$return_info['has_changed'] = true;
			$this->plugin_wp_options = $sanitized_values;
			// STEP: if the settings have changed and CSS generation is enabled, regenerate the CSS.
			if ( $trigger_css_generation ) {
				$return_info['generated_files'] = CSS_Engine::get_instance()->generate_all_css_files( $sanitized_values );
				$return_info['generated_files_number'] = count( $return_info['generated_files'] );
			}
			do_action( 'automaticcss_settings_after_save', $sanitized_values );
		}
		Logger::log(
			sprintf(
				'%s: done (saved settings: %b; regenerated CSS files: %s) in %s seconds',
				__METHOD__,
				$return_info['has_changed'],
				print_r( implode( ', ', $return_info['generated_files'] ), true ),
				$timer->get_time()
			)
		);
		return $return_info;
	}

	/**
	 * Validate a variable based on its type and value and return a sanitized value.
	 *
	 * @param  string $var_id      Variable's ID.
	 * @param  mixed  $var_value   Variable's value.
	 * @param  array  $var_options Variable's options.
	 * @param  array  $all_values  All variables' values.
	 * @return mixed
	 * @throws Invalid_Variable Exception if the variable is invalid.
	 */
	private function get_validated_var( $var_id, $var_value, $var_options, $all_values = array() ) {
		$type = isset( $var_options['type'] ) ? $var_options['type'] : null;
		if ( null === $type ) {
			$message = sprintf( '%s has no type defined.', $var_id );
			Logger::log( $message, Logger::LOG_LEVEL_ERROR );
			throw new Invalid_Variable( $message );
		}
		// STEP: perform a basic sanitization on the form's field.
		$var_value = sanitize_text_field( $var_value );
		// STEP: check that the value is not empty, if required.
		$required = self::is_required( $var_id, $var_value, $var_options, $all_values );
		if ( ! $required && '' === $var_value ) {
			// nothing else to check.
			Logger::log( sprintf( '%s: %s is not required and is empty, skipping its validation', __METHOD__, $var_id ), Logger::LOG_LEVEL_INFO );
			return $var_value;
		} else if ( $required && '' === $var_value ) {
			Logger::log( sprintf( '%s: %s is required and is empty, throwing an exception', __METHOD__, $var_id ), Logger::LOG_LEVEL_ERROR );
			throw new Invalid_Variable( sprintf( '%s cannot be empty.', $var_id ) );
		}
		// STEP: validate the value based on the type.
		switch ( $type ) {
			case 'text':
				break;
			case 'number':
				// STEP: check that the value is a number.
				if ( ! is_numeric( $var_value ) ) {
					$message = sprintf( '%s is not a number.', $var_id );
					Logger::log( $message, Logger::LOG_LEVEL_ERROR );
					throw new Invalid_Variable( $message );
				}
				// STEP: convert it to the proper type.
				$decimals = isset( $var_options['decimals'] ) && '' !== $var_options['decimals'] ? (int) $var_options['decimals'] : 0;
				$var_value = 0 === $decimals ? intval( $var_value ) : floatval( $var_value );
				// STEP: check that the value is within the allowed range.
				$min = isset( $var_options['min'] ) ? $var_options['min'] : null;
				$max = isset( $var_options['max'] ) ? $var_options['max'] : null;
				if ( null !== $min && $var_value < $min ) {
					$message = sprintf( '%s is smaller than the minimum allowed value (%s).', $var_id, $min );
					Logger::log( $message, Logger::LOG_LEVEL_ERROR );
					throw new Invalid_Variable( $message );
				}
				if ( null !== $max && $var_value > $max ) {
					$message = sprintf( '%s is greater than the maximum allowed value (%s).', $var_id, $max );
					Logger::log( $message, Logger::LOG_LEVEL_ERROR );
					throw new Invalid_Variable( $message );
				}
				break;
			case 'color':
				// STEP: check that the value is a hex color.
				$var_value = sanitize_hex_color( $var_value );
				if ( ! $var_value || ! preg_match( '/^#[a-f0-9]{6}$/i', $var_value ) ) {
					$message = sprintf( '%s is not a valid hex color.', $var_id );
					Logger::log( $message, Logger::LOG_LEVEL_ERROR );
					throw new Invalid_Variable( $message );
				}
				break;
			case 'select':
				// STEP: convert the value to the proper type (if it's a string, it stays that way).
				$var_value = self::get_converted_value( $var_value );
				// STEP: check if the value is in the list of allowed values.
				$options = isset( $var_options['options'] ) ? $var_options['options'] : null;
				if ( null === $options ) {
					$message = sprintf( '%s has no options defined.', $var_id );
					Logger::log( $message, Logger::LOG_LEVEL_ERROR );
					throw new Invalid_Variable( $message );
				}
				if ( ! in_array( $var_value, $options, true ) ) {
					$message = sprintf( '%s is not a valid option for %s.', $var_value, $var_id );
					Logger::log( $message, Logger::LOG_LEVEL_ERROR );
					throw new Invalid_Variable( $message );
				}
				break;
			case 'toggle':
				// STEP: check that the value is one of the valueon or valueoff options.
				$value_on = isset( $var_options['valueon'] ) ? $var_options['valueon'] : null;
				$value_off = isset( $var_options['valueoff'] ) ? $var_options['valueoff'] : null;
				if ( null === $value_on || null === $value_off ) {
					Logger::log( sprintf( '%s: %s is a toggle with no defined valueon or valueoff values', __METHOD__, $var_id ) );
					throw new Invalid_Variable( sprintf( '%s has no valueon or valueoff defined.', $var_id ), Logger::LOG_LEVEL_ERROR );
				}
				if ( $var_value !== $value_on && $var_value !== $value_off ) {
					Logger::log( sprintf( '%s: %s is a toggle with value %s, but acceptable values are valueon=%s, valueoff=%s', __METHOD__, $var_id, $var_value, null === $value_on ? 'null' : $value_on, null === $value_off ? 'null' : $value_off ) );
					throw new Invalid_Variable( sprintf( '%s is not a valid option.', $var_value ), Logger::LOG_LEVEL_ERROR );
				}
				break;
		}
		// STEP: return the validated and sanitized value.
		return $var_value;
	}

	/**
	 * Check weather a variable is required based on its settings and possibly other variables' values.
	 *
	 * @param  string $var_id      Variable's ID.
	 * @param  mixed  $var_value   Variable's value.
	 * @param  array  $var_options Variable's options.
	 * @param  array  $all_values  All variables' values.
	 * @return boolean
	 */
	private static function is_required( $var_id, $var_value, $var_options, $all_values ) {
		// STEP: check if it has a default value.
		// Any input that doesn't have a "required" property is required.
		$required = array_key_exists( 'required', $var_options ) ? (bool) $var_options['required'] : true;
		// STEP: check if another field requires this field.
		$required_by_condition = false;
		if ( ! empty( $var_options['condition'] ) && is_array( $var_options['condition'] ) ) {
			$condition = $var_options['condition'];
			if ( ! empty( $condition['type'] ) && 'show_only_if' === $condition['type'] ) {
				// This field is required if condition_field is set to condition_value and condition_required is true.
				$condition_field = ! empty( $condition['field'] ) ? $condition['field'] : '';
				$condition_value = isset( $condition['value'] ) ? self::get_converted_value( $condition['value'] ) : null;
				$condition_required = ! empty( $condition['required'] ) ? (bool) $condition['required'] : true;
				if ( '' !== $condition_field && null !== $condition_value ) {
					$actual_value = isset( $all_values[ $condition_field ] ) ? self::get_converted_value( $all_values[ $condition_field ] ) : null;
					Logger::log( sprintf( '%s: checking condition for %s - %s is %s (required value is %s)', __METHOD__, $var_id, $condition_field, null === $actual_value ? 'null' : $actual_value, $condition_value ), Logger::LOG_LEVEL_INFO );
					if ( null !== $actual_value && $actual_value === $condition_value ) {
						Logger::log( sprintf( '%s: %s required %s because %s is %s', __METHOD__, $var_id, $condition_required ? 'true' : 'false', $condition_field, $actual_value ), Logger::LOG_LEVEL_INFO );
						$required_by_condition = $condition_required;
					}
				}
			}
		}
		// STEP: return the result.
		return $required || $required_by_condition;
	}


	/**
	 * Convert the value based on the type. Supports int, float and string.
	 *
	 * @param  mixed $value The input value.
	 * @return mixed
	 */
	private static function get_converted_value( $value ) {
		if ( self::is_int( $value ) ) {
			return intval( $value );
		} else if ( self::is_float( $value ) ) {
			return floatval( $value );
		}
		return $value;
	}

	/**
	 * Is this value an integer?
	 *
	 * @param  mixed $value The value to check.
	 * @return boolean
	 */
	private static function is_int( $value ) {
		return( ctype_digit( strval( $value ) ) );
	}

	/**
	 * Is this value a float?
	 *
	 * @param  mixed $value The value to check.
	 * @return boolean
	 */
	private static function is_float( $value ) {
		return (string) (float) $value === $value;
	}

	/**
	 * Update database fields and values upon plugin upgrade.
	 *
	 * @param  array  $values           The database values.
	 * @param  string $current_version  The version of the plugin we're upgrading to.
	 * @param  string $previous_version The version of the plugin we're upgrading from.
	 * @return array The (maybe modified) database values.
	 */
	public function upgrade_database( $values, $current_version, $previous_version ) {
		Logger::log( sprintf( '%s: upgrading from %s to %s', __METHOD__, $previous_version, $current_version ) );
		if ( version_compare( $previous_version, '2.0.0', '<' ) && version_compare( $current_version, '2.0.0', '>=' ) ) {
			Logger::log( sprintf( '%s: running pre 2.0 -> 2.0 upgrade', __METHOD__ ) );
			// Handle section-padding-x -> section-padding-x-max conversion.
			if ( array_key_exists( 'section-padding-x', $values ) ) {
				Logger::log( sprintf( '%s: converting section-padding-x to section-padding-x-max', __METHOD__ ) );
				$values['section-padding-x-max'] = $values['section-padding-x'];
				unset( $values['section-padding-x'] );
			}
			// Handle primary-hover-var -> primary-hover-l conversion.
			$color_types = array( 'action', 'primary', 'secondary', 'base', 'accent', 'shade' );
			$color_variations = array( 'hover', 'ultra-light', 'light', 'medium', 'dark', 'ultra-dark' );
			foreach ( $color_types as $color_type ) {
				foreach ( $color_variations as $color_variation ) {
					$old_var = $color_type . '-' . $color_variation . '-val';
					if ( array_key_exists( $old_var, $values ) ) {
						$new_var = $color_type . '-' . $color_variation . '-l';
						Logger::log(
							sprintf(
								'%s: converting %s to %s with value %s',
								__METHOD__,
								$old_var,
								$new_var,
								$values[ $old_var ]
							)
						);
						$values[ $new_var ] = $values[ $old_var ];
						unset( $values[ $old_var ] );
					}
				}
			}
			// Handle text overrides REM -> px conversion.
			$text_size_variations = array( 'xs', 's', 'm', 'l', 'xl', 'xxl' );
			$text_size_min_max_variations = array( 'min', 'max' );
			$root_font_size = array_key_exists( 'root-font-size', $values ) ? floatval( $values['root-font-size'] ) : 62.5;
			foreach ( $text_size_variations as $text_size_variation ) {
				foreach ( $text_size_min_max_variations as $min_max_variation ) {
					$text_size_var = 'text-' . $text_size_variation . '-' . $min_max_variation;
					// When these values were converted from REM to PX, they were divided by 10 and then adjusted for root-font-size.
					// So: new value = old value * 10 * root-font-size / 62.5.
					if ( array_key_exists( $text_size_var, $values ) && '' !== $values[ $text_size_var ] ) { // accept 0 though.
						$text_size_old_value = $values[ $text_size_var ];
						$text_size_new_value = $text_size_old_value * 10 * $root_font_size / 62.5;
						Logger::log( sprintf( '%s: converting %s from %s to %s', __METHOD__, $text_size_var, $text_size_old_value, $text_size_new_value ) );
						$values[ $text_size_var ] = $text_size_new_value;
					}
				}
			}
		}
		if ( version_compare( $previous_version, '2.2.0.2', '<=' ) && version_compare( $current_version, '2.2.0.2', '>' ) ) {
			Logger::log( sprintf( '%s: running post 2.2.0.2 upgrades', __METHOD__ ) );
			// Add new shade hue variables.
			$color_types = array( 'action', 'primary', 'secondary', 'base', 'accent', 'shade' );
			$color_variations = array( 'hover', 'ultra-light', 'light', 'medium', 'dark', 'ultra-dark' );
			foreach ( $color_types as $color_type ) {
				foreach ( $color_variations as $color_variation ) {
					$hue_key = $color_type . '-' . $color_variation . '-h';
					$color_key = 'color-' . $color_type;
					if ( ! array_key_exists( $hue_key, $values ) && array_key_exists( $color_key, $values ) ) {
						$hue_value = ( new \Automatic_CSS\Helpers\Color( $values[ $color_key ] ) )->h;
						$values[ $hue_key ] = $hue_value;
						Logger::log( sprintf( '%s: adding %s with value %s', __METHOD__, $hue_key, $hue_value ) );
					}
				}
			}
		}
		if ( version_compare( $previous_version, '2.4', '<' ) && version_compare( $current_version, '2.4', '>=' ) ) {
			Logger::log( sprintf( '%s: running post 2.4.0 upgrades', __METHOD__ ) );
			if ( ( ! array_key_exists( 'breakpoint-xl', $values ) || '' === $values['breakpoint-xl'] ) && array_key_exists( 'vp-max', $values ) ) {
				Logger::log( sprintf( '%s: breakpoint-xl is now a variable, taking the value of vp-max', __METHOD__ ) );
				$values['breakpoint-xl'] = $values['vp-max'];
			}
		}
		if ( version_compare( $previous_version, '2.5', '<' ) && version_compare( $current_version, '2.5', '>=' ) ) {
			Logger::log( sprintf( '%s: running post 2.5.0 upgrades', __METHOD__ ) );
			// Migrate old form styling variables.
			$migration = array(
				// old variable => new variable.
				'f-light-label-size-min' => 'f-label-size-min',
				'f-light-label-size-max' => 'f-label-size-max',
				'f-light-label-font-weight' => 'f-label-font-weight',
				'f-light-label-padding-x' => 'f-label-padding-x',
				'f-light-label-padding-y' => 'f-label-padding-y',
				'f-light-label-margin-bottom' => 'f-label-margin-bottom',
				'f-light-label-text-transform' => 'f-label-text-transform',
				'f-light-legend-text-weight' => 'f-legend-text-weight',
				'f-light-legend-size-min' => 'f-legend-size-min',
				'f-light-legend-size-max' => 'f-legend-size-max',
				'f-light-legend-margin-bottom' => 'f-legend-margin-bottom',
				'f-light-legend-line-height' => 'f-legend-line-height',
				'f-light-help-size-min' => 'f-help-size-min',
				'f-light-help-size-max' => 'f-help-size-max',
				'f-light-help-line-height' => 'f-help-line-height',
				'f-light-field-margin-bottom' => 'f-field-margin-bottom',
				'f-light-fieldset-margin-bottom' => 'f-fieldset-margin-bottom',
				'f-light-grid-gutter' => 'f-grid-gutter',
				'f-light-input-border-top-size' => 'f-input-border-top-size',
				'f-light-input-border-right-size' => 'f-input-border-right-size',
				'f-light-input-border-bottom-size' => 'f-input-border-bottom-size',
				'f-light-input-border-left-size' => 'f-input-border-left-size',
				'f-light-input-border-radius' => 'f-input-border-radius',
				'f-light-input-text-size-min' => 'f-input-text-size-min',
				'f-light-input-text-size-max' => 'f-input-text-size-max',
				'f-light-input-font-weight' => 'f-input-font-weight',
				'f-light-input-height' => 'f-input-height',
				'f-light-input-padding-x' => 'f-input-padding-x',
				'f-light-btn-border-style' => 'f-btn-border-style',
				'f-light-btn-margin-top' => 'f-btn-margin-top',
				'f-light-btn-padding-y' => 'f-btn-padding-y',
				'f-light-btn-padding-x' => 'f-btn-padding-x',
				'f-light-btn-border-size' => 'f-btn-border-size',
				'f-light-btn-border-radius' => 'f-btn-border-radius',
				'f-light-btn-text-size-min' => 'f-btn-text-size-min',
				'f-light-btn-text-size-max' => 'f-btn-text-size-max',
				'f-light-btn-font-weight' => 'f-btn-font-weight',
				'f-light-btn-line-height' => 'f-btn-line-height',
				'f-light-btn-text-transform' => 'f-btn-text-transform',
				'f-light-btn-text-decoration' => 'f-btn-text-decoration',
				'f-light-option-label-font-weight' => 'f-option-label-font-weight',
				'f-light-option-label-font-size-min' => 'f-option-label-font-size-min',
				'f-light-option-label-font-size-max' => 'f-option-label-font-size-max',
				'f-light-progress-height' => 'f-progress-height',
				'f-light-tab-border-style' => 'f-tab-border-style',
				'f-light-tab-padding-y' => 'f-tab-padding-y',
				'f-light-tab-padding-x' => 'f-tab-padding-x',
				'f-light-tab-margin-x' => 'f-tab-margin-x',
				'f-light-tab-border-size' => 'f-tab-border-size',
				'f-light-tab-active-border-color' => 'f-dark-tab-border-color',
				'f-light-tab-border-radius' => 'f-tab-border-radius',
				'f-light-tab-text-size-min' => 'f-tab-text-size-min',
				'f-light-tab-text-size-max' => 'f-tab-text-size-max',
				'f-light-tab-text-weight' => 'f-tab-text-weight',
				'f-light-tab-active-text-weight' => 'f-tab-active-text-weight',
				'f-light-tab-text-line-height' => 'f-tab-text-line-height',
				'f-light-tab-text-transform' => 'f-tab-text-transform',
				'f-light-tab-text-align' => 'f-tab-text-align',
				'f-light-tab-text-decoration' => 'f-tab-text-decoration',
				'f-light-tab-active-border-bottom-size' => 'f-tab-active-border-bottom-size',
				'f-light-tab-group-padding-y' => 'f-tab-group-padding-y',
				'f-light-tab-group-padding-x' => 'f-tab-group-padding-x',
				'f-light-tab-group-border-bottom-size' => 'f-tab-group-border-bottom-size',
				'f-light-tab-group-border-bottom-style' => 'f-tab-group-border-bottom-style',
				'f-light-tab-group-margin-bottom' => 'f-tab-group-margin-bottom',
			);
			foreach ( $migration as $old_var_name => $new_var_name ) {
				if ( array_key_exists( $old_var_name, $values ) ) {
					Logger::log( sprintf( '%s: converting %s to %s', __METHOD__, $old_var_name, $new_var_name ) );
					$values[ $new_var_name ] = $values[ $old_var_name ];
					unset( $values[ $old_var_name ] );
				}
			}
		}
		if ( version_compare( $previous_version, '2.6', '<' ) && version_compare( $current_version, '2.6', '>=' ) ) {
			Logger::log( sprintf( '%s: running post 2.6.0 upgrades', __METHOD__ ) );
			$new_settings = array(
				// setting name => default value.
				'f-light-tab-inactive-text-color' => 'var(--shade-dark-trans-80)',
				'f-dark-tab-inactive-text-color' => 'var(--shade-light-trans-80)'
			);
			foreach ( $new_settings as $setting_name => $default_value ) {
				if ( ! array_key_exists( $setting_name, $values ) ) {
					Logger::log( sprintf( '%s: adding %s with default value %s', __METHOD__, $setting_name, $default_value ) );
					$values[ $setting_name ] = $default_value;
				}
			}
		}
		return $values;
	}

	/**
	 * Get the color palettes.
	 *
	 * @param array $options The options.
	 * @return array
	 */
	public static function get_color_palettes( $options = array() ) {
		$defaults = array(
			'transparency_colors' => true,
			'contextual_colors' => true,
			'global_palette' => true,
			'deprecated_colors' => true,
		);
		$options = wp_parse_args( $options, $defaults );
		$palettes = array();
		if ( $options['global_palette'] ) {
			$palettes['global'] = array(
				'name' => 'Global',
				'colors' => array()
			);
		}
		$main_colors = array( 'action', 'primary', 'secondary', 'accent', 'base', 'shade' );
		$color_modifiers = array( 'ultra-light', 'light', 'medium', 'dark', 'ultra-dark', 'hover', 'comp' );
		$transparent_colors = array( '', 'light', 'dark' ); // the empty one is necessary for primary-trans-10, etc.
		if ( $options['deprecated_colors'] ) {
			$transparent_colors[] = 'ultra-dark';
		}
		$color_transparencies = array( 'trans-10', 'trans-20', 'trans-40', 'trans-60', 'trans-80', 'trans-90' );
		// STEP: generate a color palette for each of the main colors.
		foreach ( $main_colors as $main_color ) {
			$palettes[ $main_color ] = array(
				'name' => ucfirst( $main_color ),
				'colors' => array()
			);
			// STEP: add the main color.
			$palettes[ $main_color ]['colors'][ $main_color ] = "var(--{$main_color})";
			if ( $options['global_palette'] ) {
				$palettes['global']['colors'][ $main_color ] = "var(--{$main_color})";
			}
			// STEP: add color modifiers (i.e. primary-dark).
			foreach ( $color_modifiers as $color_modifier ) {
				$color = $main_color . '-' . $color_modifier;
				$palettes[ $main_color ]['colors'][ $color ] = "var(--{$color})";
			}
			// STEP: add color transparencies (i.e. primary-trans-10).
			if ( $options['transparency_colors'] ) {
				foreach ( $transparent_colors as $transparent_color ) {
					foreach ( $color_transparencies as $color_transparency ) {
						$middle_part = empty( $transparent_color ) ? '' : '-' . $transparent_color;
						$final_part = '-' . $color_transparency;
						$color = $main_color . $middle_part . $final_part;
						$palettes[ $main_color ]['colors'][ $color ] = "var(--{$color})";
					}
				}
			}
		}
		// STEP: generate a single color palette for all contextual colors.
		if ( $options['contextual_colors'] ) {
			$palettes['contextual'] = array(
				'name' => 'Contextual',
				'colors' => array()
			);
			$contextual_colors = array( 'success', 'danger', 'warning', 'info' );
			$contextual_modifiers = array( 'light', 'dark', 'hover' );
			foreach ( $contextual_colors as $contextual_color ) {
				// STEP: add the main color.
				$palettes['contextual']['colors'][ $contextual_color ] = "var(--{$contextual_color})";
				if ( $options['global_palette'] ) {
					$palettes['global']['colors'][ $contextual_color ] = "var(--{$contextual_color})";
				}
				// STEP: add color modifiers (i.e. success-dark).
				foreach ( $contextual_modifiers as $contextual_modifier ) {
					$color = $contextual_color . '-' . $contextual_modifier;
					$palettes['contextual']['colors'][ $color ] = "var(--{$color})";
				}
			}
		}
		// STEP: add extra globals.
		if ( $options['global_palette'] ) {
			$palettes['global']['colors']['white'] = 'var(--white)';
			$palettes['global']['colors']['black'] = 'var(--black)';
			$palettes['global']['colors']['transparent'] = 'transparent';
		}
		return $palettes;
	}

	/**
	 * Delete the framework's database option(s).
	 *
	 * @return void
	 * @throws Insufficient_Permissions If the user does not have permission to delete the database options.
	 */
	public function delete_database_options() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			throw new Insufficient_Permissions( 'You do not have permission to delete the database options.' );
		}
		delete_option( self::ACSS_SETTINGS_OPTION );
	}
}
