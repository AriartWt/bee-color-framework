<?php
namespace wfw\engine\core\query;

use wfw\engine\lib\PHP\types\UUID;

/**
 * Command
 */
interface IQuery {
	/**
	 * @return UUID
	 */
	public function getId():UUID;

	/**
	 * @return null|string Uner that initates the query (if available)
	 */
	public function getInitiatorId():?string;

	/**
	 * @return float
	 */
	public function getGenerationDate():float;
}