<?php
namespace wfw\daemons\modelSupervisor\server;

use wfw\engine\lib\PHP\types\UUID;

/**
 *  Requête interne d'un MSServer vers ses composants.
 */
final class MSServerInternalRequest implements IMSServerInternalRequest {
	/** @var string $_id */
	private $_id;
	/** @var string $_request */
	private $_request;
	/** @var string $_userName */
	private $_userName;
	/** @var string $_serverKey */
	private $_serverKey;
	/** @var string $_requestData */
	private $_requestData;
	/** @var string $_requestClass */
	private $_requestClass;

	/**
	 * MSServerInternalRequest constructor.
	 *
	 * @param string $serverKey    Clé du serveur
	 * @param string $userName     Nom de l'utilisateur
	 * @param string $requestClass Classe de la requête reçue.
	 * @param string $request      Requête
	 * @param string $requestData  Données associées à la requête
	 */
	public function __construct(
		string $serverKey,
		string $userName,
		string $requestClass,
		string $request,
		string $requestData = ""
	) {
		$this->_serverKey = $serverKey;
		$this->_userName = $userName;
		$this->_requestClass = $requestClass;
		$this->_request = $request;
		$this->_requestData = $requestData;
		$this->_id = (string) new UUID(UUID::V4);
	}

	/**
	 * @return string Clé générée par le serveur
	 */
	public function getServerKey(): string {
		return $this->_serverKey;
	}

	/**
	 * @return string Identifiant de la requête
	 */
	public function getQueryId(): string {
		return $this->_id;
	}

	/**
	 * @return string La classe correspondant à la requête
	 */
	public function getRequestClass():string{
		return $this->_requestClass;
	}

	/**
	 * @return string Nom de l'utilisateur à l'origine de la requête.
	 */
	public function getUserName(): string {
		return $this->_userName;
	}

	/**
	 * @return mixed Données du message.
	 */
	public function getData() {
		return $this->_requestData;
	}

	/**
	 * @return mixed Paramètres du message
	 */
	public function getParams() {
		return $this->_request;
	}
}