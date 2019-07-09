<?php

namespace wfw\daemons\rts\server\conf;

use stdClass;
use wfw\engine\core\conf\FileBasedConf;
use wfw\engine\core\conf\io\adapters\JSONConfIOAdapter;
use wfw\engine\lib\logger\DefaultLogFormater;
use wfw\engine\lib\logger\FileLogger;
use wfw\engine\lib\logger\ILogger;
use wfw\engine\lib\PHP\objects\StdClassOperator;
use wfw\engine\lib\PHP\types\PHPString;

/**
 * Class RTSPoolConfs
 *
 * @package wfw\daemons\rts\server\conf
 */
final class RTSPoolConfs {
	private const WORKING_DIR = "working_dir";
	private const SOCKET_PATH = "socket_path";
	private const REQUEST_TTL = "request_ttl";
	private const LOGS = "logs/files";

	/** @var FileBasedConf $_conf */
	private $_conf;
	/**  @var string $_basePath */
	private $_basePath;
	/** @var ILogger $_logger */
	private $_logger;
	/** @var ILogger[] $_instanceLoggers */
	private $_instanceLoggers = [];
	/** @var StdClassOperator[] $_instancesConfs */
	private $_instancesConfs = [];

	/**
	 * KVSConfs constructor.
	 *
	 * @param string      $engineConfs Données de configuration générales
	 * @param null|string $siteConfs   (optionnel) données de configuration du site
	 * @param string      $basePath    (optionnel defaut : DAEMONS) chemin absolu permettant la
	 *                                 résolution du chemin relatif des fichiers.
	 * @param bool        $noLogger
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
		$this->_basePath = $basePath;
		$confIO = new JSONConfIOAdapter();
		//On récupère les configurations générales
		$conf = new FileBasedConf($engineConfs,$confIO);
		if(file_exists($siteConfs)){
			$conf->merge(new FileBasedConf($siteConfs,$confIO));
		}

		//On cherche le chemin des configurations du daemon.
		$confPath = $conf->getString("server/daemons/rts");
		if(is_null($confPath)){
			throw new \InvalidArgumentException("Config key server/daemons/rts not found");
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
			foreach(['err','log','warn','debug'] as $l){
				if(!is_dir($logdir = dirname($this->getLogPath(null,$l))))
					mkdir($logdir,0700,true);
			}
			$this->_logger = (new FileLogger(new DefaultLogFormater(),...[
				$this->getLogPath(null,"log"),
				$this->getLogPath(null,"err"),
				$this->getLogPath(null,"warn"),
				$this->getLogPath(null,"debug")
			]))->autoConfFileByLevel(
				FileLogger::ERR | FileLogger::WARN | FileLogger::LOG,
				FileLogger::DEBUG,
				$this->isCopyLogModeEnabled(null)
			)->autoConfByLevel($this->_conf->getInt("logs/level") ?? ILogger::ERR);
		}

		//On détermine les configurations de chaque instance à créer
		$this->_instancesConfs = [];
		$defInstance = $this->_conf->getObject("default_instance");
		foreach($this->_conf->getArray("instances") as $instanceName=>$instanceConf){
			$tmp = new StdClassOperator(new stdClass());
			$tmp->mergeStdClass($defInstance);
			$tmp->mergeStdClass($instanceConf);

			try{
				$path = $tmp->find("project_path");
				if(file_exists("$path/site/config/conf.json")){
					$tmpConf = new FileBasedConf(
						"$path/site/config/conf.json",
						new JSONConfIOAdapter()
					);
					$custom_conf = $tmpConf->getObject("server/daemons/custom_config/rts");
					if(!is_null($custom_conf)) $tmp->mergeStdClass($custom_conf);
				}
			}catch(\Exception $e){
				$this->_logger->log(
					"An error occured while trying to merge project conf for $instanceName : $e",
					ILogger::ERR
				);
			}
			$this->_conf->set("instances/$instanceName",$tmp->getStdClass());
			$this->_instancesConfs[$instanceName] = $tmp;
			if(!$noLogger){
				foreach(['err','log','warn','debug'] as $l){
					if(!is_dir($logdir = dirname($this->getLogPath($instanceName,$l))))
						mkdir($logdir,0700,true);
				}
				$this->_instanceLoggers[$instanceName] = (new FileLogger(new DefaultLogFormater(),...[
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
	 * @param null|string $instance
	 * @param string      $level
	 * @return null|string
	 */
	private function getLogPath(?string $instance, string $level="err"):?string{
		if($instance) $path = $this->_conf->getString("instances/$instance/".self::LOGS."/$level");
		else $path = $this->_conf->getString(self::LOGS."/$level");
		$errorPath = $path ?? "rts".(($instance)?"-$instance":"")."-$level.log";

		if(strpos($errorPath,"/")!==0){
			if($instance)
				$basePath = ($this->_conf->getString("instances/$instance/logs/default_path")
				?? $this->_conf->getString("logs/default_path"))."/instances";
			else $basePath = $this->_conf->getString("logs/default_path");
			if(!$basePath) $basePath = $this->getWorkingDir($instance);
			if(!is_dir($basePath)) mkdir($basePath,0700,true);
			return "$basePath/$errorPath";
		}else{
			return $errorPath;
		}
	}

	/**
	 * @param null|string $container
	 * @return bool|null
	 */
	public function isCopyLogModeEnabled(?string $container=null):?bool{
		$res = $this->_conf->getBoolean(($container ? "instances/$container/" : "") ."logs/copy");
		if(is_null($res)) return true;
		else return $res;
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
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getObject("instances/$instance/users");
	}

