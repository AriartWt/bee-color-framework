<?php
namespace wfw\daemons\modelSupervisor\server\responses;

use wfw\daemons\modelSupervisor\server\IMSServerResponse;

/**
 * Implementation de base d'une réponse du MSServer.
 */
abstract class AbastractMSServerResponse implements IMSServerResponse {
	/**
	 * @return mixed Données du message.
	 */
	public function getData() {
		return null;
	}

	/**
	 * @return mixed Paramètres du message
	 */
	public function getParams() {
		return null;
	}
}