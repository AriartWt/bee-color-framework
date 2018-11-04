<?php
namespace wfw\engine\core\request;

/**
 * Requête.
 */
final class Request implements IRequest {
	/** @var string $_ip */
	private $_ip;
	/** @var string $_url */
	private $_url;
	/** @var string $_uri */
	private $_uri;
	/** @var bool $_ajax */
	private $_ajax;
	/** @var IRequestData $_data */
	private $_data;
	/** @var string $_method */
	private $_method;
	/** @var null|string $_csrfToken */
	private $_csrfToken;
	/** @var array $_acceptLanguages */
	private $_acceptLanguages;
	/** @var array $_SERVER */
	private $_SERVER;

	/**
	 * Request constructor.
	 *
	 * @param IRequestData $data
	 * @param array        $server Paramètres SERVER de la requete
	 */
	public function __construct(IRequestData $data, array $server) {
		$this->_data = $data;
		$this->_SERVER = $server;
		$this->_ip = $server["REMOTE_ADDR"];
		$this->_uri = $server["REQUEST_URI"];
		$this->_url = $server["PATH_INFO"] ?? '/';
		$this->_ajax = $this->parseAjax();
		$this->_csrfToken = $this->parseCSRFToken();
		$this->_acceptLanguages = $this->parseAcceptLanguage(
			$server["HTTP_ACCEPT_LANGUAGE"] ?? 'fr,fr-fr;q=0.8,en-us;q=0.5,en;q=0.3'
		);
		$this->_method = $server["REQUEST_METHOD"];
	}

	/**
	 * @return bool Valeur de la variable ajax dans _GET. ajax est unset si trouvé.
	 */
	private function parseAjax():bool{
		$res = filter_var(
			$this->_data->get(IRequestData::GET,true)["ajax"] ?? false,
			FILTER_VALIDATE_BOOLEAN
		);
		$this->_data->remove(IRequestData::GET,"ajax");
		return $res;
	}

	/**
	 * @return null|string Valeur de la variable csrftoken dans _GET. csrftoken est unset si trouvé.
	 */
	private function parseCSRFToken():?string{
		$res = $this->_data->get(IRequestData::GET,true)["csrfToken"] ?? null;
		$this->_data->remove(IRequestData::GET,"csrfToken");
		return $res;
	}

	/**
	 * Obtient la langue du client envoyée en HTTP
	 *
	 * @param string $acceptString   Chaine envoyée par le serveur pour les langues acceptées
	 * @return array
	 */
	private function parseAcceptLanguage(string $acceptString):array{
		$langs = explode(',',$acceptString);
		$res = [];
		foreach($langs as $lang){
			$tmp = explode(";",$lang);
			$l = explode('-',$tmp[0])[0];
			if(!isset($res[$l])){
				$res[$l] = ((count($tmp)>1) ? floatval(explode("=",$lang)[1]) : 1);
			}
		}
		arsort($res);
		return $res;
	}

	/**
	 * @return bool True si la requête est AJAX, false sinon.
	 */
	public function isAjax(): bool {
		return $this->_ajax;
	}

	/**
	 * @return string IP du client
	 */
	public function getIP(): string {
		return $this->_ip;
	}

	/**
	 * @return string URI
	 */
	public function getURI(): string {
		return $this->_uri;
	}

	/**
	 * @return string URL
	 */
	public function getURL(): string {
		return $this->_url;
	}

	/**
	 * @return string Méthode HTTP
	 */
	public function getMethod(): string {
		return $this->_method;
	}

	/**
	 * @param array $availables Si spécifié, sert de filtre our ne retourner que les langues
	 *                          contenues dans availables
	 * @return array Langues acceptées par le client.
	 */
	public function getAcceptedLanguages(array $availables=[]): array {
		if(count($availables)>0){
			$res = $this->_acceptLanguages;
			if(count($availables)>0){
				$res = array_intersect_key($res,array_flip($availables));
				if(count($res)===0){
					$res = array_fill_keys($availables,1);
				}
			}
			arsort($res);
			return $res;
		}else{
			return $this->_acceptLanguages;
		}
	}

	/**
	 * @return null|string Token CSRF si fourni
	 */
	public function getCSRFToken(): ?string {
		return $this->_csrfToken;
	}

	/**
	 * @return IRequestData Données de la requête
	 */
	public function getData(): IRequestData {
		return $this->_data;
	}
}