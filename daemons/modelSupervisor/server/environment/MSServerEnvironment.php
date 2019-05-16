<?php
namespace wfw\daemons\modelSupervisor\server\environment;

use stdClass;
use wfw\daemons\modelSupervisor\server\components\IMSServerComponentsInitializer;
use wfw\daemons\modelSupervisor\server\errors\ComponentNotFound;
use wfw\daemons\modelSupervisor\server\errors\UserGroupNotFound;
use wfw\daemons\modelSupervisor\server\errors\UserNotFound;
use wfw\daemons\modelSupervisor\server\requests\admin\IMSServerAdminRequest;
use wfw\daemons\modelSupervisor\server\requests\admin\ShutdownMSServerRequest;
use wfw\engine\core\data\model\loaders\IModelLoader;
use wfw\engine\lib\logger\ILogger;

/**
 *  Environnement du MSServer
 */
final class MSServerEnvironment implements IMSServerEnvironment {
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
	/** @var array $_components */
	private $_components;
	/** @var int $_ttl */
	private $_ttl;
	/** @var IModelLoader $_loader */
	private $_loader;

	/**
	 * MSServerEnvironment constructor.
	 *
	 * @param string       $workingDir   Dossier de travail du serveur.
	 * @param array        $initializers Tableau sous la forme "(string)name" => "(string)class"
	 *                                   où name est le nom du composant et class la classe permettant d'initialiser le composant.
	 *                                   Une telle classe doit étendre la classe ComponentInitializer
	 * @param IModelLoader $loader       Loader de models
	 * @param stdClass     $users        Liste des utilisateurs du serveur
	 * @param stdClass     $groups       Liste des groupes d'utilisateur du serveur
	 * @param stdClass     $admins       Liste des droits d'administration du serveur
	 * @param stdClass     $components   Liste des composants
	 * @param ILogger      $logger
	 * @param int          $sessionTtl   (optionnel defaut : 900) temps en secondes avant expiration d'une session inactive.
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		string $workingDir,
		array $initializers,
		IModelLoader $loader,
		stdClass $users,
		stdClass $groups,
		stdClass $admins,
		stdClass $components,
		ILogger $logger,
		int $sessionTtl = 900
	){
		if(!file_exists($workingDir)){
			if(file_exists(dirname($workingDir))){
				mkdir($workingDir);
			}else{
				throw new \InvalidArgumentException("$workingDir is not a valid directory !");
			}
		}
		$this->_workingDir = $workingDir;
		$this->_ttl = $sessionTtl;

		$this->_users = [];
		foreach($users as $userName=>$userInfos){
			if(isset($userInfos->password) && is_string($userInfos->password)){
				$this->_users[$userName] = new MSServerUser($userName,$userInfos->password);
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
				$this->_groups[$groupName] = new MSServerUserGroup($groupName,$tmp);
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

		foreach($initializers as $componentName=>$initializerClass){
			if(!is_a($initializerClass,IMSServerComponentsInitializer::class,true)){
				throw new \InvalidArgumentException("$initializerClass have to extends ".IMSServerComponentsInitializer::class);
			}
		}

		$this->_components = [];
		foreach($components as $componentName=>$componentInfos){
			if(!isset($initializers[$componentName])){
				throw new \InvalidArgumentException("No initializer defined for $componentName !");
			}
			$this->_components[$componentName] = new MSServerComponent(
				$loader->getModelList(),
				new MSServerComponentEnvironment(
					$componentName,
					$componentInfos->working_path ?? $workingDir.DS.$componentName,
					($componentInfos->permissions ?? new stdClass())->users ?? new stdClass(),
					($componentInfos->permissions ?? new stdClass())->groups ?? new stdClass(),
					$this->_groupDefs,
					$componentInfos,
					$logger
				),
				new $initializers[$componentName]()
			);
		}

		$this->_sessions = [];
	}

	/**
	 * @return string Repertoire de travail du serveur.
	 */
	public function getWorkingDir(): string {
		return $this->_workingDir;
	}

	/**
	 * @return IModelLoader Objet permettant de charger un model
	 */
	public function getModelLoader(): IModelLoader {
		return $this->_loader;
	}

	/**
	 * @param string $name Nom du composant à tester
	 *
	 * @return bool True si le composant existe, false sinon
	 */
	public function existsComponent(string $name): bool {
		return isset($this->_components[$name]);
	}

	/**
	 * @param string $name Nom du composant à obtenir
	 *
	 * @return IMSServerClientComponent
	 */
	public function getComponent(string $name): IMSServerClientComponent {
		if($this->existsComponent($name)){
			return $this->_components[$name];
		}else{
			throw new ComponentNotFound("Unknown component $name ");
		}
	}

	/**
	 * @return IMSServerClientComponent[] Retourne la liste des composants du MSServer
	 */
	public function getComponents(): array {
		return $this->_components;
	}

	/**
	 *  Vérifie les droit d'execution d'une requête d'administration du serveur pour un utilisateur donné.
	 *
	 * @param string                        $userName Nom de l'utilisateur
	 * @param IMSServerAdminRequest $request  Requête à tester
	 *
	 * @return bool
	 */
	public function isAdminAccessGranted(string $userName, IMSServerAdminRequest $request): bool {
		if(isset($this->_admins["users"][$userName])){
			if($this->checkPermission($this->_admins["users"][$userName],$request)){
				return true;
			}
		}
		foreach($this->_groupDefs as $groupName=>$users){
			if(!is_bool(array_search($userName,$users))){
				if(isset($this->_admins["groups"][$groupName])){
					if($this->checkPermission($this->_admins["groups"][$groupName],$request)){
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
	 * @param stdClass                      $access  Accés à tester
	 * @param IMSServerAdminRequest $request Requête
	 *
	 * @return bool
	 */
	private function checkPermission(stdClass $access,IMSServerAdminRequest $request):bool{
		if(isset($access->all) && $access->all){
			return true;
		}else{
			if($request instanceof ShutdownMSServerRequest
				&& isset($access->shutdown)
				&& $access->shutdown){
				return true;
			}
		}
		return false;
	}

	/**
	 *  Retourne un utilisateur grâce à son nom.
	 *
	 * @param string $name Nom de l'utilisateur
	 *
	 * @return IMSServerUser
	 */
	public function getUser(string $name): IMSServerUser {
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
	 * @return IMSServerUserGroup
	 */
	public function getUserGroup(string $name): IMSServerUserGroup {
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
				$session = new MSServerSession($user);
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
	 * @return IMSServerSession|null
	 */
	public function getUserSession(string $sessionId): ?IMSServerSession {
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
}