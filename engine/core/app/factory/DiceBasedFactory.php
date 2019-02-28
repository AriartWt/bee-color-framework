<?php

namespace wfw\engine\core\app\factory;

use Dice\Dice;

/**
 * Generic factory based on Dice DIC.
 */
final class DiceBasedFactory implements IGenericAppFactory {
	/** @var Dice $_dice */
	private $_dice;

	/**
	 * DiceBasedFactory constructor.
	 *
	 * @param Dice $dice Pre-configred dice instance.
	 */
	public function __construct(Dice $dice) {
		$this->_dice = $dice;
	}

	/**
	 * @param string $class  Class to create
	 * @param array  $params (optionnal) Paramaters to pass to the class
	 * @param array  $isA (optionnal) Interface or class list that $class must implements or extends
	 * @return mixed A builded object
	 */
	public function create(string $class, array $params = [], array $isA = []) {
		foreach($isA as $classOrInterface){
			if(!is_a($class,$classOrInterface,true)){
				throw new \InvalidArgumentException(
					"$class doesn't implements or extends $classOrInterface"
				);
			}
		}
		return $this->_dice->create($class,$params);
	}
}