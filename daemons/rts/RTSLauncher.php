#!/usr/bin/php -q
<?php

require_once dirname(__FILE__,2)."/init.environment.php";

use wfw\daemons\rts\server\conf\RTSPoolConfs;
use wfw\daemons\rts\server\environment\RTSEnvironment;
use wfw\daemons\rts\server\RTS;
use wfw\daemons\rts\server\RTSPool;
use wfw\engine\lib\cli\argv\ArgvOpt;
use wfw\engine\lib\cli\argv\ArgvOptMap;
use wfw\engine\lib\cli\argv\ArgvParser;
use wfw\engine\lib\cli\argv\ArgvReader;
use wfw\engine\lib\cli\signalHandler\PCNTLSignalsHelper;
use wfw\engine\lib\data\string\compressor\GZCompressor;
use wfw\engine\lib\data\string\serializer\LightSerializer;
use wfw\engine\lib\data\string\serializer\PHPSerializer;
use wfw\engine\lib\network\socket\protocol\SocketProtocol;
use wfw\engine\lib\network\socket\protocol\StreamProtocol;

$argvReader = new ArgvReader(new ArgvParser(new ArgvOptMap([
   new ArgvOpt('-pid','Affiche le pid',0,null,true)
])),$argv);

try{
	if($argvReader->exists('-pid')) fwrite(STDOUT,getmypid().PHP_EOL);
	cli_set_process_title("WFW RTS server");
	//On récupère les configurations.
	$confs = new RTSPoolConfs(
		dirname(__DIR__,2)."/engine/config/conf.json",
		dirname(__DIR__,2)."/site/config/conf.json"
	);

	$pids = [];
	foreach($confs->getInstances() as $name){
		$pid = pcntl_fork();
		if($pid === 0 ){
			//clean previous servers before restart.
			$servWorkingDir = $confs->getWorkingDir($name);
			$pidFile = $servWorkingDir."/rts.pid";
			if(file_exists($pidFile))
				posix_kill(file_get_contents($pidFile),PCNTLSignalsHelper::SIGALRM);
			if($confs->enabled($name)){
				$server = new RTS(
					$name,
					$confs->getSocketPath($name),
					$confs->getHost($name),
					$confs->getPort($name),
					new StreamProtocol(),
					new RTSEnvironment(
						$servWorkingDir,
						$confs->getUsers($name),
						$confs->getGroups($name),
						$confs->getAdmins($name),
						$confs->getLogger($name),
						$confs->getModulesToLoad($name),
						$confs->getSessionTtl($name),
						$confs->getMaxWriteBufferSize($name),
						$confs->getMaxReadBufferSize($name),
						$confs->getMaxRequestHandshakeSize($name),
						$confs->getAllowedOrigins($name) ?? [$confs->getHost($name)],
						$confs->getMaxConnectionsByIp($name),
						$confs->getMaxRequestsByMinuteByClient($name),
						$confs->getMaxSocketSelect($name)
					),
					new LightSerializer(new GZCompressor(),new PHPSerializer()),
					$confs->getMaxWSockets($name),
					$confs->getMaxWorkers($name),
					$confs->getAllowedWSocketOverflow($name),
					$confs->mustSpawnAllWorkersAtStartup($name),
					$confs->getRequestTtl($name),
					$confs->getSleepInterval($name)
				);

				$pcntlHelper = new PCNTLSignalsHelper(true);
				$pcntlHelper->handleAll([
						PCNTLSignalsHelper::SIGINT,
						PCNTLSignalsHelper::SIGHUP,
						PCNTLSignalsHelper::SIGTERM,
						PCNTLSignalsHelper::SIGUSR1,
						PCNTLSignalsHelper::SIGUSR2,
						PCNTLSignalsHelper::SIGALRM
					],function()use($server){
					$server->shutdown();
				});

				$server->start();
				//If something goes wrong, break the loop for not spawning some out of controls army
				//of machiavellian childs
				exit(0);
			}
			break;
		}else if($pid < 0 ){
			throw new Exception("Unable to fork");
		}
		else $pids[]=$pid;
	}

	cli_set_process_title("WFW RTSPool server");
	$poolServer = new RTSPool(
		$confs->getSocketPath(),
		$confs->getWorkingDir(),
		new SocketProtocol(),
		$pids,
		array_combine(
			$confs->getInstances(),
			array_map(
				function($name) use($confs){
					return [
						"path" => $confs->getSocketPath($name),
						"port" => $confs->getPort($name)
					];
				},
				$confs->getInstances()
			)
		),
		$confs->getLogger()
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
}catch(\InvalidArgumentException $e){
	fwrite(STDOUT,"\e[33mWFW_rts WRONG_USAGE\e[0m : {$e->getMessage()}".PHP_EOL);
	exit(1);
}catch(\Exception $e){
	fwrite(
		STDOUT,
		"\e[31mWFW_rts ERROR\e[0m $e".PHP_EOL
	);
	exit(2);
}catch(\Error $e){
	fwrite(
		STDOUT,
		"\e[31mWFW_rts FATAL_ERROR\e[0m $e".PHP_EOL
	);
	exit(3);
}