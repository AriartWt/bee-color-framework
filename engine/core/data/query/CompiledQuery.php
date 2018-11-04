<?php
namespace wfw\engine\core\data\query;

/**
 *  Requête SQL compilée
 */
class CompiledQuery {
	/** @var string $_query */
	private $_query;
	/** @var array $_params */
	private $_params;

	/**
	 *  CompiledQuery constructor.
	 *
	 * @param string $query  Requête
	 * @param array  $params Paramètres de la requête
	 */
	public function __construct(string $query,array $params) {
		$this->_query = $query;
		$this->_params = $params;
	}

	/**
	 *  Retourne la requête
	 * @return string
	 */
	public function getQueryString():string{
		return $this->_query;
	}

	/**
	 *  Retourne les paramètres de la requête
	 * @return array
	 */
	public function getParams():array{
		return $this->_params;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->_query." \n( params : ".json_encode($this->_params)." )";
	}
}