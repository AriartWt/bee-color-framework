<?php
namespace wfw\daemons\kvstore\server\responses;

/**
 *  Une erreur est survenue lors du traitement de la requête
 */
class RequestError extends AbstractKVSResponse {
	/** @var string $_error */
	private $_error;
	/** @var string $_errorClass */
	private $_errorClass;

	/**
	 * KVSRequestError constructor.
	 *
	 * @param \Exception $error
	 */
	public function __construct(\Exception $error) {
		$this->_error = (string) $error;
		$this->_errorClass = get_class($error);
	}

	/**
	 * @return \Exception
	 */
	public function getError():string{
		return $this->_error;
	}

	/**
	 * @return string
	 */
	public function getErrorClass():string{
		return $this->_errorClass;
	}

	/**
	 * @param string $class Classe à tester
	 * @return bool
	 */
	public function instanceOf(string $class):bool{
		return is_a($this->_errorClass,$class,true);
	}
}