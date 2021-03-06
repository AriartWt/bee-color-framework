<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/08/18
 * Time: 18:09
 */

namespace wfw\daemons\rts\server\environment;

use stdClass;
use wfw\daemons\rts\server\errors\UserGroupNotFound;
use wfw\daemons\rts\server\errors\UserNotFound;
use wfw\engine\lib\logger\ILogger;

/**
 * Environment d'un serveur RTS
 */
final class RTSEnvironment implements IRTSEnvironment{
	/** @var string $_workingDir */
	private $_workingDir;
	/** @var array $_sessions */
	private $_sessions;
	/** @var array $_users */
	private $_users;
	/** @var array $_groups */
	private $_groups;
	/** @var array $_groupDefs */
	private $_groupDefs;
	/** @var array $_admins */
	private $_admins;
	/** @var int $_ttl */
	private $_ttl;
	/** @var ILogger $_logger */
	private $_logger;
	/** @var array $_modules */
	private $_modules;
	/** @var array $_allowedOrigins */
	private $_allowedOrigins;
	/** @var int $_maxSocketSelect */
	private $_maxSocketSelect;
	/** @var int $_maxReadBufferSize */
	private $_maxReadBufferSize;
	/** @var int $_maxWriteBufferSize */
	private $_maxWriteBufferSize;
	/** @var int $_maxConnectionsByIp */
	private $_maxConnectionsByIp;
	/** @var int $_maxRequestHandshakeSize */
	private $_maxRequestHandshakeSize;
	/** @var int $_maxRequestBySecondByClient */
	private $_maxRequestBySecondByClient;

	/**
	 * MSServerEnvironment constructor.
	 *
	 * @param string       $workingDir Dossier de travail du serveur.
	 * @param stdClass     $users      Liste des utilisateurs du serveur
	 * @param stdClass     $groups     Liste des groupes d'utilisateur du serveur
	 * @param stdClass     $admins     Liste des droits d'administration du serveur
	 * @param null|ILogger $logger
	 * @param array        $modulesToLoad
	 * @param int          $sessionTtl (optionnel defaut : 900) temps en secondes avant expiration d'une session inactive.
	 * @param int          $maxWriteBuffer
	 * @param int          $maxReadBuffer
	 * @param int          $maxRequestHandshakeSize
	 * @param array        $allowedOrigins
	 * @param int          $maxConnectionsByIp
	 * @param int          $maxRequestBySecondByClient
	 * @param int          $maxSocketSelect
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		string $workingDir,
		stdClass $users,
		stdClass $groups,
		stdClass $admins,
		ILogger $logger,
		array $modulesToLoad,
		int $sessionTtl = 900,
		int $maxWriteBuffer = 49152,
		int $maxReadBuffer = 49152,
		int $maxRequestHandshakeSize = 1024,
		array $allowedOrigins = [],
		int $maxConnectionsByIp = 20,
		int $maxRequestBySecondByClient = 20,
		int $maxSocketSelect = 1000
	) {
		if(!file_exists($workingDir)){
			if(file_exists(dirname($workingDir))){
				mkdir($workingDir);
			}else{
				throw new \InvalidArgumentException("$workingDir is not a valid directory !");
			}
		}
		$this->_logger = $logger;
		$this->_ttl = $sessionTtl;
		$this->_workingDir = $workingDir;
		$this->_modules = $modulesToLoad;
		$this->_allowedOrigins = $allowedOrigins;
		$this->_maxSocketSelect = $maxSocketSelect;
		$this->_maxReadBufferSize = $maxReadBuffer;
		$this->_maxWriteBufferSize = $maxWriteBuffer;
		$this->_maxConnectionsByIp = $maxConnectionsByIp;
		$this->_maxRequestHandshakeSize = $maxRequestHandshakeSize;
		$this->_maxRequestBySecondByClient = $maxRequestBySecondByClient;

		$this->_users = [];
		foreach($users as $userName=>$userInfos){
			if(isset($userInfos->password) && is_string($userInfos->password)){
				$this->_users[$userName] = new RTSUser($userName,$userInfos->password);
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
				$this->_groups[$groupName] = new RTSUserGroup($groupName,$tmp);
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

		$this->_sessions = [];
	}

	/**
	 * @return array
	 */
	public function getModules(): array {
		return $this->_modules;
	}

