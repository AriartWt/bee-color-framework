#!/usr/bin/php -q
<?php
use wfw\daemons\multiProcWorker\socket\protocol\DefaultProtocol;
use wfw\daemons\sctl\conf\SCTLConf;
use wfw\daemons\sctl\SCTLClient;
use wfw\engine\lib\cli\argv\ArgvOpt;
use wfw\engine\lib\cli\argv\ArgvOptMap;
use wfw\engine\lib\cli\argv\ArgvParser;
use wfw\engine\lib\cli\argv\ArgvReader;

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."init.environment.php";

$argvReader = new ArgvReader(new ArgvParser(new ArgvOptMap([
	new ArgvOpt(
		'-cmd',
		"[-cmd] (start|stop|restart|status)[daemon1,daemon2,...]*",
		null,
		null,
		true),
	new ArgvOpt(
		'-path',
		"Chemin vers le dossier contenant auth.pwd",
		1,
		function($arg){ return is_dir($arg);},
		true,
		"[$0] is not a valid file !"),
	new ArgvOpt('-all',"Tous les daemons",null,null,true),
	new ArgvOpt('--debug',"Affiche plus d'informations sur les erreurs",0,null,true)
])),$argv);

try{
	$sctlClient = new SCTLClient(
		new SCTLConf(
			ENGINE."/config/conf.json",
			SITE."/config/conf.json",
			DAEMONS,
			null,
			$argvReader->exists('-path') ? $argvReader->get('-path')[0] : null
		),
		new DefaultProtocol()
	);
	$args = ($argvReader->exists('-cmd'))
		? $argvReader->get('-cmd')
		: array_diff(array_values(array_slice($argv,1)),['-all','--debug']);

	if(is_int(array_search('-path',$args)))
		array_splice($args,array_search('-path',$args),2);
	if(!preg_match("/^(start|stop|restart|status)$/",$args[0]))
		throw new \InvalidArgumentException("First arg of -cmd need to be start|stop|restart");

	$cmd = array_shift($args);
	$res = [];
	if($argvReader->exists('-all')) $res = $sctlClient->{"{$cmd}All"}();
	else $res = $sctlClient->$cmd(...$args);
	if(!empty($res)){
		$print = [];
		foreach($res as $r){
			$print[] = implode("\n",$r);
		}
		echo implode("\n***\n",$print).PHP_EOL;
	}

}catch(\InvalidArgumentException $e){
	fwrite(STDOUT,"\e[33mWFW_sctlClient WRONG_USAGE\e[0m : {$e->getMessage()}".PHP_EOL);
	exit(1);
}catch(\Exception $e){
	if($argvReader->exists('--debug'))
		fwrite(STDOUT,"\e[31mWFW_sctlClient ERROR\e[0m : ".PHP_EOL."$e".PHP_EOL);
	else
		fwrite(
			STDOUT,
			"\e[31mWFW_sctlClient ERROR\e[0m (try --debug for more) : {$e->getMessage()}".PHP_EOL
		);
	exit(2);
}
exit(0);