<?php
namespace wfw\engine\core\security;

use Dice\Dice;
use wfw\engine\core\app\factory\IGenericAppFactory;

/**
 * Factory d'AccessRule basée sur Dice
 */
final class AccessRuleFactory implements IAccessRuleFactory {
	/** @var IGenericAppFactory $_factory */
	private $_factory;

	/**
	 * AccessRuleFactory constructor.
	 *
	 * @param IGenericAppFactory $factory
	 */
	public function __construct(IGenericAppFactory $factory) {
		$this->_factory = $factory;
	}

	/**
	 * Créer une AccessRule
	 *
	 * @param string $ruleClass Classe de l'AccessRule à créer
	 * @param array  $params    Paramètres de construction
	 * @return IAccessRule
	 */
	public function create(string $ruleClass, array $params = []): IAccessRule {
		return $this->_factory->create($ruleClass,$params,[IAccessRule::class]);
	}
}