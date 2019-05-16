<?php
namespace wfw\engine\lib\network\http;

/**
 *  Permet de gérer des requêtes HTTP
 */
class HTTPRequest implements IHTTPRequest {
	/** @var string $_url */
	private $_url;
	/** @var mixed $_data */
	private $_data;
	/** @var array|null $_options */
	private $_options;

	/**
	 * HTTPRequest constructor.
	 *
	 * @param string      $url     Adresse de la requête
	 * @param array       $data    (optionnel) Données de la requête au format attendu par la fonction http_build_query()
	 * @param array|null  $options (optionnel) Options attendues par la fonction stream_context_create()
	 */
	public function __construct(string $url,array $data=[],?array $options=null) {
		$this->_url = $url;
		$this->_data = $data;
		$this->_options = $options;
	}

	/**
	 *  Permet d'envoyer une requête HTTP
	 * @return string
	 */
	function send():string{
		$options=array(
			"http"=>array(
				'header' => 'Content-type: application/x-www-form-urlencoded',
				'method' => $this->_options["method"] ?? "POST",
				'content'=> http_build_query($this->_data)
			)
		);
		$context=stream_context_create($options);
		return file_get_contents($this->_url,false,$context);
	}
}