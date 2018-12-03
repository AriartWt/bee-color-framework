#!/usr/bin/php -q
<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/08/18
 * Time: 12:56
 */
require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."init.environment.php";

use wfw\daemons\multiProcWorker\socket\protocol\DefaultProtocol;
use wfw\daemons\rts\server\conf\RTSPoolConfs;
use wfw\daemons\rts\server\environment\RTSEnvironment;
use wfw\daemons\rts\server\RTS;
use wfw\daemons\rts\server\RTSPool;
use wfw\engine\lib\cli\argv\ArgvOpt;
use wfw\engine\lib\cli\argv\ArgvOptMap;
use wfw\engine\lib\cli\argv\ArgvParser;
use wfw\engine\lib\cli\argv\ArgvReader;
use wfw\engine\lib\cli\signalHandler\PCNTLSignalsHelper;

$argvReader = new ArgvReader(new ArgvParser(new ArgvOptMap([
   new ArgvOpt('-pid','Affiche le pid',0,null,true),
   new ArgvOpt('--debug','Affiche le détail des erreurs',0,null,true)
])),$argv);

try{
	if($argvReader->exists('-pid'))
		fwrite(STDOUT,getmypid().PHP_EOL);

	//On récupère les configurations.
	$confs = new RTSPoolConfs(
		ENGINE.DS."config".DS."conf.json",
		SITE.DS."config".DS."conf.json"
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
			$server = new RTS(
				$confs->getSocketPath($name),
				$confs->getPort($name),
				new DefaultProtocol(),
				new RTSEnvironment(
					$servWorkingDir,
					$confs->getUsers($name),
					$confs->getGroups($name),
					$confs->getAdmins($name),
					$confs->getSessionTtl($name)
				),
				$confs->getRequestTtl($name),
				$confs->getMaxWSockets($name),
				$confs->getMaxWorkers($name),
				$confs->getAllowedWSocketOverflow($name),
				$confs->haveToSendErrorToClient($name),
				$confs->getErrorLogsPath($name)
			);

			$pcntlHelper = new PCNTLSignalsHelper(true);
			$pcntlHelper->handleAll([
				PCNTLSignalsHelper::SIGINT,
				PCNTLSignalsHelper::SIGHUP,
				PCNTLSignalsHelper::SIGTERM,
				PCNTLSignalsHelper::SIGUSR1,
				PCNTLSignalsHelper::SIGUSR2,
				PCNTLSignalsHelper::SIGALRM
			],function($signo)use($server){
				$server->shutdown("PCNTL signal $signo recieved. Server shutdown gracefully.");
			});

			$server->start();
			//If something goes wrong, break the loop for not spawning some out of controls army
			//of machiavellian childs
			break;
		}else if($pid < 0 ){
			throw new Exception("Unable to fork");
		}
		else $pids[]=$pid;
	}
	if(count($pids) > 0 || count($confs->getInstances()) === 0){
		$poolServer = new RTSPool(
			$confs->getSocketPath(),
			$confs->getWorkingDir(),
			new DefaultProtocol(),
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
			$confs->getErrorLogsPath()
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
	fwrite(STDOUT,"\e[33mWFW_rts WRONG_USAGE\e[0m : {$e->getMessage()}".PHP_EOL);
	error_log(
		"\e[33mWFW_rts WRONG_USAGE\e[0m : {$e->getMessage()}".PHP_EOL,
		3,
		$confs->getErrorLogsPath()
	);
	exit(1);
}catch(\Exception $e){
	if($argvReader->exists('--debug')){
		fwrite(STDOUT,"\e[31mWFW_rts ERROR\e[0m : ".PHP_EOL."$e".PHP_EOL);
		error_log(
			"\e[31mWFW_rts ERROR\e[0m : ".PHP_EOL."$e".PHP_EOL,
			3,
			$confs->getErrorLogsPath()
		);
	}
	else{
		fwrite(
			STDOUT,
			"\e[31mWFW_rts ERROR\e[0m (try --debug for more) : {$e->getMessage()}".PHP_EOL
		);
		error_log(
			"\e[31mWFW_rts ERROR\e[0m (try --debug for more) : {$e->getMessage()}".PHP_EOL,
			3,
			$confs->getErrorLogsPath()
		);
	}
	exit(2);
}catch(\Error $e){
	if($argvReader->exists('--debug')){
		fwrite(STDOUT,"\e[31mWFW_rts FATAL_ERROR\e[0m : ".PHP_EOL."$e".PHP_EOL);
		error_log(
			"\e[31mWFW_rts FATAL_ERROR\e[0m : ".PHP_EOL."$e".PHP_EOL,
			3,
			$confs->getErrorLogsPath()
		);
	}
	else{
		fwrite(
			STDOUT,
			"\e[31mWFW_rts FATAL_ERROR\e[0m (try --debug for more) : {$e->getMessage()}".PHP_EOL
		);
		error_log(
			"\e[31mWFW_rts FATAL_ERROR\e[0m (try --debug for more) : {$e->getMessage()}".PHP_EOL,
			3,
			$confs->getErrorLogsPath()
		);
	}
	exit(3);
}