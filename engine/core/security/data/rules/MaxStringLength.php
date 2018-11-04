<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 03/11/18
 * Time: 23:26
 */

namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Permet de limiter la longueur d'un champ à un certain nombre de caractères.
 */
final class MaxStringLength extends ForEachFieldRule{
	/** @var int $_length */
	private $_length;

	/**
	 * MaxStringLength constructor.
	 *
	 * @param string $message   Message affiché en cas d'erreur
	 * @param int    $maxLength Taille maximum du champ
	 * @param string ...$fields Liste des champs concernés
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $message,int $maxLength=255, string... $fields) {
		parent::__construct($message, ...$fields);
		if($maxLength<=0) throw new \InvalidArgumentException("maxLength must be > 0");
		$this->_length = $maxLength;
	}

	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool {
		return is_string($data) && strlen($data) <= $this->_length;
	}
}