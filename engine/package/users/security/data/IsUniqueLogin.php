<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/06/18
 * Time: 17:29
 */

namespace wfw\engine\package\users\security\data;

use wfw\engine\core\security\data\ForEachFieldRule;
use wfw\engine\package\users\data\model\IUserModelAccess;

/**
 * Verifie l'unicité d'un Login
 */
final class IsUniqueLogin extends ForEachFieldRule {
	/** @var IUserModelAccess $_access */
	private $_access;

	/**
	 * IsUniqueLogin constructor.
	 * @param IUserModelAccess $access
	 * @param string   $message
	 * @param string[] $fields
	 */
	public function __construct(IUserModelAccess $access,string $message, string... $fields) {
		parent::__construct($message, ...$fields);
		$this->_access = $access;
	}

	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool {
		return is_null($this->_access->getByLogin($data));
	}
}