#!/usr/bin/php -q
<?php


use wfw\cli\tester\launchers\ITestsLauncher;
use wfw\engine\core\conf\FileBasedConf;
use wfw\engine\core\conf\io\adapters\JSONConfIOAdapter;
use wfw\engine\lib\cli\argv\ArgvOpt;
use wfw\engine\lib\cli\argv\ArgvOptMap;
use wfw\engine\lib\cli\argv\ArgvParser;
use wfw\engine\lib\cli\argv\ArgvReader;

require_once dirname(dirname(__FILE__))."/init.environment.php";

$argvReader = new ArgvReader(new ArgvParser(new ArgvOptMap([
	new ArgvOpt("-conf","Chemin(s) d'accès au(x) fichier(s) de configurations (par défaut, le fichier cli/tester/config/conf.json est utilisé)",
		null,null,true),
	new ArgvOpt("-mode","Mode : [t|s]. t : tests (mode tests, execute les tests contenus dans le(s) dossier(s) spécifié(s)) s : sequences (mode séquences, execute les séquences de tests). (Défaut : s)",1,function($m){
		return $m === "s" || $m ==="t";
	},true,"Unknown mode [$0]. See --help to display the available modes."),
	new ArgvOpt("-list","Liste des tests à effectuer. Si mode 's' : "
		."launcher:* (tous les tests pour le launcher) launcher:unit/* (tous les tests de type unit)"
		." launcher:unit/engine.* (tous les test de type unit dont le nom commence par 'engine.'), "
		."si mode 't' : launcher:\"dir,file,...\"",
		null,null,true),
	new ArgvOpt('-mute','Désactive les messages de sortie du tester (conserve seulement les sorties produites par les tests)',
		0,null,true)
])),$argv);

try{
	/** @var FileBasedConf|null $conf */
	$conf = null;
	$confIOAdpater = new JSONConfIOAdapter();
	if($argvReader->exists("-conf")){
		foreach($argvReader->get("-conf") as $path){
			$c = new FileBasedConf($path,$confIOAdpater);
			if(is_null($conf)) $conf = $c;
			else $conf->merge($c);
		}
	}else $conf = new FileBasedConf(CLI."/tester/config/conf.json",$confIOAdpater);

	$mode = $argvReader->exists("-mode") ? $argvReader->get("-mode")[0] : "s";
	$list = $argvReader->exists("-list") ? $argvReader->get("-list") : null;
	$launchers = [];

	if($list === null){
		if($mode === "s"){
			$list = [];
			$sequences = $conf->getArray("sequences");
			foreach($sequences as $l=>$s){ $list[]="$l:*";}
		} else $list = ["PHPUnit:\"".ROOT."/tests\""];
	}
	foreach($list as $l){
		$tmp = explode(":",$l);
		if(!isset($launchers[$tmp[0]])) $launchers[array_shift($tmp)]=[implode(':',$tmp)];
		else $launchers[array_shift($tmp)][] = implode(':',$tmp);
	}

	$method = "launch".($mode === "s" ? "Sequences" : "Tests");
	foreach($launchers as $launcher=>$args){
		$class = $conf->getString("launchers/$launcher");
		if(is_null($class)) throw new InvalidArgumentException(
			"No launcher defined as $launcher, please check your conf files !"
		);
		if(!is_a($class, ITestsLauncher::class,true))
			throw new InvalidArgumentException("$class doesn't implements ". ITestsLauncher::class);
		(new $class($conf,$argvReader->exists('-mute')))->$method(...$args);
	}

}catch(\InvalidArgumentException $e){
	fwrite(STDOUT,"\e[33mWFW_tester WRONG_USAGE\e[0m : {$e->getMessage()}".PHP_EOL);
	exit(1);
}catch(\Exception $e){
	fwrite(
		STDOUT,
		"\e[31mWFW_tester ERROR\e[0m : {$e}".PHP_EOL
	);
	exit(2);
}
exit(0);