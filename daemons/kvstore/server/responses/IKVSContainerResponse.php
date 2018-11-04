<?php
namespace wfw\daemons\kvstore\server\responses;

use wfw\daemons\kvstore\server\requests\IKVSRequest;

/**
 *  Réponse d'un container KVS à une requête du serveur.
 */
interface IKVSContainerResponse extends IKVSRequest {
	/**
	 * @return string
	 */
	public function getQueryId():string;

	/**
	 * @return null|IKVSResponse
	 */
	public function getResponse():?IKVSResponse;
}