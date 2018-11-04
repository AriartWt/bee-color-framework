<?php
namespace wfw\engine\core\response\responses;

use wfw\engine\core\response\IResponse;

/**
 * La réponse est une erreur
 */
class ErrorResponse implements IResponse {
	/** @var string $_code */
	private $_code;
	/** @var string $_msg */
	private $_msg;
	/** @var mixed|null $_data */
	private $_data;

	/**
	 * ErrorResponse constructor.
	 *
	 * @param string $code Code d'erreur
	 * @param string $msg  Message
	 * @param mixed  $data (optionnel) données associées
	 */
	public function __construct(string $code, string $msg, $data = null) {
		$this->_code = $code;
		$this->_msg = $msg;
		$this->_data = $data;
	}

	/**
	 * @return string Code d'erreur
	 */
	public function getCode():string{
		return $this->_code;
	}

	/**
	 * @return string
	 */
	public function getMessage():string{
		return $this->_msg;
	}

	/**
	 * @return mixed Données de la réponse
	 */
	public function getData() {
		return $this->_data;
	}
}