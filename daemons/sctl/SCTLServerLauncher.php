#!/usr/bin/php -q
<?php
use wfw\daemons\multiProcWorker\socket\protocol\DefaultProtocol;
use wfw\daemons\sctl\conf\SCTLConf;
use wfw\daemons\sctl\SCTLServer;
use wfw\engine\lib\cli\argv\ArgvOpt;
use wfw\engine\lib\cli\argv\ArgvOptMap;
use wfw\engine\lib\cli\argv\ArgvParser;
use wfw\engine\lib\cli\argv\ArgvReader;
use wfw\engine\lib\cli\signalHandler\PCNTLSignalsHelper;

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."init.environment.php";

$argvReader = new ArgvReader(new ArgvParser(new ArgvOptMap([
	new ArgvOpt(
		'-dir',
		"Repertoire de travail recevant auth.pwd, sctl.pid, sem_file.semaphore",
		1,
		function($arg){ return is_dir($arg);},
		true,
		"[$0] is not a valid directory !"),
	new ArgvOpt(
		'-user',
		"Utilisateur propriétaire du fichier auth.pwd (defaut : www-data)",
		1,
		null,
		true),
	new ArgvOpt('-daemons',"Liste de daemons à gérer",null,null,true),
	new ArgvOpt('-pid', "Affiche le pid",0,null,true),
	new ArgvOpt('--debug',"Affiche plus d'informations sur les erreurs",0,null,true)
])),$argv);

try{
	if($argvReader->exists("-pid"))
		echo getmypid().PHP_EOL;

	$sctlServer = new SCTLServer(
		new SCTLConf(
			ENGINE."/config/conf.json",
			SITE."/config/conf.json",
			DAEMONS,
			$argvReader->exists('-user') ? $argvReader->get('-user')[0] : null,
			$argvReader->exists('-path') ? $argvReader->get('-path')[0] : null,
			...($argvReader->exists('-daemons') ? $argvReader->get('-daemons') : [])
		),
		new DefaultProtocol()
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
	],function($signo) use ($sctlServer){
		$sctlServer->shutdown($signo);
	});

	$sctlServer->start();
}catch(\InvalidArgumentException $e){
	fwrite(STDOUT,"\e[33mWFW_sctl WRONG_USAGE\e[0m : {$e->getMessage()}".PHP_EOL);
	exit(1);
}catch(\Exception $e){
	if($argvReader->exists('--debug'))
		fwrite(STDOUT,"\e[31mWFW_sctl ERROR\e[0m : ".PHP_EOL."$e".PHP_EOL);
	else
		fwrite(
			STDOUT,
			"\e[31mWFW_sctl ERROR\e[0m (try --debug for more) : {$e->getMessage()}".PHP_EOL
		);
	exit(2);
}