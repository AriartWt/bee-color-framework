<?php
namespace wfw\daemons\kvstore\server\environment;

use stdClass;
use wfw\daemons\kvstore\server\KVSModes;
use wfw\engine\lib\logger\ILogger;

/**
 *  Container kvs
 */
final class KVSContainer implements IKVSContainer {
	/** @var string $_name */
	private $_name;
	/** @var array $_users */
	private $_users;
	/** @var string $_dbPath */
	private $_dbPath;
	/** @var array $_groups */
	private $_groups;
	/** @var int $_defaultStorageMode */
	private $_defaultStorageMode;
	/** @var ILogger $_logger */
	private $_logger;
	/** @var bool $_enabled */
	private $_enabled;

	/**
	 * KVSContainer constructor.
	 *
	 * @param string   $name               Nom du container
	 * @param stdClass $users              Permissions sur les utilisateurs
	 * @param stdClass $groups             Permissions sur les groupes
	 * @param array    $groupDefs          Définition des groupes (stdClass type "groupeName"=>string[] (user names)
	 * @param int      $defaultStorageMode Mode de stockage par défaut du container
	 * @param string   $dbPath             Chemin d'accés au repertoire parent du container
	 * @param ILogger  $logger
	 * @param bool     $enabled
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		string $name,
		stdClass $users,
		stdClass $groups,
		array $groupDefs,
		int $defaultStorageMode,
		string $dbPath,
		ILogger $logger,
		bool $enabled = true
	) {
		$this->_enabled = $enabled;
		$this->_logger = $logger;
		if(KVSModes::existsValue($defaultStorageMode)){
			$this->_defaultStorageMode = $defaultStorageMode;
			$this->_name = $name;
			if(file_exists($dbPath)){
				$this->_dbPath = $dbPath;
				if(!file_exists($this->getSavePath())){
					mkdir($this->getSavePath());
				}
			}else{
				throw new \InvalidArgumentException("$dbPath is not a valide directory !");
			}

			$this->_users = [];
			foreach($users as $name=>$access){
				$read = isset($access->read) && $access->read ? KVSUserPermissions::READ:0;
				$write = isset($access->write) && $access->write ? KVSUserPermissions::WRITE:0;
				$admin = isset($access->admin) && $access->admin ? KVSUserPermissions::ADMIN:0;
				$this->_users[$name] = $read | $write | $admin;
			}

			$this->_groups = [];
			foreach($groups as $name=>$access){
				$read = isset($access->read) && $access->read ? KVSUserPermissions::READ:0;
				$write = isset($access->write) && $access->write ? KVSUserPermissions::WRITE:0;
				$admin = isset($access->admin) && $access->admin ? KVSUserPermissions::ADMIN:0;
				if(isset($groupDefs[$name])){
					$this->_groups[$name] = [
						"users" => $groupDefs[$name],
						"access" => $read | $write | $admin
					];
				}else{
					throw new \InvalidArgumentException("Unknown group $name in groupDefs !");
				}
			}
		}else{
			throw new \InvalidArgumentException("$defaultStorageMode is not a valide storage mode !");
		}
	}

	/**
	 * @return string Nom du container
	 */
	public function getName(): string {
		return $this->_name;
	}

	/**
	 *  Teste l'accés d'un utilisater sur l'écriture, la lecture ou l'adminsitration du container.
	 *
	 * @param string $userName   Nom de l'utilisateur dont on souhaite tester les droits
	 * @param int    $permission Permission à tester
	 *
	 * @return bool
	 */
	public function isUserAccessGranted(string $userName, int $permission): bool {
		if(KVSUserPermissions::existsValue($permission)){
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

	/**
	 * @return int Retourne le mode de stockage des données par défaut pour se container.
	 */
	public function getDefaultStorageMode(): int {
		return $this->_defaultStorageMode;
	}

	/**
	 * @return string Chemin d'accés au repertoir du container.
	 */
	public function getSavePath(): string {
		return $this->_dbPath.'/'.$this->getName();
	}

	/**
	 * @return ILogger
	 */
	public function getLogger(): ILogger {
		return $this->_logger;
	}

	/**
	 * @return bool True if the container may start, false otherwise
	 */
	public function enabled(): bool {
		return $this->_enabled;
	}
}