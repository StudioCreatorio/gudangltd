<?php
/**
 * Automatic.css classes config PHP file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Model\Config;

/**
 * Automatic.css classes config class.
 */
class Classes extends Base {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( 'classes.json' );
	}

	/**
	 * Get the plugin's config/classes.json content and store it / return it
	 *
	 * @return array
	 * @throws \Exception If it can't load the file or it doesn't have the right structure.
	 */
	public function load() {
		parent::load(); // contents stored in $this->contents.
		if ( ! array_key_exists( 'classes', $this->contents ) ) {
			throw new \Exception(
				sprintf(
					'%s: there is no "classes" item in the configuration file',
					__METHOD__
				)
			);
		}
		return array_unique( $this->contents['classes'] );
	}

}
