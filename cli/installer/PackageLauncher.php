#!/usr/bin/php -q
<?php

use wfw\engine\lib\cli\argv\ArgvOpt;
use wfw\engine\lib\cli\argv\ArgvOptMap;
use wfw\engine\lib\cli\argv\ArgvParser;
use wfw\engine\lib\cli\argv\ArgvReader;

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."init.environment.php";

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

	if($argvReader->exists('-install')){
		$args = $argvReader->get('-install');
		foreach($args as $package){
			$tmp = explode("/",$package);
			$location = SITE.'/package';
			$l = 'site';
			if(count($tmp)===2){
				$location = ($tmp[0] === 'engine')
					? ENGINE."/package/".($p=$tmp[1]) : "$location/".($p=$tmp[1]);
				$l = $tmp[0];
			}
			else $location = "$location/".($p = $tmp[0]);
			if(is_dir($location)){
				$webroot = array_diff(scandir("$location/webroot"),['..','.']);
				foreach($webroot as $dir){
					if(!is_dir(SITE."/webroot/$dir")) mkdir(SITE."/webroot/$dir");
					if(is_link(SITE."/webroot/$dir/$p")) unlink(SITE."/webroot/$dir/$p");
					chdir(SITE."/webroot/$dir");
					$exec("ln -s \"../../../$l/package/$p/webroot/$dir\" \"$p\"");
				}
			}else fwrite(STDOUT,"\e[33mWFW_installer UNKNOWN_PACKAGE\e[0m : $package\n");
		}
	}else if($argvReader->exists('-uninstall')){
		$args = $argvReader->get('-uninstall');
		foreach($args as $package){
			$tmp = explode("/",$package);
			$location = SITE.'/package';
			$l = 'site';
			if(count($tmp)===2){
				$location = ($tmp[0] === 'engine')
					? ENGINE."/package/".($p=$tmp[1]) : "$location/".($p=$tmp[1]);
				$l = $tmp[0];
			}
			else $location = "$location/".($p = $tmp[0]);
			if(is_dir($location)){
				$webroot = array_diff(scandir("$location/webroot"),['..','.']);
				foreach($webroot as $dir){
					unlink(SITE."/webroot/$dir/$p");
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