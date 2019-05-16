<?php
namespace wfw\daemons\modelSupervisor\server;


use stdClass;
use wfw\engine\lib\logger\ILogger;

/**
 * Configuration d'un pool de MSServer
 */
interface IMSServerPoolConf {
	/**
	 * @brief Obtient le dossier de travail par défaut. Si aucun dossier de travail n'est défini,
	 *        le dossier DAEMONS."/model_supervisor/default_working_dir" est utilisé.
	 * @param null|string $instance Nom de l'instance dont on souhaite obtenir le dossier de travail
	 *                              Si non précisé, renvoie le dossier de travail global
	 * @return string
	 */
	public function getWorkingDir(?string $instance=null):string;

	/**
	 * @param null|string $instance Chemin de la socket d'une DB particulière
	 * @return string
	 */
	public function getSocketPath(?string $instance=null):string;

	/**
	 * @param string $instance Instance dont on souhaite connaitre les utilisateurs
	 * @return stdClass
	 * @throws \InvalidArgumentException
	 */
	public function getUsers(string $instance): stdClass;

	/**
	 * @param string $instance Instance dont on souhaite connaitre les groupes
	 * @return stdClass
	 * @throws \InvalidArgumentException
	 */
	public function getGroups(string $instance): stdClass;

	/**
	 * @param string $instance Instance dont ou souhaite connaitre les admins
	 * @return stdClass
	 * @throws \InvalidArgumentException
	 */
	public function getAdmins(string $instance):stdClass;

	/**
	 * @param string $instance
	 * @return stdClass
	 */
	public function getComponents(string $instance):stdClass;

	/**
	 * @param string $instance Instance dont ou souhaite connaître le temps de vie des sessions
	 * @return int
	 */
	public function getSessionTtl(string $instance):int;

	/**
	 * @param null|string $instance Instance dont on souhaite connaitre le temps de vie des requetes
	 * @return int
	 */
	public function getRequestTtl(?string $instance = null):int;

	/**
	 * @param string $instance Instance concernée
	 * @return bool
	 */
	public function haveToSendErrorToClient(string $instance):bool;

	/**
	 * @param string $instance Instance concernée
	 * @return bool
	 */
	public function haveToShutdownOnError(string $instance):bool;

	/**
	 * @param string $instance Instance concernée
	 * @return string
	 */
	public function getInitializersPath(string $instance):string;

	/**
	 * @param string $instance Instance concernée
	 * @return string
	 */
	public function getModelsToLoadPath(string $instance):string;

	/**
	 * @param string $instance Instance concernée
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getKVSLogin(string $instance):string;

	/**
	 * @param string $instance instance concernée
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getKVSPassword(string $instance):string;

	/**
	 * @param string $instance Instance concernée
	 * @return string
	 */
	public function getKVSContainer(string $instance):string;

	/**
	 * @param string $instance Instance concernée
	 * @return null|string
	 * @throws \InvalidArgumentException
	 */
	public function getKVSDefaultStorage(string $instance):?string;

	/**
	 * @return string
	 */
	public function getKVSAddr():string;

	/**
	 * @return string[] Noms des instances à créer
	 */
	public function getInstances():array;

	/**
	 * @return ILogger
	 */
	public function getLogger():ILogger;
}