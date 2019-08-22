<?php
namespace wfw\engine\core\query;

use wfw\engine\lib\PHP\types\UUID;

/**
 *  Querye de base
 */
abstract class Query implements IQuery {
	/** @var UUID */
	private $_uuid;
	/** @var null|string $_userId */
	private $_initiatorId;
	/** @var float */
	private $_generationDate;

	/**
	 *  Query constructor.
	 *
	 * @param null|string $initiatorId
	 */
	public function __construct(?string $initiatorId=null){
		$this->_initiatorId = $initiatorId;
		$this->_uuid = new UUID();
		$this->_generationDate = microtime(true);
	}

	/**
	 * @return UUID Query ID
	 */
	public function getId():UUID{
		return $this->_uuid;
	}

	/**
	 * @return null|string User ID that try to execute the query
	 */
	public function getInitiatorId(): ?string {
		return $this->_initiatorId;
	}

	/**
	 * @return float Creation date
	 */
	public function getGenerationDate():float{
		return $this->_generationDate;
	}
}