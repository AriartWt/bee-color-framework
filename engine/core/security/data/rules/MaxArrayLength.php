<?php
namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Permet de limiter le nombre d'éléments dans un tableau
 */
final class MaxArrayLength extends ForEachFieldRule{
	/** @var int $_length */
	private $_length;

	/**
	 * MaxArrayLength constructor.
	 *
	 * @param string $message
	 * @param int    $maxLength
	 * @param string ...$fields
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $message, int $maxLength,string... $fields) {
		parent::__construct($message, ...$fields);
		if($maxLength <= 0) throw new \InvalidArgumentException("maxLength must be > 0");
		$this->_length = $maxLength;
	}

	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool {
		return is_array($data) && count($data) <= $this->_length;
	}
}