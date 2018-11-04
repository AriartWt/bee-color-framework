<?php
namespace wfw\daemons\modelSupervisor\server;

/**
 *  Requête du ModelManagerServer vers un de ses Workers
 */
interface IMSServerRequest extends IMSserverMessage {
	/**
	 * @return null|string Identifiant de session
	 */
	public function getSessionId(): ?string;
}