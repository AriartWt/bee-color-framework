<?php
namespace wfw\daemons\kvstore\server\environment;

use stdClass;
use wfw\daemons\kvstore\server\KVSModes;
use wfw\daemons\kvstore\server\errors\ContainerNotFound;
use wfw\daemons\kvstore\server\errors\UserGroupNotFound;
use wfw\daemons\kvstore\server\errors\UserNotFound;
use wfw\daemons\kvstore\server\requests\ShutdownKVSServerRequest;

/**
 *  Environnement du serveur KVS
 */
final class KVSServerEnvironment implements IKVSServerEnvironment {
	/** @var IKVSUser[] $_users */
	private $_users;
	/** @var IKVSUserGroup[] $_groups */
	private $_groups;
	/** @var IKVSContainer[] $_containers */
	private $_containers;
	/** @var array $_admins */
	private $_admins;
	/** @var array $_sessions */
	private $_sessions;
	/** @var array $_groupDefs */
	private $_groupDefs;
	/** @var string $_dbPath */
	private $_dbPath;
	/** @var int $_ttl */
	private $_ttl;

	/**
	 * KVSServerEnvironment constructor.
	 *
	 * @param stdClass $users      Descripteurs d'utilisateur
	 * @param stdClass $groups     Descripteurs de groupes
	 * @param stdClass $admins     Descripteurs de droits d'administration
	 * @param stdClass $containers Descripteurs de conteners
	 * @param string   $dbPath     Repertoir parent dans lequel les container seront enregistrés par défaut.
	 * @param int      $ttl        (optionnel défaut : 900s) Temps de vie d'une session avant suppression pour inactivité.
	 */
	public function __construct(
		stdClass $users,
		stdClass $groups,
		stdClass $admins,
		stdClass $containers,
		string $dbPath,
		int $ttl = 900
	) {
		if(file_exists($dbPath)){
			$this->_dbPath = $dbPath;
		}else{
			mkdir($dbPath,0777,true);
		}
		$this->_ttl = $ttl;
		$this->_users = [];
		foreach($users as $userName=>$userInfos){
			if(isset($userInfos->password) && is_string($userInfos->password)){
				$this->_users[$userName] = new KVSUser($userName,$userInfos->password);
			}else{
				throw new \InvalidArgumentException("Invalid user $userName : missing '(string)password' field !");
			}
		}
		$this->_groups=[];
		$this->_groupDefs = [];
		foreach($groups as $groupName=>$users){
			if(is_array($users)){
				$tmp = [];
				foreach ($users as $k=>$userName){
					if(is_string($userName)){
						if(isset($this->_users[$userName])){
							$tmp[$userName] = $this->_users[$userName];
						}else{
							throw new \InvalidArgumentException("Unknown user $userName in group's declaration of $groupName !");
						}
					}else{
						throw new \InvalidArgumentException("Invalid group $groupName : string expected at offset $k but ".gettype($userName)." given !");
					}
				}
				$this->_groupDefs[$groupName] = $tmp;
				$this->_groups[$groupName] = new KVSUserGroup($groupName,$tmp);
			}else{
				throw new \InvalidArgumentException("Invalid group $groupName : expecting array but ".gettype($users)." given !");
			}
		}
		$this->_admins = [
			"users" => [],
			"groups" => []
		];
		if(isset($admins->users)){
			foreach($users as $userName=>$access){
				if(isset($this->_users[$userName])){
					$this->_admins["users"][$userName] = $access;
				}else{
					throw new \InvalidArgumentException("Unknown user in KVS admins users permission : $userName");
				}
			}
		}
		if(isset($admins->groups)){
			foreach($groups as $groupName=>$access){
				if(isset($this->_groups[$groupName])){
					$this->_admins["groups"][$groupName] = $access;
				}else{
					throw new \InvalidArgumentException("Unknown user group in KVS admins groups permission : $groupName");
				}
			}
		}
		$this->_containers = [];
		foreach($containers as $containerName=>$containerInfos){
			$defaultStorage = $containerInfos->default_storage ?? KVSModes::IN_MEMORY_PERSISTED_ON_DISK;
			if(is_string($defaultStorage)){
				$defaultStorage = KVSModes::get($defaultStorage);
			}
			if(!isset($containerInfos->permissions)){
				throw new \InvalidArgumentException("Container $containerName's permissions have to be defined !");
			}
			$this->_containers[$containerName] = new KVSContainer(
				$containerName,
				$containerInfos->permissions->users??new stdClass(),
				$containerInfos->permissions->groups??new stdClass(),
				$this->_groupDefs,
				$defaultStorage,
				$containerInfos->path ?? $dbPath,
				$containerInfos->logger,
				$containerInfos->enabled ?? true
			);
		}
		$this->_sessions = [];
	}

