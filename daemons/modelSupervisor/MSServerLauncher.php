#!/usr/bin/php -q
<?php

require_once dirname(__FILE__,2)."/init.environment.php";

use wfw\Autoloader;
use wfw\daemons\modelSupervisor\server\conf\MSServerPoolConfs;
use wfw\daemons\modelSupervisor\server\environment\MSServerEnvironment;
use wfw\daemons\modelSupervisor\server\errors\ExternalShutdown;
use wfw\daemons\modelSupervisor\server\MSServer;
use wfw\daemons\modelSupervisor\server\MSServerPool;
use wfw\daemons\modelSupervisor\server\requestHandler\MSServerRequestHandlerManager;
use wfw\daemons\modelSupervisor\socket\protocol\MSServerSocketProtocol;

use wfw\engine\core\conf\WFW;
use wfw\engine\core\data\DBAccess\NOSQLDB\kvs\KVSAccess;
use wfw\engine\core\data\model\loaders\KVStoreBasedModelLoader;
use wfw\engine\lib\cli\argv\ArgvOpt;
use wfw\engine\lib\cli\argv\ArgvOptMap;
use wfw\engine\lib\cli\argv\ArgvParser;
use wfw\engine\lib\cli\argv\ArgvReader;
use wfw\engine\lib\cli\signalHandler\PCNTLSignalsHelper;
use wfw\engine\lib\data\string\compressor\GZCompressor;
use wfw\engine\lib\data\string\serializer\LightSerializer;
use wfw\engine\lib\data\string\serializer\PHPSerializer;
use wfw\engine\lib\logger\ILogger;

$argvReader = new ArgvReader(new ArgvParser(new ArgvOptMap([
	new ArgvOpt('-pid','Affiche le pid',0,null,true),
	new ArgvOpt('--debug','Affiche le détail des erreurs',0,null,true)
])),$argv);

$confs = null;

