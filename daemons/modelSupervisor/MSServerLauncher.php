#!/usr/bin/php -q
<?php

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."init.environment.php";

use wfw\daemons\modelSupervisor\server\conf\MSServerPoolConfs;
use wfw\daemons\modelSupervisor\server\environment\MSServerEnvironment;
use wfw\daemons\modelSupervisor\server\errors\ExternalShutdown;
use wfw\daemons\modelSupervisor\server\MSServer;
use wfw\daemons\modelSupervisor\server\MSServerPool;
use wfw\daemons\modelSupervisor\server\requestHandler\MSServerRequestHandlerManager;
use wfw\daemons\modelSupervisor\socket\protocol\MSServerSocketProtocol;

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
		ENGINE.DS."config".DS."conf.json",
		SITE.DS."config".DS."conf.json"
	);

	$pids = [];
	//if can't fork : return null
	//if fork parent : true
	//if fork child : false
	$startInstance = function(string $name, ?string $oldPID=null) use (&$pids,$confs) : ?bool{
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

			if($oldPID) sleep(10); //ugly but let the time for childs to die;...

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
						require $confs->getModelsToLoadPath($name)
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
				"[MSServerPool] Unable to fork to create instance '$name', maybe insufficient ressources or max process limit reached.",
				ILogger::ERR
			);
			return null;
		}else{
			if(!is_null($oldPID) && isset($pids[$oldPID])) unset($pids[$oldPID]);
			$pids[$pid]=["name" => $name, "working_dir" => $servWorkingDir ];
			return true;
		}
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
	if($argvReader->exists('--debug')){
		fwrite(STDOUT,"\e[31mWFW_msserver ERROR\e[0m : ".PHP_EOL."$e".PHP_EOL);
		$confs->getLogger()->log(
			"\e[31mWFW_msserver ERROR\e[0m : ".PHP_EOL."$e",
			ILogger::ERR
		);
	}
	else{
		fwrite(
			STDOUT,
			"\e[31mWFW_msserver ERROR\e[0m (try --debug for more) : {$e->getMessage()}".PHP_EOL
		);
		$confs->getLogger()->log("\e[31mWFW_msserver ERROR\e[0m (try --debug for more) : {$e->getMessage()}", ILogger::ERR);
	}
	exit(2);
}catch(\Error $e){
	if($argvReader->exists('--debug')){
		$confs->getLogger()->log(
			"\e[31mWFW_msserver FATAL_ERROR\e[0m : ".PHP_EOL."$e",
			ILogger::ERR
		);
	}
	else{
		fwrite(
			STDOUT,
			"\e[31mWFW_msserver FATAL_ERROR\e[0m (try --debug for more) : {$e->getMessage()}".PHP_EOL
		);
		$confs->getLogger()->log(
			"\e[31mWFW_msserver FATAL_ERROR\e[0m (try --debug for more) : {$e->getMessage()}",
			ILogger::ERR
		);
	}
	exit(3);
}