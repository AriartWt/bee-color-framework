<?php

namespace wfw\engine\core\command\security\rules;

use wfw\engine\core\command\ICommand;

/**
 * If any of the given rules is true, the result is true.
 */
class OrCommandAccessRule implements ICommandAccessRule{
	/** @var ICommandAccessRule[] $_rules */
	private $_rules;

	/**
	 * OrCommandAccessRule constructor.
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
			if($res = $rule->checkCommand($cmd)) return true;
			else if(is_null($res)) $unapplicable++;
		}
		if($unapplicable === count($this->_rules)) return null;
		return false;
	}
}