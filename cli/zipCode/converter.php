#!/usr/bin/php -q
<?php

use wfw\engine\lib\cli\argv\ArgvOpt;
use wfw\engine\lib\cli\argv\ArgvOptMap;
use wfw\engine\lib\cli\argv\ArgvParser;
use wfw\engine\lib\cli\argv\ArgvReader;

/**
 * Permet de traduire le csv produit par le script parser.php en un fichier php indexant les villes
 * par code postaux.
 * Le tableau résultant est tri dans l"ordre des codes postaux du plus petit au plus grand.
 */
require_once dirname(__DIR__)."/init.environment.php";

$argvReader = new ArgvReader(new ArgvParser(new ArgvOptMap([
	new ArgvOpt(
		"-s",
		"Fichier CSV contenant les données",
		1,
		function($dirname){ return file_exists($dirname);},
		false,
		"[$0] n'est pas un fichier valide !"
	),
	new ArgvOpt(
		"-d",
		"Nom du fichier de sortie",
		1,
		function($fileName){ return is_dir(dirname($fileName)); },
		false,
		"[$0] existe déjà."
	)
])),$argv);

$source = $argvReader->get('-s')[0];
$dest = $argvReader->get('-d')[0];
$codes = [];

$toRead = fopen($source,'r');
fgets($toRead); //On passe la première ligne, qui contient l'intitulé des colonnes.
while(!feof($toRead)){
	$line = explode(';',str_replace("\n",'',fgets($toRead)));
	if(is_array($line) && count($line) === 2){
		if(isset($codes[$line[0]])) $codes[$line[0]][] = $line[1];
		else $codes[$line[0]] = [$line[1]];
	}
}
fclose($toRead);

ksort($codes);
$totalLength = count($codes);
$current = 0;

$toWrite = fopen($dest,'w');
fwrite($toWrite,"<?php\nreturn [\n");
foreach($codes as $code=>$cities){
    if(!empty($code)){
	    $str = "\t\"$code\"=>[\"".implode('","',$cities)."\"]";
	    if($current < $totalLength - 1) $str.=',';
	    fwrite($toWrite,"$str\n");
    }
}
fwrite($toWrite,"];");
fclose($toWrite);