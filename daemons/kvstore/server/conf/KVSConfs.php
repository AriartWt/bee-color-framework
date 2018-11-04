<?php
namespace wfw\daemons\kvstore\server\conf;

use stdClass;
use wfw\engine\core\conf\FileBasedConf;
use wfw\engine\core\conf\io\adapters\JSONConfIOAdapter;
use wfw\engine\lib\PHP\types\PHPString;

/**
 *  Permet d'obtenir les configurations du serveur KVS en lisant les fichiers de configuration
 *  dans SITE et ENGINE
 */
final class KVSConfs {
	private const WORKING_DIR = "working_dir";
	private const SOCKET_PATH = "socket_path";
	private const DB_PATH = "db_path";
	private const USERS = "users";
	private const GROUPS = "groups";
	private const ADMINS = "admins";
	private const CONTAINERS = "containers";
	private const SESSION_TTL = "session_ttl";
	private const REQUEST_TTL = "request_ttl";
	private const SEND_ERROR_TO_CLIENT = "send_error_to_client";
	private const SHUTDOWN_ON_ERROR = "shutdown_on_error";
	private const ERROR_LOGS = "error_logs";

	/** @var FileBasedConf $_conf */
	private $_conf;
	/** @var string $_basePath */
	private $_basePath;

	/**
	 * KVSConfs constructor.
	 *
	 * @param string      $engineConfs Données de configuration générales
	 * @param null|string $siteConfs   (optionnel) données de configuration du site
	 * @param string      $basePath    (optionnel defaut : DAEMONS) chemin absolu permettant la résolution du chemin relatif des fichiers.
	 *                                 Si le chemin commence par {ROOT}, {ROOT} est remplacé par la valeur de la constante ROOT.
	 */
	public function __construct(string $engineConfs,?string $siteConfs=null,string $basePath = DAEMONS) {
		$this->_basePath = $this->resolvePath($basePath);

		$confIO = new JSONConfIOAdapter();
		//On récupère les configurations générales
		$conf = new FileBasedConf($engineConfs,$confIO);
		if(file_exists($siteConfs)){
			$conf->merge(new FileBasedConf($siteConfs,$confIO));
		}

		//On cherche le chemin des configurations du daemon.
		$confPath = $conf->getString("server/daemons/kvs");
		if(is_null($confPath)){
			throw new \InvalidArgumentException("Config key server/daemons/kvs not found");
		}

		$confPath = new PHPString($confPath);
		if(!$confPath->startBy("/")){
			$confPath = $basePath.DS.$confPath;
		}else{
			$confPath = (string) $confPath;
		}

		$this->_conf = new FileBasedConf($confPath,$confIO);
	}

	/**
	 * @return FileBasedConf
	 */
	public function getConfFile():FileBasedConf{
		return $this->_conf;
	}

	/**
	 * Obtient le dossier de travail par défaut. Si aucun dossier de travail n'est défini,
	 * le dossier DAEMONS."/kvstore/default_working_dir" est utilisé.
	 *
	 * @return string
	 */
	public function getWorkingDir():string{
		return $this->resolvePath(
			$this->_conf->getString(self::WORKING_DIR)
			?? $this->_basePath.DS."kvstore".DS."data",false);
	}

	/**
	 * Tente de résoudre un chemin relatif. Si le chemin est absolu, il est inchangé.
	 * Si le chemin commence par {ROOT}, {ROOT} est remplacé par la valeur de la constante ROOT.
	 *
	 * @param string $path                  Chemin à résoudre.
	 * @param bool $useWorkingPathAsbase (optionnel defaut : true) Si true : le chemin de base utilisé est
	 *                                   le résultat de getWorkingPath, sinon $this->_basePath
	 *
	 * @return string
	 */
	private function resolvePath(string $path,bool $useWorkingPathAsbase = true):string{
		$path = new PHPString($path);
		if(!$path->startBy("/")){
			if($path->startBy("{ROOT}")){
				return $path->replaceAll("{ROOT}",ROOT);
			}else{
				if($useWorkingPathAsbase){
					return $this->getWorkingDir().DS.$path;
				}else{
					return $this->_basePath.DS.$path;
				}
			}
		}else{
			return $path;
		}
	}

	/**
	 * @return string
	 */
	public function getSocketPath():string{
		$socketPath = new PHPString(
			$this->resolvePath($this->_conf->getString(self::SOCKET_PATH))
		);
		if(!$socketPath->startBy("/")){
			return $this->getWorkingDir().DS.$socketPath;
		}else{
			return $socketPath;
		}
	}

	/**
	 * @return string
	 */
	public function getDbPath():string{
		$dbPath = new PHPString(
			$this->resolvePath($this->_conf->getString(self::DB_PATH))
		);
		if(!$dbPath->startBy("/")){
			return $this->getWorkingDir().DS.$dbPath;
		}else{
			return $dbPath;
		}
	}

	/**
	 * @return stdClass
	 * @throws \wfw\engine\lib\errors\InvalidTypeSupplied
	 */
	public function getUsers(): stdClass{
		return $this->_conf->getObject(self::USERS);
	}

	/**
	 * @return stdClass
	 * @throws \wfw\engine\lib\errors\InvalidTypeSupplied
	 */
	public function getGroups(): stdClass{
		return $this->_conf->getObject(self::GROUPS)??new stdClass();
	}

	/**
	 * @return stdClass
	 * @throws \wfw\engine\lib\errors\InvalidTypeSupplied
	 */
	public function getAdmins():stdClass{
		return $this->_conf->getObject(self::ADMINS)??new stdClass();
	}

	/**
	 * @return stdClass
	 * @throws \wfw\engine\lib\errors\InvalidTypeSupplied
	 */
	public function getContainers():stdClass{
		return $this->_conf->getObject(self::CONTAINERS);
	}

	/**
	 * @return int
	 */
	public function getSessionTtl():int{
		return $this->_conf->getInt(self::SESSION_TTL)??60;
	}

	/**
	 * @return int
	 */
	public function getRequestTtl():int{
		return $this->_conf->getInt(self::REQUEST_TTL)??900;
	}

	/**
	 * @return bool
	 */
	public function haveToSendErrorToClient():bool{
		return $this->_conf->getBoolean(self::SEND_ERROR_TO_CLIENT);
	}

	/**
	 * @return bool
	 */
	public function haveToShutdownOnError():bool{
		return $this->_conf->getBoolean(self::SHUTDOWN_ON_ERROR);
	}

	/**
	 * @return string
	 */
	public function getErrorLogsPath():string{
		$errorPath = new PHPString(
			$this->resolvePath($this->_conf->getString(self::ERROR_LOGS)??"error_logs.txt")
		);
		if(!$errorPath->startBy("/")){
			return $this->getWorkingDir().DS.$errorPath;
		}else{
			return $errorPath;
		}
	}
}