	/**
	 * @return array
	 */
	public function getAllowedOrigins(): array {
		return $this->_allowedOrigins;
	}

	/**
	 * @return int
	 */
	public function getMaxSocketSelect(): int {
		return $this->_maxSocketSelect;
	}

	/**
	 * @return int
	 */
	public function getMaxReadBufferSize(): int {
		return $this->_maxReadBufferSize;
	}

	/**
	 * @return int
	 */
	public function getMaxWriteBufferSize(): int {
		return $this->_maxWriteBufferSize;
	}

	/**
	 * @return int
	 */
	public function getMaxConnectionsByIp(): int {
		return $this->_maxConnectionsByIp;
	}

	/**
	 * @return int
	 */
	public function getMaxRequestHandshakeSize(): int {
		return $this->_maxRequestHandshakeSize;
	}

	/**
	 * @return int
	 */
	public function getMaxRequestBySecondByClient(): int {
		return $this->_maxRequestBySecondByClient;
	}

	/**
	 * @return string Repertoire de travail du serveur.
	 */
	public function getWorkingDir(): string {
		return $this->_workingDir;
	}

	/**
	 *  Retourne un utilisateur grâce à son nom.
	 *
	 * @param string $name Nom de l'utilisateur
	 *
	 * @return IRTSUser
	 * @throws UserNotFound
	 */
	public function getUser(string $name): IRTSUser{
		if($this->existsUser($name)){
			return $this->_users[$name];
		}else{
			throw new UserNotFound("Unknown user $name");
		}
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
	 * @return IRTSUserGroup
	 */
	public function getUserGroup(string $name): IRTSUserGroup {
		if($this->existsUserGroup($name)){
			return $this->_groups[$name];
		}else{
			throw new UserGroupNotFound("Unknown user group $name");
		}
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
	 * @param string $login    Login de l'utilisateur
	 * @param string $password Mot de passe de l'utilisateur
	 *
	 * @return null|string Identifiant de session si la session a été créée, null sinon.
	 */
	public function createSessionForUser(string $login, string $password): ?string {
		if($this->existsUser($login)){
			$user = $this->getUser($login);
			if($user->matchPassword($password)){
				$session = new RTSSession($user);
				$this->_sessions[$session->getId()] = [
					"session" => $session,
					"expire_date" => microtime(true) + $this->_ttl
				];
				return $session->getId();
			}else{
				return null;
			}
		}else{
			return null;
		}
	}

	/**
	 *  Retourne une session grace à son identifiant.
	 *
	 * A chaque fois que la fonction est appelée, le temps avant suppression de la session doit être remis à 0.
	 *
	 * @param string $sessionId Identifiant de session
	 *
	 * @return IRTSSession|null
	 */
	public function getUserSession(string $sessionId): ?IRTSSession {
		if($this->existsUserSession($sessionId)){
			$this->touchUserSession($sessionId);
			return $this->_sessions[$sessionId]["session"];
		}else{
			return null;
		}
	}

	/**
	 *  Remet à 0 le compteur de suppression de la session.
	 *
	 * @param string $sessionId Identifiant de la session.
	 */
	public function touchUserSession(string $sessionId): void {
		if($this->existsUserSession($sessionId)){
			$this->_sessions[$sessionId]["expire_date"] = microtime(true) + $this->_ttl;
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
			if($session["expire_date"] < microtime(true)){
				$this->destroyUserSession($id);
			}
		}
	}

	/**
	 * @return ILogger
	 */
	public function getLogger():ILogger{
		return $this->_logger;
	}
}