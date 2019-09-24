<?php

namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Test si la date est valide
 */
final class IsDate extends ForEachFieldRule{
	/** @var array $_opt */
	private $_opt;

	/**
	 * IsDate constructor.
	 *
	 * @param string $message
	 * @param array  $options
	 * @param string ...$fields
	 */
	public function __construct(string $message, array $options=[], string... $fields) {
		parent::__construct($message, ...$fields);
		if(!is_string($options["before"]??"") || !is_string($options["after"]))
			throw new \InvalidArgumentException("options['before'] and options['after'] have to be strings !");
		if(!is_array($options["between"]??[]) && !is_string(($options["between"]??[""])[0]??null)
			&& !is_string(($options["between"]??["",""])[1]??null))
			throw new \InvalidArgumentException("options['between'] have to be an array with two string dates !");
		$this->_opt = $options;
	}

	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool {
		if(is_string($data)){
			$tmp = explode("-",$data);
			if(count($tmp)===3){
				if(checkdate($tmp[1],$tmp[2],$tmp[0])){
					if(isset($this->_opt["before"]))
						return strtotime($this->_opt["before"]) > strtotime($data);
					else if(isset($this->_opt["after"]))
						return strtotime($this->_opt["after"]) < strtotime($data);
					else if(isset($this->_opt["between"]))
						return strtotime($this->_opt["between"][1]) > strtotime($data)
							&& strtotime($this->_opt["between"][0]) < strtotime($data);
					else return true;
				}else return false;
			}else return false;
		}else return false;
	}
}