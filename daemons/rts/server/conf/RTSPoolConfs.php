<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/08/18
 * Time: 13:38
 */

namespace wfw\daemons\rts\server\conf;
use stdClass;
use wfw\engine\core\conf\FileBasedConf;
use wfw\engine\core\conf\io\adapters\JSONConfIOAdapter;
use wfw\engine\lib\PHP\objects\StdClassOperator;
use wfw\engine\lib\PHP\types\PHPString;

/**
 * Class RTSPoolConfs
 *
 * @package wfw\daemons\rts\server\conf
 */
final class RTSPoolConfs {
	private const DEFAULT_INSTANCE = "default_instance";
	private const WORKING_DIR = "working_dir";
	private const SOCKET_PATH = "socket_path";
	private const REQUEST_TTL = "request_ttl";
	private const DEF_LOGS = "log_file";
	private const ERR_LOGS = "err_logs";
	private const WARN_LOGS = "warn_logs";
	private const DEBUG_LOGS = "debug_logs";
	private const DEBUG_LEVEL = "debug_level";
	private const LOG_LEVEL = "log_level";

	/** @var FileBasedConf $_conf */
	private $_conf;
	/**  @var string $_basePath */
	private $_basePath;
	/** @var StdClassOperator[] $_instancesConfs */
	private $_instancesConfs = [];

	/**
	 * KVSConfs constructor.
	 *
	 * @param string      $engineConfs Données de configuration générales
	 * @param null|string $siteConfs   (optionnel) données de configuration du site
	 * @param string $basePath (optionnel defaut : DAEMONS) chemin absolu permettant la
	 *                         résolution du chemin relatif des fichiers.
	 */
	public function __construct(
		string $engineConfs,
		?string $siteConfs=null,
		string $basePath = DAEMONS
	){
		$this->_basePath = $basePath;
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

		//On détermine les configurations de chaque instance à créer
		$this->_instancesConfs = [];
		$defInstance = $this->_conf->getObject(self::DEFAULT_INSTANCE);
		foreach($this->_conf->getArray("instances") as $instanceName=>$instanceConf){
			$tmp = new StdClassOperator(new stdClass());
			$tmp->mergeStdClass($defInstance);
			$tmp->mergeStdClass($instanceConf);
			$this->_instancesConfs[$instanceName] = $tmp;
		}
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
			?? $this->_basePath.DS."rts/data$instance",false);
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
	 * @param string $instance Instance dont on souhaite connaitre le niveau de debug
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function getDebugLevel(string $instance): int{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_instancesConfs[$instance]->find(self::DEBUG_LEVEL) ?? 0;
	}

	/**
	 * @param string $instance Instance dont on souhaite le nombre maximum de sockets par worker
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function getMaxWSockets(string $instance): int{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_instancesConfs[$instance]->find("max_wsockets")??1000;
	}

	/**
	 * @param string $instance Instance dont on souhaite le nombre maximum de worker
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function getMaxWorkers(string $instance): int{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_instancesConfs[$instance]->find("max_workers")??0;
	}

	/**
	 * @param string $instance Instance dont on souhaite le nombre maximum de fois que l'on peut
	 *                         dépasser max_wsockets lorsque le nombre max_worker est atteint.
	 *                         -1 : pas de limite, n > 0 : max_wsockets * n+1
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function getAllowedWSocketOverflow(string $instance): int{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_instancesConfs[$instance]->find("allowed_wsockets_overflow")??-1;
	}

	/**
	 * @param string $instance  Instance dont on souhaite connaitre le port
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function getPort(string $instance): int{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_instancesConfs[$instance]->find("port");
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
	 * @return string
	 */
	public function getErrorLogsPath(?string $instance=null):string{
		$errorPath = new PHPString(
			$this->resolvePath($this->_conf->getString(self::ERR_LOGS)??"err.log")
		);
		if(!$errorPath->startBy("/")){
			return $this->getWorkingDir($instance).DS.$errorPath;
		}else{
			return $errorPath;
		}
	}

	/**
	 * @param null|string $instance Instance concernée
	 * @return string
	 */
	public function getWarningLogsPath(?string $instance=null):string{
		$errorPath = new PHPString(
			$this->resolvePath($this->_conf->getString(self::WARN_LOGS)??"warn.log")
		);
		if(!$errorPath->startBy("/")){
			return $this->getWorkingDir($instance).DS.$errorPath;
		}else{
			return $errorPath;
		}
	}

	/**
	 * @param null|string $instance Instance concernée
	 * @return string
	 */
	public function getDebugLogsPath(?string $instance=null):string{
		$errorPath = new PHPString(
			$this->resolvePath($this->_conf->getString(self::DEBUG_LOGS)??"debug.log")
		);
		if(!$errorPath->startBy("/")){
			return $this->getWorkingDir($instance).DS.$errorPath;
		}else{
			return $errorPath;
		}
	}

	/**
	 * @param null|string $instance Instance concernée
	 * @return string
	 */
	public function getLogsPath(?string $instance=null):string{
		$errorPath = new PHPString(
			$this->resolvePath($this->_conf->getString(self::DEF_LOGS)??"rts.log")
		);
		if(!$errorPath->startBy("/")){
			return $this->getWorkingDir($instance).DS.$errorPath;
		}else{
			return $errorPath;
		}
	}

	/**
	 * @param string $instance Instance concernée
	 * @return string
	 */
	public function getModulesToLoad(string $instance):string{
		if(!isset($this->_instancesConfs[$instance]))
			throw new \InvalidArgumentException("Unknown instance $instance");
		$path = null;

		try{
			$path = $this->_instancesConfs[$instance]->find("modules_to_load_path");
		}catch(\Exception $e){
			$path = "{ROOT}/site/config/load/rts.php";
		}

		$modelsToLoad = new PHPString($path);
		if(!$modelsToLoad->startBy("/")){
			return $this->resolvePath($modelsToLoad,false);
		}else{
			return $modelsToLoad;
		}
	}

	/**
	 * @return string[] Noms des instances à créer
	 */
	public function getInstances():array{
		return array_keys($this->_instancesConfs);
	}
}