	/**
	 * @param string $instance Instance dont on souhaite le nombre maximum de sockets par worker
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function getMaxWSockets(string $instance): int{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getInt("instances/$instance/max_websockets_by_worker") ?? 1;
	}

	/**
	 * @param string $instance Instance dont on souhaite le nombre maximum de worker
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function getMaxWorkers(string $instance): int{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getInt("instances/$instance/max_workers")??0;
	}

	/**
	 * @param string $instance Instance dont on souhaite le nombre maximum de fois que l'on peut
	 *                         dépasser max_wsockets lorsque le nombre max_worker est atteint.
	 *                         -1 : pas de limite, n > 0 : max_wsockets * n+1
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function getAllowedWSocketOverflow(string $instance): int{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getInt("instances/$instance/allowed_wsockets_overflow") ?? -1;
	}

	/**
	 * @param string $instance  Instance dont on souhaite connaitre le port
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function getPort(string $instance): int{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getInt("instances/$instance/port") ?? 8000;
	}

	/**
	 * @param string $instance  Min time in a loop
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function getSleepInterval(string $instance): int{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getInt("instances/$instance/sleep_interval") ?? 100;
	}

	/**
	 * @param string $instance  Instance
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function getMaxWriteBufferSize(string $instance): int{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getInt("instances/$instance/max_write_buffer_size") ?? 49152;
	}

	/**
	 * @param string $instance  Instance
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function getMaxReadBufferSize(string $instance): int{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getInt("instances/$instance/max_read_buffer_size") ?? 49152;
	}

	/**
	 * @param string $instance  Instance
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function getMaxConnectionsByIp(string $instance): int{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getInt("instances/$instance/max_connections_by_ip") ?? 20;
	}

	/**
	 * @param string $instance  Instance
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function getMaxRequestHandshakeSize(string $instance): int{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getInt("instances/$instance/max_request_handshake_size") ?? 1024;
	}

	/**
	 * @param string $instance  Instance
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public function getAllowedOrigins(string $instance): ?array{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getArray("instances/$instance/allowed_origins") ?? null;
	}

	/**
	 * @param string $instance  Instance
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function getMaxRequestsByMinuteByClient(string $instance): int{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getInt("instances/$instance/max_requests_by_minute_by_client") ?? 20;
	}

	/**
	 * @param string $instance  Instance
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	public function getMaxSocketSelect(string $instance): int{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getInt("instances/$instance/max_socket_select") ?? 20;
	}

	/**
	 * @param string $instance  Instance dont on souhaite connaitre le port
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getHost(string $instance): string{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		$res = $this->_conf->getString("instances/$instance/host");
		if(is_null($res)) throw new \InvalidArgumentException("A host must be defined !");
		return $res;
	}

	/**
	 * @param string $instance Instance dont on souhaite connaitre les groupes
	 * @return stdClass
	 * @throws \InvalidArgumentException
	 */
	public function getGroups(string $instance): stdClass{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getObject("instances/$instance/groups") ?? new stdClass();
	}

	/**
	 * @param string $instance Instance dont ou souhaite connaitre les admins
	 * @return stdClass
	 * @throws \InvalidArgumentException
	 */
	public function getAdmins(string $instance):stdClass{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getObject("instances/$instance/admins") ?? new stdClass();
	}

	/**
	 * @param null|string $instance Instance dont on souhaite connaitre le temps de vie des requetes
	 * @return int
	 */
	public function getRequestTtl(?string $instance = null):int{
		if($instance){
			if(!$this->_conf->existsKey("instances/$instance"))
				throw new \InvalidArgumentException("Unknown instance $instance");
			return $this->_conf->getInt("instances/$instance/request_ttl") ?? 900;
		}else return $this->_conf->getInt(self::REQUEST_TTL)??900;
	}

	/**
	 * @param string $instance Instance dont ou souhaite connaître le temps de vie des sessions
	 * @return int
	 */
	public function getSessionTtl(string $instance):int{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getInt("instances/$instance/session_ttl") ?? 60;
	}

	/**
	 * @param string $instance Instance concernée
	 * @return bool
	 */
	public function haveToSendErrorToClient(string $instance):bool{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getBoolean("instances/$instance/send_error_to_client") ?? true;
	}

	/**
	 * @param string $instance Instance concernée
	 * @return bool
	 */
	public function haveToShutdownOnError(string $instance):bool {
		if (!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getBoolean("instances/$instance/shutdown_on_error") ?? false;
	}


	/**
	 * @param string $instance Instance concernée
	 * @return array
	 */
	public function getModulesToLoad(string $instance):array{
		if(!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");

		$modulesToLoad = new PHPString(
			$this->_conf->getString("instances/$instance/modules_to_load_path")
				?? "{ROOT}/site/config/load/rts.php"
		);
		if(!$modulesToLoad->startBy("/")){
			$modulesToLoad = $this->resolvePath($modulesToLoad,false);
		}
		return require($modulesToLoad) ?? [];
	}

	/**
	 * @param null|string $instance
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function enabled(?string $instance=null):bool{
		if (!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getBoolean("instances/$instance/enabled") ?? false;
	}

	/**
	 * @param null|string $instance
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function mustSpawnAllWorkersAtStartup(?string $instance=null):bool{
		if (!$this->_conf->existsKey("instances/$instance"))
			throw new \InvalidArgumentException("Unknown instance $instance");
		return $this->_conf->getBoolean("instances/$instance/spawn_all_workers_at_startup") ?? true;
	}

	/**
	 * @param null|string $instance
	 * @return ILogger
	 */
	public function getLogger(?string $instance=null):ILogger{
		return $this->_instanceLoggers[$instance] ?? $this->_logger;
	}

	/**
	 * @return string[] Noms des instances à créer
	 */
	public function getInstances():array{
		return array_keys($this->_instancesConfs);
	}
}