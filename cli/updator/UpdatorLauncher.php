#!/usr/bin/php -q
<?php


use wfw\cli\updator\conf\UpdatorConf;
use wfw\cli\updator\Updator;
use wfw\engine\lib\cli\argv\ArgvOpt;
use wfw\engine\lib\cli\argv\ArgvOptMap;
use wfw\engine\lib\cli\argv\ArgvParser;
use wfw\engine\lib\cli\argv\ArgvReader;

require_once dirname(__FILE__,2)."/init.environment.php";

$argvReader = new ArgvReader(new ArgvParser(new ArgvOptMap([
	new ArgvOpt('-check',"Obtient la liste des mises à jour à installer",
		0,null,true),
	new ArgvOpt('-download',"Télécharge les mises à jour disponibles",
		0,null,true),
	new ArgvOpt('-install',"Installe les mises à jour contenue dans ./downloads ou dans -path",
		null,null,true),
	new ArgvOpt('-update',"Télécharge et installe les mises à jours disponibles",
		0,null,true),
	new ArgvOpt('--debug',"Affiche plus de détails sur les erreurs",
		0,null,true)
])),$argv);

try{
	throw new Exception("For now this functionnality is deprecated and should not be used anymore !");
	$confs = new UpdatorConf(
		dirname(__DIR__,2)."/engine/config/conf.json",
		dirname(__DIR__,2)."/site/config/conf.json"
	);
	$updator = new Updator($confs);
	$check = false; $download = false; $install = false;
	if($argvReader->exists('-update') || count($argv) === 1){
		$check = true; $download = true; $install = true;
	}else{
		$check = $argvReader->exists('-check');
		$download = $argvReader->exists('-download');
		$install = $argvReader->exists('-install');
	}
	if($check){
		$availables = $updator->check();
		if(count($availables) > 0){
			fwrite(STDOUT,"\e[33mSome updates have to be installed :\e[0m\n");
			foreach($availables as $update){
				fwrite(STDOUT,"$update\n");
			}
		}else{
			fwrite(STDOUT,"\e[32mNo update available.\e[0m\n");
		}
	}
	if($download){
		$updator->download();
		fwrite(STDOUT,"\e[32mUpdates downloaded, ready to install.\e[0m\n");
	}
	if($install){
		$updator->install();
		fwrite(STDOUT,"\e[32mUpdated !\e[0m\n");
	}
}catch(\InvalidArgumentException $e){
	fwrite(STDOUT,"\e[33mWFW_updator WRONG_USAGE\e[0m : {$e->getMessage()}".PHP_EOL);
	exit(1);
}catch(\Exception $e){
	if($argvReader->exists('--debug'))
		fwrite(STDOUT,"\e[31mWFW_updator ERROR\e[0m : ".PHP_EOL."$e".PHP_EOL);
	else
		fwrite(
			STDOUT,
			"\e[31mWFW_updator ERROR\e[0m (try --debug for more) : {$e->getMessage()}".PHP_EOL
		);
	exit(2);
}
exit(0);