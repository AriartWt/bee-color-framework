<?php
namespace wfw\daemons\kvstore\server;

/**
 *  Requête interne du KVSServer vers les KVSContainerWorker
 */
interface IKVSInternalRequest extends IKVSMessage {
	/**
	 * @return string Clé générée par le serveur
	 */
	public function getServerKey():string;

	/**
	 * @return string Identifiant de la requête
	 */
	public function getQueryId():string;

	/**
	 * @return string Nom de l'utilisateur à l'origine de la requête.
	 */
	public function getUserName():string;
}