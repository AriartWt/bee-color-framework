<?php
namespace wfw\daemons\kvstore\server\containers\response;

use wfw\daemons\kvstore\server\responses\IKVSResponse;

/**
 *  Réponse de base d'un container vers le KVSServer
 */
final class ContainerResponse extends AbstractKVSContainerResponse {
	/** @var string $_queryId */
	private $_queryId;
	/** @var null|IKVSResponse $_response */
	private $_response;

	/**
	 * AbstractContainerResponse constructor.
	 *
	 * @param string                    $queryId  Identifiant de la requête
	 * @param null|IKVSResponse $response Réponse à envoyer au client
	 */
	public function __construct(string $queryId, ?IKVSResponse $response=null) {
		$this->_queryId = $queryId;
		$this->_response = $response;
	}

	/**
	 * @return string
	 */
	public function getQueryId(): string {
		return $this->_queryId;
	}

	/**
	 * @return null|IKVSResponse
	 */
	public function getResponse(): ?IKVSResponse {
		return $this->_response;
	}

	/**
	 *  Retourne l'identifiant de session de l'utilisateur
	 * @return null|string
	 */
	public function getSessionId(): ?string {
		return null;
	}
}