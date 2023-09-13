<?php

/**
 * SCSSPHP
 *
 * @copyright 2012-2020 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace Automatic_CSS\ScssPhp\Compiler;

/**
 * Compiler environment
 *
 * @author Anthon Pang <anthon.pang@gmail.com>
 *
 * @internal
 */
class Environment {

	/**
	 * @var \ScssPhp\Block|null
	 */
	public $block;

	/**
	 * @var \ScssPhp\Compiler\Environment|null
	 */
	public $parent;

	/**
	 * @var array
	 */
	public $store;

	/**
	 * @var array
	 */
	public $storeUnreduced;

	/**
	 * @var integer
	 */
	public $depth;
}
