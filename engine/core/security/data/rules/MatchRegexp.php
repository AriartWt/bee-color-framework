<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/03/18
 * Time: 02:55
 */

namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Verfie que chacun des champs match une expression régulière
 */
class MatchRegexp extends ForEachFieldRule
{
	/**
	 * @var string $_regexp
	 */
	private $_regexp;

	/**
	 * MatchRegexp constructor.
	 *
	 * @param string   $regexp    Expression régulière
	 * @param string   $message   Message en cas d'erreur
	 * @param string[] $fields Champs à matcher
	 */
	public function __construct(string $regexp,string $message, string... $fields) {
		parent::__construct($message, ...$fields);
		$this->_regexp = $regexp;
	}

	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected final function applyOn($data): bool {
		return preg_match($this->_regexp,$data);
	}
}