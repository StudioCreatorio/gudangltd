<?php
/**
 * Slider Functions.
 *
 * @package Frames_Client
 */

namespace Frames_Client\Widgets\Slider\Inc;

use \Frames_Client\Widget_Manager;

if ( ! class_exists( Slider_Functions::class ) ) {

	/**
	 * Slider_Functions class.
	 */
	class Slider_Functions extends \Bricks\Element {

		function testEcho() {
			echo 'test';
		}


		// function generateArrayOfControls that will return an variable with an array of strings
		// this strings will be taken from parameters. each parameter will be pushed to the array
		// and then the array will be returned

		function generateArrayOfControls( $controlArray ) {
				$controls = array();
			foreach ( $controlArray as $control ) {
				array_push( $controls, $control );
			}
			return $controls;
		}



		function generateResponsiveControls( $controlArray, $settings ) {
			$breakpoints = \Bricks\Breakpoints::$breakpoints;
			foreach ( $breakpoints as $breakpoint ) {
				$breakpoint_name = $breakpoint['key'];
				$breakpoint_width = $breakpoint['width'];
				echo '"' . $breakpoint_width . '": {';

				$controls = array();
				foreach ( $controlArray as $control ) {
					if ( Widget_Manager::control( $settings, $control ) != null ) {
						if ( isset( $settings[ $control . ':' . $breakpoint_name ] ) ) {
							$value = $settings[ $control . ':' . $breakpoint_name ];
						} else {
							$value = $settings[ $control ];
						}
						if ( is_string( $value ) ) {
							$controls[] = '"' . $control . '": "' . $value . '"';
						} else if ( is_bool( $value ) ) {
							$controls[] = '"' . $control . '": ' . ( $value ? 'true' : 'false' );
						} else {
							$controls[] = '"' . $control . '": ' . $value;
						}
					}
				}
				echo implode( ',', $controls );
				if ( $breakpoint_name != end( $breakpoints )['key'] ) {
					echo '},';
				} else {
					echo '}';
				}
			}
		}

		function dataControl( $settings, $controls ) {
			foreach ( $controls as $control ) {
				if ( ! empty( $settings[ $control ] ) ) {
						$outputControl = '"' . $control . '": ';
						$outputValue = Widget_Manager::control( $settings, $control );
					if ( is_string( $outputValue ) ) {
						if ( is_numeric( $outputValue ) ) {
							$outputValue = $outputValue . ',';
						} else {
								$outputValue = '"' . $outputValue . '",';
						}
					} else if ( is_bool( $outputValue ) || $outputValue === 'true' || $outputValue === 'false' ) {
							$outputValue = ( $outputValue ? 'true' : 'false' ) . ',';
					} else if ( $control === 'drag' ) {
						if ( $outputValue === 'true' ) {
							$outputValue = 'true,';
						} else if ( $outputValue === 'free' ) {
							$outputValue = 'asdfasd,';
						} else {
							$outputValue = 'false,';
						}
					} else {
							$outputValue = $outputValue . ',';
					}
						echo $outputControl . $outputValue;
				}
			}
		}



		function syncControl( $settings ) {
			if ( isset( $settings['sync'] ) ) {
				if ( true === $settings['sync'] ) {
					echo 'data-fr-slider-sync="true"';
				} else {
					echo 'data-fr-slider-sync="false"';
				}
			} else {
				echo 'data-fr-slider-sync="false"';
			}
		}

		function syncIDControl( $settings ) {
			if ( isset( $settings['syncID'] ) ) {
				echo 'data-fr-slider-sync="';
				echo Widget_Manager::control( $settings, 'syncID' );
				echo '"';
			} else {
				echo '';
			}
		}


		function ifControlIsEmpty( $settings, $controls, $outputTrue, $outputFalse ) {
			foreach ( $controls as $control ) {
				if ( ! isset( $settings[ str_replace( ' ', '', $control ) ] ) || '' === $settings[ str_replace( ' ', '', $control ) ] ) {
						echo $outputTrue;
						return;
				}
			}
			echo $outputFalse;
		}





	}
}
