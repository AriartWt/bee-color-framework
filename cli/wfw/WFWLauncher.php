#!/usr/bin/php -q
<?php

use wfw\engine\lib\cli\argv\ArgvOpt;
use wfw\engine\lib\cli\argv\ArgvOptMap;
use wfw\engine\lib\cli\argv\ArgvParser;
use wfw\engine\lib\cli\argv\ArgvReader;

require_once(dirname(__DIR__)."/init.environment.php");

$argvReader = new ArgvReader(new ArgvParser(new ArgvOptMap([
	new ArgvOpt(
		'backup',
		"Gestion de backups : backup -help pour plus d'informations.",
		null, null, true
	),
	new ArgvOpt(
		'package',
		"Gestion de packages : package -help pour plus d'informations.",
		null, null, true
	),
	/*new ArgvOpt(
		'update',
		"Gestion de mises à jour : updator -help pour plus d'informations.",
		null,null,true
	),*/
	new ArgvOpt(
		'service',
		"Gestion des services wfw : service -help pour plus d'informations.",
		null,null,true
	),
	new ArgvOpt(
		'test',
		"Lancement de tests wfw : test -help pour plus d'informations.",
		null,null,true
	)
])),$argv);

/**
 * @param string $cmd Commande à executer
 * @throws Exception
 */
function wfwexec(string $cmd):void{
	$res = null;
	system($cmd,$res);
	if($res !== 0){
		if($res === 1)
			throw new InvalidArgumentException(
				"Try [command] -help for command usage or --help for command list"
			);
		throw new Exception(
			"Error trying to exec '$cmd' code $res. "
			."Try [command] -help for command usage or --help for command list."
		);
	}
}

try{
	if(count($argv) === 1)
		throw new InvalidArgumentException("At least one parameter expected ! --help for more !");
	$path = null;
	switch($argv[1]){
		case 'backup' :
			$path = dirname(__DIR__).'/backup/BackupLauncher.php';
			break;
		/*case 'update' :
			$path = CLI.'/updator/UpdatorLauncher.php';
			break;*/
		case 'package' :
			$path = dirname(__DIR__).'/installer/PackageLauncher.php';
			break;
		case 'service' :
			$path = dirname(__DIR__,2).'/daemons/sctl/SCTLClientLauncher.php';
			break;
		case 'test' :
			$path = dirname(__DIR__).'/tester/testsLauncher.php';
			break;
		default :
			throw new InvalidArgumentException("Unknown command $argv[1]. --help to display the command list.");
	}
	foreach(array_slice($argv,2) as $c){
		if($c === '-help') $c = "-$c";
		$path.=" \"$c\" ";
	}

	wfwexec("$path 2>&1");
}catch(\InvalidArgumentException $e){
	fwrite(STDOUT,"\e[33mWFW WRONG_USAGE\e[0m : {$e->getMessage()}".PHP_EOL);
	exit(1);
}catch(\Exception $e){
	fwrite(
		STDOUT,
		"\e[31mWFW ERROR\e[0m : {$e->getMessage()}".PHP_EOL
	);
	exit(2);
}
exit(0);