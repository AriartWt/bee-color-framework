<?php
namespace wfw\daemons\kvstore\server\containers\response;

use wfw\daemons\kvstore\server\IKVSMessage;
use wfw\daemons\kvstore\server\responses\IKVSContainerResponse;

/**
 * Implementation de base pour une réponse d'un container du KVSServer au KVSServer
 */
abstract class AbstractKVSContainerResponse implements IKVSMessage,IKVSContainerResponse {
	/**
	 * @return mixed Données du message
	 */
	public function getData() {
		return null;
	}

	/**
	 * @return mixed Paramètres du message.
	 */
	public function getParams() {
		return null;
	}
}