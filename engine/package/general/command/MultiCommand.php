<?php
namespace wfw\engine\package\general\command;

use wfw\engine\core\command\Command;
use wfw\engine\core\command\ICommand;

/**
 * Commande contenant une à plusieur commandes
 */
final class MultiCommand extends Command{
	/** @var ICommand[] $_commands */
	private $_commands;

	/**
	 * MultiCommand constructor.
	 * @param ICommand[] $commands Liste des commandes
	 */
	public function __construct(ICommand... $commands) {
		parent::__construct();
		if(count($commands) < 2) throw new \InvalidArgumentException(
			"At least two commands expected !"
		);
		$this->_commands = $commands;
	}

	/**
	 * @return array
	 */
	public function commands():array{
		return $this->_commands;
	}
}