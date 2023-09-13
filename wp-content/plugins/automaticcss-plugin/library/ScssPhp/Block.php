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

namespace Automatic_CSS\ScssPhp;

/**
 * Block
 *
 * @author Anthon Pang <anthon.pang@gmail.com>
 *
 * @internal
 */
class Block {

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var \ScssPhp\Block
	 */
	public $parent;

	/**
	 * @var string
	 */
	public $sourceName;

	/**
	 * @var integer
	 */
	public $sourceIndex;

	/**
	 * @var integer
	 */
	public $sourceLine;

	/**
	 * @var integer
	 */
	public $sourceColumn;

	/**
	 * @var array|null
	 */
	public $selectors;

	/**
	 * @var array
	 */
	public $comments;

	/**
	 * @var array
	 */
	public $children;

	/**
	 * @var \ScssPhp\Block|null
	 */
	public $selfParent;
}
