<?php
namespace wfw\engine\core\action;

use wfw\engine\core\app\factory\IGenericAppFactory;

/**
 * Factory de ActionHandler basée sur Dice pour la résolution des dépendances à injecter.
 */
final class ActionHandlerFactory implements IActionHandlerFactory {
	/** @var IGenericAppFactory $_factory */
	private $_factory;

	/**
	 * ActionHandlerFactory constructor.
	 *
	 * @param IGenericAppFactory $factory factory permettant de créer un ActionHandler
	 */
	public function __construct(IGenericAppFactory $factory) {
		$this->_factory = $factory;
	}

	/**
	 * Créer un ActionHandler en y injectant les objets demandés au constructeur.
	 *
	 * @param string $className Classe du handler. Doit implémenter IActionHandler.
	 * @param array  $params    Paramètres supplémentaires à passer au handler
	 * @return IActionHandler
	 */
	public function create(string $className, array $params = []): IActionHandler {
		return $this->_factory->create($className,$params,[IActionHandler::class]);
	}
}