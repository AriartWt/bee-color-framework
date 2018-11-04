<?php
namespace wfw\engine\core\security;

use Dice\Dice;

/**
 * Factory d'AccessRule basée sur Dice
 */
final class AccessRuleFactory implements IAccessRuleFactory {
	/** @var Dice $_dice */
	private $_dice;

	/**
	 * AccessRuleFactory constructor.
	 *
	 * @param Dice $dice Dice
	 */
	public function __construct(Dice $dice) {
		$this->_dice = $dice;
	}

	/**
	 * Créer une AccessRule
	 *
	 * @param string $ruleClass Classe de l'AccessRule à créer
	 * @param array  $params    Paramètres de construction
	 * @return IAccessRule
	 */
	public function create(string $ruleClass, array $params = []): IAccessRule {
		if(is_a($ruleClass,IAccessRule::class,true)){
			/** @var IAccessRule $rule */
			$rule = $this->_dice->create($ruleClass,$params);
			return $rule;
		}else{
			throw new \InvalidArgumentException(
				"$ruleClass doesn't implements ".IAccessRule::class
			);
		}
	}
}