	/**
	 *  Vérifie les droit d'execution d'une requête d'administration du serveur pour un utilisateur donné.
	 *
	 * @param string $userName Nom de l'utilisateur
	 * @param string $requestClass Nom de la classe de la requête à tester
	 *
	 * @return bool
	 */
	public function isAdminAccessGranted(string $userName, string $requestClass):bool {
		if(isset($this->_admins["users"][$userName])){
			if($this->checkPermission($this->_admins["users"][$userName],$requestClass)){
				return true;
			}
		}
		foreach($this->_groupDefs as $groupName=>$users){
			if(!is_bool(array_search($userName,$users))){
				if(isset($this->_admins["groups"][$groupName])){
					if($this->checkPermission($this->_admins["groups"][$groupName],$requestClass)){
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 *  Determine si une permission est accordée d'après un profile d'accés et une requête.
	 *
	 * @param stdClass $access       Accés à tester
	 * @param string   $requestClass Nom de la classe de la requête à tester
	 *
	 * @return bool
	 */
	private function checkPermission(stdClass $access,string $requestClass):bool{
		if(isset($access->all) && $access->all) return true;
		else{
			if(is_a($requestClass,ShutdownKVSServerRequest::class,true)
				&& isset($access->shutdown)
				&& $access->shutdown){
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $name Nom du container
	 *
	 * @return IKVSContainer
	 */
	public function getContainer(string $name): IKVSContainer {
		if($this->existsContainer($name)) return $this->_containers[$name];
		else throw new ContainerNotFound("Unknwown container $name");
	}

	/**
	 *  Teste l'existence d'un container
	 *
	 * @param string $name Nom du container à tester
	 *
	 * @return bool
	 */
	public function existsContainer(string $name): bool {
		return isset($this->_containers[$name]);
	}

	/**
	 *  Retourne un utilisateur grâce à son nom.
	 *
	 * @param string $name Nom de l'utilisateur
	 *
	 * @return IKVSUser
	 */
	public function getUser(string $name): IKVSUser {
		if($this->existsUser($name)) return $this->_users[$name];
		else throw new UserNotFound("Unknown user $name !");
	}

	/**
	 *  Teste l'existence d'un utilisateur
	 *
	 * @param string $name Nom de l'utilisateur à tester
	 *
	 * @return bool
	 */
	public function existsUser(string $name): bool {
		return isset($this->_users[$name]);
	}

	/**
	 *  Retourne un groupe d'utilisateur
	 *
	 * @param string $name Nom du groupe
	 *
	 * @return IKVSUserGroup
	 */
	public function getUserGroup(string $name): IKVSUserGroup {
		if($this->existsUserGroup($name)) return $this->_groups[$name];
		else throw new UserGroupNotFound("Unknown user group : $name");
	}

	/**
	 *  Teste l'existence d'un groupe d'utilisateur
	 *
	 * @param string $name Nom du groupe à tester
	 *
	 * @return bool
	 */
	public function existsUserGroup(string $name): bool {
		return isset($this->_groups[$name]);
	}

	/**
	 *  Crée une session pour un utilisateur si ses informations de connexion sont valides.
	 *
	 * @param string $container            Container auquel l'utilisaeur tente la connexion
	 * @param string $login                Login de l'utilisateur
	 * @param string $password             Mot de passe de l'utilisateur
	 * @param int    $default_storage_mode Type de stoclage par défaut
	 *
	 * @return null|string Identifiant de session si la session a été créée, null sinon.
	 * @throws ContainerNotFound
	 * @throws UserNotFound
	 */
	public function createSessionForUser(
		string $container,
		string $login,
		string $password,
		?int $default_storage_mode = null
	): ?string {
		if($this->existsContainer($container) && $this->existsUser($login)){
			$container = $this->getContainer($container);
			if(is_null($default_storage_mode) || !KVSModes::existsValue($default_storage_mode)){
				$default_storage_mode = $container->getDefaultStorageMode();
			}
			$user = $this->getUser($login);
			if($user->matchPassword($password)){
				$session = new KVSUserSession($container,$user,$default_storage_mode);
				$this->_sessions[$session->getId()] = [
					"session" => $session,
					"expire_date" => microtime(true) + $this->_ttl
				];
				return $session->getId();
			}else return null;
		}else return null;
	}

	/**
	 *  Retourne une session grace à son identifiant.
	 *
	 * A chaque fois que la fonction est appelée, le temps avant suppression de la session doit être remis à 0.
	 *
	 * @param string $sessionId Identifiant de session
	 *
	 * @return null|IKVSUserSession
	 */
	public function getUserSession(string $sessionId): ?IKVSUserSession {
		if($this->existsUserSession($sessionId)){
			$this->touchUserSession($sessionId);
			return $this->_sessions[$sessionId]["session"];
		}else return null;
	}

	/**
	 *  Remet à 0 le compteur de suppression de la session.
	 *
	 * @param string $sessionId Identifiant de la session.
	 */
	public function touchUserSession(string $sessionId): void {
		if($this->existsUserSession($sessionId)){
			$this->_sessions[$sessionId]["expire_date"]=microtime(true) + $this->_ttl;
		}
	}

	/**
	 *  Teste l'existence d'une session
	 *
	 * @param string $sessionId Identifiant de la session à tester
	 *
	 * @return bool
	 */
	public function existsUserSession(string $sessionId): bool {
		return isset($this->_sessions[$sessionId]);
	}

	/**
	 *  Détruit la session d'un utilisateur
	 *
	 * @param string $sessionId Session à détruire.
	 */
	public function destroyUserSession(string $sessionId) {
		if($this->existsUserSession($sessionId)){
			unset($this->_sessions[$sessionId]);
		}
	}

	/**
	 *  Supprime les sessions inactives depuis un certain temps.
	 */
	public function destroyOutdatedSessions(): void {
		foreach($this->_sessions as $id=>$session){
			if($session["expire_date"]<time()){
				$this->destroyUserSession($id);
			}
		}
	}

	/**
	 * @return IKVSContainer[] Liste des containers
	 */
	public function getContainers(): array {
		return $this->_containers;
	}

	/**
	 * @return string Chemin du dossier d'enregistrement des containers par défaut.
	 */
	public function getDefaultDBPath():string{
		return $this->_dbPath;
	}
}