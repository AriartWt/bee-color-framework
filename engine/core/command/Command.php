<?php
namespace wfw\engine\core\command;

use wfw\engine\lib\PHP\types\UUID;

/**
 *  Commande de base
 */
abstract class Command implements ICommand {
	/** @var UUID */
	private $_uuid;
	/** @var null|string $_userId */
	private $_userId;
	/** @var float */
	private $_generationDate;

	/**
	 *  Command constructor.
	 *
	 * @param null|string $userId
	 */
	public function __construct(?string $userId=null){
		$this->_userId = $userId;
		$this->_uuid = new UUID();
		$this->_generationDate = microtime(true);
	}

	/**
	 * @return UUID Command ID
	 */
	public function getId():UUID{
		return $this->_uuid;
	}

	/**
	 * @return null|string User ID that try to execute the command
	 */
	public function getUserId(): ?string {
		return $this->_userId;
	}

	/**
	 * @return float Creation date
	 */
	public function getGenerationDate():float{
		return $this->_generationDate;
	}
}