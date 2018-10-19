<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 27/06/18
 * Time: 16:56
 */

namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\RuleReport;

/**
 * Verifie que tous les champs spécifiés sont strictement égaux
 */
final class AreEquals implements IRule{
	/** @var string[] $_fields */
	private $_fields;
	/** @var string $_message */
	private $_message;

	/**
	 * AreEquals constructor.
	 * @param string $message
	 * @param string ...$fields
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $message, string... $fields) {
		if(count($fields)===0)
			throw new \InvalidArgumentException("At least one field expected !");
		$this->_fields = array_flip($fields);
		$this->_message = $message;
	}

	/**
	 * @param array $data Données auxquelles appliquer la règle.
	 * @return IRuleReport
	 */
	public function applyTo(array $data): IRuleReport {
		if(count(array_unique(array_intersect_key($data,$this->_fields))) !== 1){
			return new RuleReport(
				false,
				array_combine(
					array_keys($this->_fields),
					array_fill(0,count($this->_fields),$this->_message)
				)
			);
		}else return new RuleReport(true);
	}
}