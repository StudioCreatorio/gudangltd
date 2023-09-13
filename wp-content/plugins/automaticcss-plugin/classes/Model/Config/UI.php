<?php
/**
 * Automatic.css UI config PHP file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Model\Config;

use Automatic_CSS\Helpers\Logger;

/**
 * Automatic.css UI config class.
 */
class UI extends Base {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( 'ui.json' );
	}

	/**
	 * Load the 'tabs' item from the ui.json file.
	 *
	 * @return array
	 * @throws \Exception If it can't load the file or it doesn't have the right structure.
	 */
	public function load() {
		parent::load(); // contents stored in $this->contents.
		if ( ! array_key_exists( 'tabs', $this->contents ) ) {
			throw new \Exception(
				sprintf(
					'%s: there is no "tabs" item in the configuration file',
					__METHOD__
				)
			);
		}
		return $this->contents['tabs'];
	}
}
