<?php
namespace wfw\engine\core\command;

use wfw\engine\lib\PHP\types\UUID;

/**
 * Command
 */
interface ICommand {
	/**
	 * @return UUID
	 */
	public function getId():UUID;

	/**
	 * @return null|string Uner that initates the command (if available)
	 */
	public function getUserId():?string;

	/**
	 * @return float
	 */
	public function getGenerationDate():float;
}