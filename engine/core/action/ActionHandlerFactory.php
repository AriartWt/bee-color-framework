<?php
namespace wfw\engine\core\action;

use Dice\Dice;

/**
 * Factory de ActionHandler basée sur Dice pour la résolution des dépendances à injecter.
 */
final class ActionHandlerFactory implements IActionHandlerFactory {
	/** @var Dice $_dice */
	private $_dice;

	/**
	 * ActionHandlerFactory constructor.
	 *
	 * @param Dice $dice Container permettant la résolution des dépendances des handlers.
	 */
	public function __construct(Dice $dice) {
		$this->_dice = $dice;
	}

	/**
	 * Créer un ActionHandler en y injectant les objets demandés au constructeur.
	 *
	 * @param string $className Classe du handler. Doit implémenter IActionHandler.
	 * @param array  $params    Paramètres supplémentaires à passer au handler
	 * @return IActionHandler
	 */
	public function create(string $className, array $params = []): IActionHandler {
		if(is_a($className,IActionHandler::class,true)){
			/** @var IActionHandler $handler */
			$handler = $this->_dice->create($className,$params);
			return $handler;
		}else{
			throw new \InvalidArgumentException("$className doesn't implements ".IActionHandler::class);
		}
	}
}