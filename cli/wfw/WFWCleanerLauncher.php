#!/usr/bin/php -q
<?php

use wfw\engine\core\conf\WFW;
use wfw\engine\lib\cli\argv\ArgvOpt;
use wfw\engine\lib\cli\argv\ArgvOptMap;
use wfw\engine\lib\cli\argv\ArgvParser;
use wfw\engine\lib\cli\argv\ArgvReader;

require_once(dirname(__DIR__)."/init.environment.php");

$argvReader = new ArgvReader(new ArgvParser(new ArgvOptMap([
	new ArgvOpt("-update","Specify that the clean is for update. If not specified, the clean will be applyed for import.",0,null,true),
	new ArgvOpt("-list","Display only the folder list that will be cleaned up, without performing cleaning step",0,null,true),
])),$argv);

WFW::collectModules();
if($argvReader->exists("-update")) $paths = WFW::cleanablePathsForUpdate();
else $paths = WFW::cleanablePaths();

if($argvReader->exists('-list')) foreach($paths as $path) fwrite(STDOUT,"$path\n");
else exec('rm -rf "'.implode('" "',$paths).'"');