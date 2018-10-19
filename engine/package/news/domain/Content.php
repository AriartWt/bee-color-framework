<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/04/18
 * Time: 09:01
 */

namespace wfw\engine\package\news\domain;

/**
 * Contenu d'un article
 */
class Content
{
	/**
	 * @var string $_content
	 */
	private $_content;

	/**
	 * Content constructor.
	 *
	 * @param string $content Contenu d'un article
	 */
	public function __construct(string $content){
		if(strlen($content)>0) $this->_content = $content;
		else throw new \InvalidArgumentException("An article content cann't be empty !");
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->_content;
	}
}