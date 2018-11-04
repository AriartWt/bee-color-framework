<?php
namespace wfw\engine\core\command;

use wfw\engine\lib\PHP\types\UUID;

/**
 *  Représente une commande pour un CommandHandler
 */
interface ICommand {
	/**
	 * @return UUID
	 */
	public function getId():UUID;

	/**
	 * @return float
	 */
	public function getGenerationDate():float;
}