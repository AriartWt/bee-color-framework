<?php
namespace wfw\engine\core\response\responses;

use wfw\engine\core\response\IResponse;

/**
 * Réponse de base.
 */
class Response implements IResponse {
	/** @var mixed|null $_data */
	private $_data;

	/**
	 * Response constructor.
	 *
	 * @param mixed $data Données
	 */
	public function __construct($data=null) {
		$this->_data=$data;
	}

	/**
	 * @return mixed Données de la réponse
	 */
	public function getData() {
		return $this->_data;
	}
}