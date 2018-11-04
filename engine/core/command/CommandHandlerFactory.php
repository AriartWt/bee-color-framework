<?php
namespace wfw\engine\core\command;


use Dice\Dice;

/**
 * Factory basée sur dice pour la création d'un command handler
 */
final class CommandHandlerFactory implements ICommandHandlerFactory {
	/** @var Dice $_dice */
	private $_dice;

	/**
	 * DiceBasedCommandHandlerFactory constructor.
	 *
	 * @param Dice $dice
	 */
	public function __construct(Dice $dice) {
		$this->_dice = $dice;
	}

	/**
	 * Constuit un ICommandHandler à partir du nom de la classe d'un CommandHandler
	 *
	 * @param string $handlerClass Classe du handler à construire
	 * @param array  $params Paramètres de création
	 * @return ICommandHandler
	 */
	public function build(string $handlerClass,array $params=[]): ICommandHandler {
		/** @var ICommandHandler $handler */
		$handler = $this->_dice->create($handlerClass);
		return $handler;
	}
}