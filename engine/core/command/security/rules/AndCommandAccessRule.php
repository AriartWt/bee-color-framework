<?php

namespace wfw\engine\core\command\security\rules;

use wfw\engine\core\command\ICommand;

/**
 * If any of the given rules return false, the result is false.
 */
class AndCommandAccessRule implements ICommandAccessRule{
	/** @var ICommandAccessRule[] $_rules */
	private $_rules;

	/**
	 * AndCommandAccessRule constructor.
	 *
	 * @param ICommandAccessRule ...$rules
	 */
	public function __construct(ICommandAccessRule ...$rules) {
		$this->_rules = $rules;
	}

	/**
	 * @param ICommand    $cmd
	 * @return null|bool True if the command can be run, false otherwise.
	 */
	public function checkCommand(ICommand $cmd): ?bool {
		if(count($this->_rules) === 0) return null;
		$unapplicable = 0;
		foreach($this->_rules as $rule){
			if(!$res = $rule->checkCommand($cmd)){
				if(is_null($res)) $unapplicable++;
				else return false;
			}
		}
		if($unapplicable === count($this->_rules)) return null;
		return true;
	}


}