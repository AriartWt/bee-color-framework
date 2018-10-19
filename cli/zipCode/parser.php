#!/usr/bin/php -q
<?php

use wfw\engine\lib\cli\argv\ArgvOpt;
use wfw\engine\lib\cli\argv\ArgvOptMap;
use wfw\engine\lib\cli\argv\ArgvParser;
use wfw\engine\lib\cli\argv\ArgvReader;

/**
 * Note à moi même :
 *
 * Ce script permet d'extraire les noms des communes et les codes postaux des fichiers
 * trouvés sur http://bano.openstreetmap.fr/BAN_odbl/csv/
 * Les fichiers doivent tous se trouver dans le même dossier et être préalablement décompressés.
 * Les fichiers à telecharger sont au format csv, pour un traitement ligne par ligne.
 * Il les aggrége dans un nouveau fichier au format csv contenant code_postal;commune
 * Un autre script permet ensuite de convertir ce fichier en un fichier PHP important
 * un tableau indéxé par code postal : converter.php
 * Ce fichier PHP est à utiliser lorsque OPCACHE est activé.
 * Sinon le jeu de données CSV peut être utilisé pour remplire une base mysql ou autre systeme.
 */
require_once dirname(__DIR__)."/init.environment.php";

$argvReader = new ArgvReader(new ArgvParser(new ArgvOptMap([
	new ArgvOpt(
		"-s",
		"Chemin d'accés au dossier contenant les fichiers à traiter",
		1,
		function($dirname){ return is_dir($dirname);},
		false,
		"[$0] n'est pas un dossier"
	),
	new ArgvOpt(
		"-d",
		"Nom du fichier de sortie",
		1,
		function($fileName){ return is_dir(dirname($fileName)); },
		false,
		"[$0] existe déjà."
	),
	new ArgvOpt('-sep', 'Séparateur (défaut : , )', 1,null,true),
	new ArgvOpt('-z', 'ZipCode index (défaut : 6)', 1,function($data){
		return intval($data) >= 0;
	},true,"L'index doit être positif"),
	new ArgvOpt('-c', 'Commune index (défaut : 11)', 1,function($data){
		return intval($data) >= 0;
	},true,"L'index doit être positif")
])),$argv);

$d = $argvReader->get('-d')[0];
$source = $argvReader->get("-s")[0];
$sep = $argvReader->exists('-sep') ? $argvReader->get('-sep')[0] : ',';
$zIndex = $argvReader->exists('-z') ? $argvReader->get('-z')[0] : 6;
$cIndex = $argvReader->exists('-c') ? $argvReader->get('-c')[0] : 11;

$start = microtime(true);
$files = 0;
$lines = 0;
$cache = [];
$dest = fopen($d,'w');
fwrite($dest,"code_postal;commune\n");
foreach(array_diff(scandir($source),['.','..']) as $f){
	fwrite(STDOUT,"Reading file $source/$f ... \n");
	$files++;
	$current = fopen("$source/$f",'r');
	//on passe la première ligne qui contient les intitulés de colonnes
	fgets($current);
	while(!feof($current)){
		$tmp = explode($sep, $line = fgets($current));
		if(count($tmp)>1){
			$lines++;
			$cacheKey = "$tmp[$zIndex];$tmp[$cIndex]";

			if(!isset($cache[$cacheKey])){
				$cache[$cacheKey] = true;
				fwrite($dest,"$cacheKey\n");
			}
		}
	}
	fclose($current);
}
fclose($dest);
fwrite(STDOUT, "Done (".((microtime(true)-$start))
       ."s to parse $files files and $lines lines) \n"
);