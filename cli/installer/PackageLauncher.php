#!/usr/bin/php -q
<?php

use wfw\engine\lib\cli\argv\ArgvOpt;
use wfw\engine\lib\cli\argv\ArgvOptMap;
use wfw\engine\lib\cli\argv\ArgvParser;
use wfw\engine\lib\cli\argv\ArgvReader;

require_once dirname(dirname(__FILE__))."/init.environment.php";

$argvReader = new ArgvReader(new ArgvParser(new ArgvOptMap([
	new ArgvOpt('-install','Installe un package pour le projet courant',null,null,true),
	new ArgvOpt('-uninstall','Désinstalle un package pour le projet courant',null,null,true)
])),$argv);

try{
	$exec = function(string $cmd):void{
		$outputs = []; $res = null;
		exec($cmd,$outputs,$res);
		if($res !== 0) throw new Exception(
			"Error trying to exec '$cmd'".
			" code $res, outputs : ".implode("\n",$outputs)
		);
	};

	$site = dirname(dirname(__DIR__))."/site";
	$engine = dirname(dirname(__DIR__))."/engine";

	if($argvReader->exists('-install')){
		$args = $argvReader->get('-install');
		foreach($args as $package){
			$tmp = explode("/",$package);
			$location = $site.'/package';
			$l = 'site';
			if(count($tmp)===2){
				$location = ($tmp[0] === 'engine')
					? $engine."/package/".($p=$tmp[1]) : "$location/".($p=$tmp[1]);
				$l = $tmp[0];
			}
			else $location = "$location/".($p = $tmp[0]);
			if(is_dir($location)){
				$webroot = array_diff(scandir("$location/webroot"),['..','.']);
				foreach($webroot as $dir){
					if(!is_dir("$site/webroot/$dir")) mkdir("$site/webroot/$dir");
					if(is_link("$site/webroot/$dir/$p")) unlink("$site/webroot/$dir/$p");
					chdir("$site/webroot/$dir");
					$exec("ln -s \"../../../$l/package/$p/webroot/$dir\" \"$p\"");
				}
			}else fwrite(STDOUT,"\e[33mWFW_installer UNKNOWN_PACKAGE\e[0m : $package\n");
			fwrite(STDOUT,"$package installed.\n");
		}
	}else if($argvReader->exists('-uninstall')){
		$args = $argvReader->get('-uninstall');
		foreach($args as $package){
			$tmp = explode("/",$package);
			$location = $site.'/package';
			$l = 'site';
			if(count($tmp)===2){
				$location = ($tmp[0] === 'engine')
					? $engine."/package/".($p=$tmp[1]) : "$location/".($p=$tmp[1]);
				$l = $tmp[0];
			}
			else $location = "$location/".($p = $tmp[0]);
			if(is_dir($location)){
				$webroot = array_diff(scandir("$location/webroot"),['..','.']);
				foreach($webroot as $dir){
					unlink("$site/webroot/$dir/$p");
				}
			}else fwrite(STDOUT,"\e[33mWFW_installer UNKNOWN_PACKAGE\e[0m : $package\n");
		}
	}else throw new InvalidArgumentException("Unknown command $argv[1]");
}catch(\InvalidArgumentException $e){
	fwrite(STDOUT,"\e[33mWFW_installer WRONG_USAGE\e[0m : {$e->getMessage()}".PHP_EOL);
	exit(1);
}catch(\Exception $e){
	if($argvReader->exists('--debug'))
		fwrite(STDOUT,"\e[31mWFW_installer ERROR\e[0m : ".PHP_EOL."$e".PHP_EOL);
	else
		fwrite(
			STDOUT,
			"\e[31mWFW_installer ERROR\e[0m (try --debug for more) : {$e->getMessage()}".PHP_EOL
		);
	exit(2);
}
exit(0);