try{
	if($argvReader->exists('-pid')) fwrite(STDOUT,getmypid().PHP_EOL);

	//On récupère les configurations.
	$confs = new MSServerPoolConfs(
		dirname(__DIR__,2)."/engine/config/conf.json",
		dirname(__DIR__,2)."/site/config/conf.json"
	);

	$pids = [];
	$restarts = [];
	$lastMailSent = 0;
	//if can't fork : return null
	//if fork parent : true
	//if fork child : false
	$startInstance = function(string $name, ?string $oldPID=null) use (&$pids,$confs,&$restarts,&$lastMailSent) : ?bool{
		if($confs->enabled($name)){
			$servWorkingDir = $confs->getWorkingDir($name);
			$pid = pcntl_fork();
			if($pid === 0 ){
				cli_set_process_title("WFW MSServer $name instance");
				//clean previous servers before restart.
				if(!is_dir($servWorkingDir))
					mkdir($servWorkingDir,0700,true);

				$out=[];
				exec("find $servWorkingDir -name *.pid",$out);
				foreach ($out as $pidf){
					posix_kill((int)file_get_contents($pidf),PCNTLSignalsHelper::SIGALRM);
				}

				if(!is_null($pPath = $confs->getProjectPath($name)))
					(new Autoloader([],$pPath))->register(false,true);

				WFW::collectModules();

				if($oldPID){
					sleep(10);//ugly but let the time for childs to die;...
					if(!isset($restarts[$name])) $restarts[$name]=[];
					$li = count($restarts[$name])-1;
					if($li>=0) $last = $restarts[$name][$li];
					else $last = null;
					$restarts[$name][]= $new = microtime(true);
					$mail = $confs->getAdminMailAddr($name);
					//limit sending mail once every 30min, avoid spamming in case of fail chain,
					//execpt for the 3 first attempts.
					if((($last && $new - $lastMailSent > 1800) || count($restarts[$name]) < 4) && $mail){
						$file = $confs->getLogPath($name,$confs->isCopyLogModeEnabled()?"err":"debug");
						$lastMailSent=microtime(true);
						exec("tail -n150 $file | mail -s \"[MSSERVER][ERROR] $name instance have been restarted "
						     .count($restarts[$name])." times from now.\" $mail"
						);
						$confs->getLogger()->log(
							"[MSServerPool] [AliveChecker] Error notification mail sent to $mail.",
							ILogger::LOG
						);
					}
				}

				$server = new MSServer(
					$confs->getSocketPath($name),
					new MSServerSocketProtocol(),
					new MSServerEnvironment(
						$servWorkingDir,
						require $confs->getInitializersPath($name),
						new KVStoreBasedModelLoader(
							new KVSAccess(
								$confs->getKVSAddr(),
								$confs->getKVSLogin($name),
								$confs->getKVSPassword($name),
								$confs->getKVSContainer($name),
								$confs->getKVSDefaultStorage($name) ?? null
							),
							WFW::models()
						),
						$confs->getUsers($name),
						$confs->getGroups($name),
						$confs->getAdmins($name),
						$confs->getComponents($name),
						$confs->getLogger($name),
						$confs->getSessionTtl($name)
					),
					new MSServerRequestHandlerManager(),
					$confs->getLogger($name),
					new LightSerializer(
						new GZCompressor(),
						new PHPSerializer()
					),
					$confs->getRequestTtl($name),
					$confs->haveToSendErrorToClient($name),
					$confs->haveToShutdownOnError($name)
				);

				$pcntlHelper = new PCNTLSignalsHelper(true);
				$pcntlHelper->handleAll([
						PCNTLSignalsHelper::SIGINT,
						PCNTLSignalsHelper::SIGHUP,
						PCNTLSignalsHelper::SIGTERM,
						PCNTLSignalsHelper::SIGUSR1,
						PCNTLSignalsHelper::SIGUSR2,
						PCNTLSignalsHelper::SIGALRM //socket_accept workaround
					],function($signo)use($server){
					$server->shutdown(
						new ExternalShutdown("PCNTL signal $signo recieved. Server shutdown gracefully.")
					);
				});

				$server->start();
				return false;
			}else if($pid < 0 ){
				$confs->getLogger()->log(
					"[MSServerPool] Unable to fork to create instance '$name', maybe"
					." insufficient ressources or max process limit reached.",
					ILogger::ERR
				);
				return null;
			}else{
				if(!is_null($oldPID) && isset($pids[$oldPID])) unset($pids[$oldPID]);
				$pids[$pid]=["name" => $name, "working_dir" => $servWorkingDir ];
				return true;
			}
		}else return null;
	};

	foreach($confs->getInstances() as $name){
		$res = $startInstance($name);
		if(!is_null($res) && !$res) break;
	}

	if(count($pids) > 0 || count($confs->getInstances()) === 0){
		cli_set_process_title("WFW MSServerPool");
		$poolServer = new MSServerPool(
			$confs->getSocketPath(),
			$confs->getWorkingDir(),
			new MSServerSocketProtocol(),
			$pids,
			array_combine(
				$confs->getInstances(),
				array_map(
					function($name) use($confs){ return $confs->getSocketPath($name);},
					$confs->getInstances()
				)
			),
			$confs->getLogger(),
			$startInstance
		);

		$pcntlHelper = new PCNTLSignalsHelper(true);
		$pcntlHelper->handleAll([
				PCNTLSignalsHelper::SIGINT,
				PCNTLSignalsHelper::SIGHUP,
				PCNTLSignalsHelper::SIGTERM,
				PCNTLSignalsHelper::SIGUSR1,
				PCNTLSignalsHelper::SIGUSR2,
				PCNTLSignalsHelper::SIGALRM
			],function() use ($poolServer){
				$poolServer->shutdown();
		});

		$poolServer->start();
	}
}catch(\InvalidArgumentException $e){
	fwrite(STDOUT,"\e[33mWFW_msserver WRONG_USAGE\e[0m : {$e->getMessage()}".PHP_EOL);
	$confs->getLogger()->log(
		"\e[33mWFW_msserver WRONG_USAGE\e[0m : {$e->getMessage()}",
		ILogger::WARN
	);
	exit(1);
}catch(\Exception $e){
	if($argvReader->exists('--debug'))fwrite(
		STDOUT,"\e[31mWFW_msserver ERROR\e[0m : ".PHP_EOL."$e".PHP_EOL
	);
	else fwrite(
		STDOUT,
		"\e[31mWFW_msserver ERROR\e[0m (try --debug for more) : {$e->getMessage()}".PHP_EOL
	);
	$confs->getLogger()->log(
		"\e[31mWFW_msserver ERROR\e[0m : $e",
		ILogger::ERR
	);
	exit(2);
}catch(\Error $e){
	if($argvReader->exists('--debug')) fwrite(
		STDOUT,
		"\e[31mWFW_msserver FATAL_ERROR\e[0m (try --debug for more) : $e".PHP_EOL
	);
	else fwrite(
		STDOUT,
		"\e[31mWFW_msserver FATAL_ERROR\e[0m (try --debug for more) : {$e->getMessage()}".PHP_EOL
	);
	$confs->getLogger()->log(
		"\e[31mWFW_msserver FATAL_ERROR\e[0m : $e",
		ILogger::ERR
	);
	exit(3);
}