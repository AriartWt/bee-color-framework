<?php
namespace wfw\daemons\kvstore\server\conf;

use stdClass;
use wfw\engine\core\conf\FileBasedConf;
use wfw\engine\core\conf\io\adapters\JSONConfIOAdapter;
use wfw\engine\lib\logger\SimpleLogFormater;
use wfw\engine\lib\logger\FileLogger;
use wfw\engine\lib\logger\ILogger;
use wfw\engine\lib\PHP\objects\StdClassOperator;
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
	private const LOGS = "logs/files";

	/** @var FileBasedConf $_conf */
	private $_conf;
	/** @var string $_basePath */
	private $_basePath;
	/** @var ILogger $_logger */
	private $_logger;
	/** @var ILogger[] $_instanceLoggers */
	private $_instanceLoggers=[];

	/**
	 * KVSConfs constructor.
	 *
	 * @param string      $engineConfs Données de configuration générales
	 * @param null|string $siteConfs   (optionnel) données de configuration du site
	 * @param string      $basePath    (optionnel defaut : DAEMONS) chemin absolu permettant la résolution du chemin relatif des fichiers.
	 *                                 Si le chemin commence par {ROOT}, {ROOT} est remplacé par la valeur de la constante ROOT.
	 * @param bool        $noLogger    (optionnel) Désactive les loggers
	 * @throws \InvalidArgumentException
	 * @throws \wfw\engine\lib\errors\InvalidTypeSupplied
	 * @throws \wfw\engine\lib\errors\PermissionDenied
	 */
	public function __construct(string $engineConfs,?string $siteConfs=null,string $basePath = DAEMONS,bool $noLogger=false) {
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

		$workingDir = $this->getWorkingDir();
		if(!is_dir($workingDir)) mkdir($workingDir,0700,true);

		if(!$noLogger){
			$this->_logger = (new FileLogger(new SimpleLogFormater(),...[
				$this->getLogPath(null,"log"),
				$this->getLogPath(null,"err"),
				$this->getLogPath(null,"warn"),
				$this->getLogPath(null,"debug")
			]))->autoConfFileByLevel(
				FileLogger::ERR | FileLogger::WARN | FileLogger::LOG,
				FileLogger::DEBUG,
				$this->isCopyLogModeEnabled(null)
			)->autoConfByLevel($this->_conf->getInt("logs/level") ?? ILogger::ERR);

			foreach($this->getContainers(false) as $containerName=>$data){
				try{
					$path = $this->_conf->getString("containers/$containerName/project_path");
					if(file_exists("$path/site/config/conf.json")){
						$tmpConf = new FileBasedConf($path,new JSONConfIOAdapter());
						$customConf = $tmpConf->getObject("server/daemons/custom_config/kvs");
						if(!is_null($customConf)){
							$currentConf = new StdClassOperator($this->_conf->getObject("containers/$containerName"));
							$currentConf->mergeStdClass($customConf);
							$this->_conf->set("containers/$containerName",$currentConf->getStdClass());
						}
					}
				}catch(\Exception $e){}
				$this->_instanceLoggers[$containerName] = (new FileLogger(new SimpleLogFormater(),...[
					$this->getLogPath($containerName,"log"),
					$this->getLogPath($containerName,"err"),
					$this->getLogPath($containerName,"warn"),
					$this->getLogPath($containerName,"debug")
				]))->autoConfFileByLevel(
					FileLogger::ERR | FileLogger::WARN | FileLogger::LOG,
					FileLogger::DEBUG,
					$this->isCopyLogModeEnabled($containerName)
				)->autoConfByLevel($this->_conf->getInt("containers/$containerName/logs/level")
				                   ?? $this->_conf->getInt("logs/level") ?? ILogger::ERR
				);
			}
		}
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
	 * @param null|string $container
	 * @return string
	 */
	public function getWorkingDir(?string $container=null):string{
		if(!$container) $container = '';
		else $container = "/$container";
		return $this->resolvePath(
			$this->_conf->getString(self::WORKING_DIR)
			?? $this->_basePath."/kvstore/data$container",false);
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
	 * @param bool $withLoggers
	 * @return stdClass
	 * @throws \InvalidArgumentException
	 * @throws \wfw\engine\lib\errors\InvalidTypeSupplied
	 */
	public function getContainers(bool $withLoggers = true):stdClass{
		$res = $this->_conf->getObject(self::CONTAINERS);
		if($withLoggers){
			foreach($res as $name=>$container){
				$container->logger = $this->getLogger($name);
			}
		}
		return $res;
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

	/**
	 * @param null|string $container
	 * @return bool|null
	 */
	public function isCopyLogModeEnabled(?string $container=null):?bool{
		$res = $this->_conf->getBoolean(($container ? "containers/$container/" : "") ."logs/copy");
		if(is_null($res)) return true;
		else return $res;
	}

	/**
	 * @param null|string $container
	 * @param string      $level
	 * @return null|string
	 */
	private function getLogPath(?string $container,string $level="err"):?string{
		if($container) $path = $this->_conf->getString("containers/$container/".self::LOGS."/$level");
		else $path = $this->_conf->getString(self::LOGS."/$level");
		$errorPath = $path ?? "kvs".(($container)?"-$container":"")."-$level.log";

		if(strpos($errorPath,"/")!==0){
			if($container) $basePath = $this->_conf->getString("containers/$container/logs/default_path")
										?? $this->_conf->getString("logs/default_path")."/containers";
			else $basePath = $this->_conf->getString("logs/default_path");
			if(!$basePath) $basePath = $this->getWorkingDir($container);
			if(!is_dir($basePath)) mkdir($basePath,0700,true);
			return "$basePath/$errorPath";
		}else{
			return $errorPath;
		}
	}

	/**
	 * @param null|string $container
	 * @return ILogger
	 */
	public function getLogger(?string $container=null):ILogger{
		if($container && !isset($this->_instanceLoggers[$container]))
			throw new \InvalidArgumentException("No Logger defined for $container !");
		return (is_null($container) ? $this->_logger : $this->_instanceLoggers[$container]);
	}
	/**
	 * @param null|string $container
	 * @return null|string
	 */
	public function getAdminMailAddr(?string $container=null):?string{
		if($container) return $this->_conf->getString("containers/$container/admin_mail") ?? $this->getAdminMailAddr();
		else return $this->_conf->getString("admin_mail");
	}
}