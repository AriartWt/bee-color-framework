<?php

namespace wfw\engine\lib\HTML\resources\lazyloader;

use wfw\engine\core\view\View;

/**
 * Class Lazyloader
 *
 * @package wfw\engine\lib\HTML\resources\lazyloader
 */
class Lazyloader extends View {
	public const CSS_CLASS="lazy-load";
	public const IMG_PLACEHOLDER="Image/placeholder.png";

	/** @var string $_class */
	private $_class;

	/**
	 * Lazyloader constructor.
	 *
	 * @param string $class
	 */
	public function __construct(string $class = self::CSS_CLASS) {
		parent::__construct(null, true);
		$this->_class = $class;
	}

	/**
	 * @return string
	 */
	public function getClass():string{
		return $this->_class;
	}
}