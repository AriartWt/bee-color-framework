<?php
namespace wfw\daemons\modelSupervisor\server\conf;

use stdClass;
use wfw\daemons\kvstore\server\conf\KVSConfs;
use wfw\daemons\modelSupervisor\server\IMSServerPoolConf;
use wfw\engine\core\conf\FileBasedConf;
use wfw\engine\core\conf\io\adapters\JSONConfIOAdapter;
use wfw\engine\lib\logger\SimpleLogFormater;
use wfw\engine\lib\logger\FileLogger;
use wfw\engine\lib\logger\ILogger;
use wfw\engine\lib\PHP\objects\StdClassOperator;
use wfw\engine\lib\PHP\types\PHPString;

/**
 * @brief Classe de configurations du MSServer en lisatn les fichiers de confs de ENGINE et SITE
 */
final class MSServerPoolConfs implements IMSServerPoolConf {
	private const DEFAULT_INSTANCE = "default_instance";
	private const WORKING_DIR = "working_dir";
	private const SOCKET_PATH = "socket_path";
	private const REQUEST_TTL = "request_ttl";
	private const LOGS = "logs/files";
	private const KVS_ADDR = "kvs/addr";

	/** @var FileBasedConf $_conf */
	private $_conf;
	/**  @var string $_basePath */
	private $_basePath;
	/** @var string $_kvsAddr */
	private $_kvsAddr;
	/** @var StdClassOperator[] $_instancesConfs */
	private $_instancesConfs = [];
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
	 * @param bool        $noLogger    (optionnel) Disable loggers
	 * @throws \InvalidArgumentException
	 * @throws \wfw\engine\lib\errors\InvalidTypeSupplied
	 * @throws \wfw\engine\lib\errors\PermissionDenied
	 */
	public function __construct(
		string $engineConfs,
		?string $siteConfs=null,
		string $basePath = DAEMONS,
		bool $noLogger = false
	){
		$this->_kvsAddr = (new KVSConfs($engineConfs,$siteConfs,DAEMONS,true))->getSocketPath();
		$this->_basePath = $basePath;
		$this->_instancesConfs = [];
		$confIO = new JSONConfIOAdapter();
		//On récupère les configurations générales
		$conf = new FileBasedConf($engineConfs,$confIO);
		if(file_exists($siteConfs)){
			$conf->merge(new FileBasedConf($siteConfs,$confIO));
		}

		//On cherche le chemin des configurations du daemon.
		$confPath = $conf->getString("server/daemons/model_supervisor");
		if(is_null($confPath)){
			throw new \InvalidArgumentException("Config key server/daemons/model_supervisor not found");
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

		if(!$noLogger) $this->_logger = (new FileLogger(new SimpleLogFormater(),...[
			$this->getLogPath(null,"log"),
			$this->getLogPath(null,"err"),
			$this->getLogPath(null,"warn"),
			$this->getLogPath(null,"debug")
		]))->autoConfFileByLevel(
			FileLogger::ERR | FileLogger::WARN | FileLogger::LOG,
			FileLogger::DEBUG,
			$this->isCopyLogModeEnabled()
		)->autoConfByLevel($this->_conf->getInt("logs/level") ?? ILogger::ERR);

		//On détermine les configurations de chaque instance à créer
		$defInstance = $this->_conf->getObject(self::DEFAULT_INSTANCE);
		foreach($this->_conf->getArray("instances") as $instanceName=>$instanceConf){
			$tmp = new StdClassOperator(new stdClass());
			$tmp->mergeStdClass($defInstance);
			$tmp->mergeStdClass($instanceConf);
			try{
				$path = $tmp->find("project_path");
				if(file_exists("$path/site/config/conf.json")){
					$tmpConf = new FileBasedConf($path,new JSONConfIOAdapter());
					$custom_conf = $tmpConf->getObject("server/daemons/custom_config/msserver");
					if(!is_null($custom_conf)) $tmp->mergeStdClass($custom_conf);
				}
			}catch(\Exception $e){}
			$this->_instancesConfs[$instanceName] = $tmp;
			if(!$noLogger)
			$this->_instanceLoggers[$instanceName] = (new FileLogger(new SimpleLogFormater(),...[
				$this->getLogPath($instanceName,"log"),
				$this->getLogPath($instanceName,"err"),
				$this->getLogPath($instanceName,"warn"),
				$this->getLogPath($instanceName,"debug")
			]))->autoConfFileByLevel(
				FileLogger::ERR | FileLogger::WARN | FileLogger::LOG,
				FileLogger::DEBUG,
				$this->isCopyLogModeEnabled($instanceName)
			)->autoConfByLevel($this->_conf->getInt("instances/$instanceName/logs/level")
				?? $this->_conf->getInt("logs/level") ?? ILogger::ERR
			);
		}
	}

	/**
	 * @return FileBasedConf
	 */
	public function getConfFile():FileBasedConf{
		return $this->_conf;
	}

	/**
	 * @brief Obtient le dossier de travail par défaut. Si aucun dossier de travail n'est défini,
	 *        le dossier DAEMONS."/modelSupervisor/data" est utilisé.
	 * @param null|string $instance Nom de l'instance dont on souhaite obtenir le dossier de travail
	 *                              Si non précisé, renvoie le dossier de travail global
	 * @return string
	 */
	public function getWorkingDir(?string $instance=null):string{
		if(!$instance) $instance = '';
		else $instance = "/$instance";

		return $this->resolvePath(
			$this->_conf->getString(self::WORKING_DIR).$instance
			?? $this->_basePath.DS."modelSupervisor/data$instance",false);
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
	 * @param null|string $instance Chemin de la socket d'une DB particulière
	 * @return string
	 */
	public function getSocketPath(?string $instance=null):string{
		if($instance) return $this->getWorkingDir($instance)."/$instance.socket";
		else{
			$socketPath = new PHPString(
				$this->resolvePath($this->_conf->getString(self::SOCKET_PATH))
			);
			if(!$socketPath->startBy("/")){
				return $this->getWorkingDir().$socketPath;
			}else{
				return $socketPath;
			}
		}
	}

	/**
	 * @param string $instance Instance dont on souhaite connaitre les utilisateurs
	 * @return stdClass
	 * @throws \InvalidArgumentException
	 */
	public function getUsers(string $instance): stdClass{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_instancesConfs[$instance]->find("users");
	}

	/**
	 * @param string $instance Instance dont on souhaite connaitre les groupes
	 * @return stdClass
	 * @throws \InvalidArgumentException
	 */
	public function getGroups(string $instance): stdClass{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		try{
			return $this->_instancesConfs[$instance]->find("groups");
		}catch(\Exception $e){
			return new stdClass;
		}
	}

	/**
	 * @param string $instance Instance dont ou souhaite connaitre les admins
	 * @return stdClass
	 * @throws \InvalidArgumentException
	 */
	public function getAdmins(string $instance):stdClass{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		try{
			return $this->_instancesConfs[$instance]->find("admins");
		}catch(\Exception $e){
			return new stdClass;
		}
	}

	/**
	 * @param string $instance
	 * @return stdClass
	 */
	public function getComponents(string $instance):stdClass{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		$components = (new StdClassOperator(
			$this->_instancesConfs[$instance]->find("components")))->getStdClassCopy();
		foreach($components as $component){
			if(isset($component->kvs)) $component->kvs->addr = $this->getKVSAddr();
		}
		return $components;
	}

	/**
	 * @param string $instance Instance dont ou souhaite connaître le temps de vie des sessions
	 * @return int
	 */
	public function getSessionTtl(string $instance):int{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		try{
			return $this->_instancesConfs[$instance]->find("session_ttl");
		}catch(\Exception $e){
			return 60;
		}
	}

	/**
	 * @param null|string $instance Instance dont on souhaite connaitre le temps de vie des requetes
	 * @return int
	 */
	public function getRequestTtl(?string $instance = null):int{
		if($instance){
			if(!isset($this->_instancesConfs[$instance]))
				throw new \InvalidArgumentException("Unknown instance $instance");
			try{
				return $this->_instancesConfs[$instance]->find("request_ttl");
			}catch(\Exception $e){
				return 900;
			}
		}else return $this->_conf->getInt(self::REQUEST_TTL)??900;
	}

	/**
	 * @param string $instance Instance concernée
	 * @return bool
	 */
	public function haveToSendErrorToClient(string $instance):bool{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		try{
			return $this->_instancesConfs[$instance]->find("send_error_to_client");
		}catch(\Exception $e){
			return true;
		}
	}

	/**
	 * @param string $instance Instance concernée
	 * @return bool
	 */
	public function haveToShutdownOnError(string $instance):bool{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		try{
			return $this->_instancesConfs[$instance]->find("shutdown_on_error");
		}catch(\Exception $e){
			return false;
		}
	}

	/**
	 * @param null|string $instance Instance concernée
	 * @param string      $level
	 * @return string
	 */
	public function getLogPath(?string $instance,string $level="err"):string{
		if($instance) $path = $this->_conf->getString("instances/$instance/".self::LOGS."/$level");
		else $path = $this->_conf->getString(self::LOGS."/$level");
		$errorPath = $path ?? "msserver".(($instance)?"-$instance":"")."-$level.log";

		if(strpos($errorPath,"/")!==0){
			if($instance) $basePath = $this->_conf->getString("instances/$instance/logs/default_path")
										?? $this->_conf->getString("logs/default_path")."/instances";
			else $basePath = $this->_conf->getString("logs/default_path");
			if(!$basePath) $basePath = $this->getWorkingDir($instance);
			if(!is_dir($basePath)) mkdir($basePath,0700,true);
			return "$basePath/$errorPath";
		}else{
			return $errorPath;
		}
	}

	/**
	 * @param string $instance Instance concernée
	 * @return string
	 */
	public function getInitializersPath(string $instance):string{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		$path=null;
		try{
			$path = $this->_instancesConfs[$instance]->find("initializers_path");
		}catch(\Exception $e){
			$path = "{ROOT}/daemons/model_supervisor/config/initializers.components.php";
		}
		return $this->resolvePath($path,false);
	}

	/**
	 * @param string $instance Instance concernée
	 * @return string
	 */
	public function getModelsToLoadPath(string $instance):string{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		$path = null;

		try{
			$path = $this->_instancesConfs[$instance]->find("models_to_load_path");
		}catch(\Exception $e){
			$path = "{ROOT}/engine/config/default.models.php";
		}

		$modelsToLoad = new PHPString($path);
		if(!$modelsToLoad->startBy("/")){
			return $this->resolvePath($modelsToLoad,false);
		}else{
			return $modelsToLoad;
		}
	}

	/**
	 * @param string $instance Instance concernée
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getKVSLogin(string $instance):string{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_instancesConfs[$instance]->find("kvs/login");
	}

	/**
	 * @param string $instance instance concernée
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getKVSPassword(string $instance):string{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_instancesConfs[$instance]->find("kvs/password");
	}

	/**
	 * @param string $instance Instance concernée
	 * @return string
	 */
	public function getKVSContainer(string $instance):string{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_instancesConfs[$instance]->find("kvs/container");
	}

	/**
	 * @param string $instance Instance concernée
	 * @return null|string
	 * @throws \InvalidArgumentException
	 */
	public function getKVSDefaultStorage(string $instance):?string{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		try{
			return $this->_instancesConfs[$instance]->find("kvs/default_storage");
		}catch(\Exception $e){
			return null;
		}
	}

	/**
	 * @return string
	 */
	public function getKVSAddr():string{
		return $this->_conf->getString(self::KVS_ADDR) ?? $this->_kvsAddr;
	}

	/**
	 * @return string[] Noms des instances à créer
	 */
	public function getInstances():array{
		return array_keys($this->_instancesConfs);
	}

	/**
	 * @param null|string $instance
	 * @return ILogger
	 */
	public function getLogger(?string $instance=null): ILogger {
		if($instance && !isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return (is_null($instance) ? $this->_logger : $this->_instanceLoggers[$instance]);
	}

	/**
	 * @param null|string $instance
	 * @return bool
	 */
	public function isCopyLogModeEnabled(?string $instance=null):bool{
		$res = $this->_conf->getBoolean(($instance ? "instances/$instance/" : "") ."logs/copy");
		if(is_null($res)) return true;
		else return $res;
	}

	/**
	 * @param null|string $instance
	 * @return null|string
	 */
	public function getAdminMailAddr(?string $instance=null):?string{
		if($instance) return $this->_conf->getString("instances/$instance/admin_mail") ?? $this->getAdminMailAddr();
		else return $this->_conf->getString("admin_mail");
	}
}