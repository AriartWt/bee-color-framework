<?php
namespace wfw\daemons\modelSupervisor\server\environment;

use stdClass;
use wfw\engine\core\conf\AbstractConf;
use wfw\engine\lib\PHP\errors\IllegalInvocation;

/**
 *  Environnement d'un composant MSServer
 */
final class MSServerComponentEnvironment extends AbstractConf implements IMSServerComponentEnvironment {
	/**
	 * @var string $_workingDir
	 */
	private $_workingDir;
	/**
	 * @var string $_name
	 */
	private $_name;
	/**
	 * @var array $_users
	 */
	private $_users;
	/**
	 * @var array $_groups
	 */
	private $_groups;

	/**
	 * MSServerComponentEnvironment constructor.
	 *
	 * @param string   $name       Nom du composant
	 * @param string   $workingDir Dossier de travail du composant
	 * @param stdClass $users      Liste des droits utilisateurs pour ce composant
	 * @param stdClass $groups     Liste des droits de groupes pour ce composant
	 * @param array    $groupDefs  Définition des groupes (stdClass type "groupeName"=>string[] (user names)
	 * @param stdClass $confs      Configurations du composant
	 */
	public function __construct(
		string $name,
		string $workingDir,
		stdClass $users,
		stdClass $groups,
		array $groupDefs,
		stdClass $confs)
	{
		parent::__construct($confs);
		if(!file_exists($workingDir)){
			if(file_exists(dirname($workingDir))){
				mkdir($workingDir,0777,true);
			}else{
				throw new \InvalidArgumentException("$workingDir is not a valide directory !");
			}
		}
		$this->_workingDir = $workingDir;
		$this->_name = $name;

		$this->_users = [];
		foreach($users as $name=>$access){
			$read = isset($access->read) && $access->read ? MSServerUserPermissions::READ:0;
			$write = isset($access->write) && $access->write ? MSServerUserPermissions::WRITE:0;
			$admin = isset($access->admin) && $access->admin ? MSServerUserPermissions::ADMIN:0;
			$this->_users[$name] = $read | $write | $admin;
		}

		$this->_groups = [];
		foreach($groups as $name=>$access){
			$read = isset($access->read) && $access->read ? MSServerUserPermissions::READ:0;
			$write = isset($access->write) && $access->write ? MSServerUserPermissions::WRITE:0;
			$admin = isset($access->admin) && $access->admin ? MSServerUserPermissions::ADMIN:0;
			if(isset($groupDefs[$name])){
				$this->_groups[$name] = [
					"users" => $groupDefs[$name],
					"access" => $read | $write | $admin
				];
			}else{
				throw new \InvalidArgumentException("Unknown group $name in groupDefs !");
			}
		}
	}

	/**
	 *  Retourne le chemin d'accés au fichier de configurations (le cas échéant)
	 * @return string|null
	 */
	public function getConfPath(): ?string
	{
		return null;
	}

	/**
	 *  Enregistre le fichier de configuration courant (écrase l'ancien)
	 *
	 */
	public function save():void
	{
		throw new IllegalInvocation("Cannot save a ComponentEnvironment !");
	}

	/**
	 * @return string Nom du container
	 */
	public function getName(): string
	{
		return $this->_name;
	}

	/**
	 * @return string Chemin d'accés au dossier de travail du component.
	 */
	public function getWorkingDir(): string
	{
		return $this->_workingDir;
	}

	/**
	 *  Teste l'accés d'un utilisater sur l'écriture, la lecture ou l'adminsitration du container.
	 *
	 * @param string $userName   Nom de l'utilisateur dont on souhaite tester les droits
	 * @param int    $permission Permission à tester
	 *
	 * @return bool
	 */
	public function isUserAccessGranted(string $userName, int $permission): bool
	{
		if(MSServerUserPermissions::existsValue($permission)){
			return $permission & ($this->_users[$userName] ?? 0)
				|| $permission & $this->getUserGroupPermission($userName);
		}else{
			throw new \InvalidArgumentException("$permission is not a valide permission !");
		}
	}

	/**
	 *  Retourne les permissions accordées par tous les groupes dont l'utilisateur $userName fait partie
	 * @param string $userName Nom de l'utilisateur
	 *
	 * @return int
	 */
	private function getUserGroupPermission(string $userName):int{
		$permission = 0;
		foreach($this->_groups as $k=>$v){
			if(!is_bool(array_search($userName,array_keys($v["users"])))){
				$permission |= $v["access"];
			}
		}
		return $permission;
	}
}