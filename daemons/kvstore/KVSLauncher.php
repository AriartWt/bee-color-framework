#!/usr/bin/php -q
<?php

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."init.environment.php";

use wfw\daemons\kvstore\server\conf\KVSConfs;
use wfw\daemons\kvstore\server\environment\KVSServerEnvironment;
use wfw\daemons\kvstore\server\errors\ExternalShutdown;
use wfw\daemons\kvstore\server\KVSServer;
use wfw\daemons\kvstore\socket\protocol\KVSSocketProtocol;

use wfw\engine\lib\cli\argv\ArgvOpt;
use wfw\engine\lib\cli\argv\ArgvOptMap;
use wfw\engine\lib\cli\argv\ArgvParser;
use wfw\engine\lib\cli\argv\ArgvReader;
use wfw\engine\lib\cli\signalHandler\PCNTLSignalsHelper;
use wfw\engine\lib\data\string\compressor\GZCompressor;
use wfw\engine\lib\data\string\serializer\LightSerializer;
use wfw\engine\lib\data\string\serializer\PHPSerializer;

$argvReader = new ArgvReader(new ArgvParser(new ArgvOptMap([
	new ArgvOpt('-pid','Affiche le pid',0,null,true),
	new ArgvOpt('--debug','Affiche le détail des erreurs',0,null,true)
])),$argv);

try{
	if($argvReader->exists('-pid'))
		fwrite(STDOUT,getmypid().PHP_EOL);

	//On récupère les configurations du server
	$conf = new KVSConfs(ENGINE.DS."config".DS."conf.json", SITE.DS."config".DS."conf.json");
	cli_set_process_title("WFW KVS server");
	//On prépare le serveur
	$KVSServer = new KVSServer(
		$conf->getSocketPath(),
		$conf->getDbPath(),
		new KVSSocketProtocol(),
		new KVSServerEnvironment(
			$conf->getUsers(),
			$conf->getGroups(),
			$conf->getAdmins(),
			$conf->getContainers(),
			$conf->getDbPath(),
			$conf->getSessionTtl()
		),
		new LightSerializer(
			new GZCompressor(),
			new PHPSerializer()
		),
		$conf->getRequestTtl(),
		$conf->haveToSendErrorToClient(),
		$conf->haveToShutdownOnError(),
		$conf->getErrorLogsPath()
	);

	//On prépare les handlers de signaux
	$helper = new PCNTLSignalsHelper();
	$helper->handleAll([
		PCNTLSignalsHelper::SIGINT,
		PCNTLSignalsHelper::SIGHUP,
		PCNTLSignalsHelper::SIGTERM,
		PCNTLSignalsHelper::SIGUSR1,
		PCNTLSignalsHelper::SIGUSR2,
		PCNTLSignalsHelper::SIGALRM //Permet de receptionner un signal pendant l'attente socket_accept
	],function($signo) use ($KVSServer){
		$KVSServer->shutdown(
			new ExternalShutdown("PCNTL signal $signo recieved. Server will shutdown gracefully.")
		);
	});

	//On démarre le serveur
	$KVSServer->start();
}catch(\InvalidArgumentException $e){
	fwrite(STDOUT,"\e[33mWFW_kvs WRONG_USAGE\e[0m : {$e->getMessage()}".PHP_EOL);
	error_log(
		"\e[33mWFW_kvs WRONG_USAGE\e[0m : {$e->getMessage()}".PHP_EOL,
		3,
		$conf->getErrorLogsPath()
	);
	exit(1);
}catch(\Exception $e){
	if($argvReader->exists('--debug')){
		fwrite(STDOUT,"\e[31mWFW_kvs ERROR\e[0m : ".PHP_EOL."$e".PHP_EOL);
		error_log(
			"\e[31mWFW_kvs ERROR\e[0m : ".PHP_EOL."$e".PHP_EOL,
			3,
			$conf->getErrorLogsPath()
		);
	}
	else{
		fwrite(
			STDOUT,
			"\e[31mWFW_kvs ERROR\e[0m (try --debug for more) : {$e->getMessage()}".PHP_EOL
		);
		error_log(
			"\e[31mWFW_kvs ERROR\e[0m (try --debug for more) : {$e->getMessage()}".PHP_EOL,
			3,
			$conf->getErrorLogsPath()
		);
	}
	exit(2);
}