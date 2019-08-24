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
	/** @var bool|null $_absolute */
	private $_absolute;

	/**
	 * RedirectionResponse constructor.
	 *
	 * @param string    $url  URL de redirection
	 * @param int|null  $code Code de redirection
	 * @param bool|null $absolute If true, the given url is absolute and must not be parsed by the router.
	 */
	public function __construct(string $url,?int $code=null, ?bool $absolute=false) {
		$this->_url = $url;
		$this->_code = $code;
		$this->_absolute = $absolute;
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
	 * @return bool
	 */
	public function isAbsolute():bool{
		return $this->_absolute;
	}

	/**
	 * @return mixed Données de la réponse
	 */
	public function getData() {
		return null;
	}
}