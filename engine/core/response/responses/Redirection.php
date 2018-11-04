<?php
namespace wfw\engine\core\response\responses;

use wfw\engine\core\response\IResponse;

/**
 * Déclenche une redirection.
 */
final class Redirection implements IResponse {
	/** @var string $_url */
	private $_url;
	/** @var null|int $code */
	private $_code;

	/**
	 * RedirectionResponse constructor.
	 *
	 * @param string   $url  URL de redirection
	 * @param int|null $code Code de redirection
	 */
	public function __construct(string $url,?int $code=null) {
		$this->_url = $url;
		$this->_code = $code;
	}

	/**
	 * @return string
	 */
	public function getUrl():string{
		return $this->_url;
	}

	/**
	 * @return null|int
	 */
	public function getCode():?int{
		return $this->_code;
	}

	/**
	 * @return mixed Données de la réponse
	 */
	public function getData() {
		return null;
	}
}