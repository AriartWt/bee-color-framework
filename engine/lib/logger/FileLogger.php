<?php

namespace wfw\engine\lib\logger;

use wfw\engine\lib\errors\FileNotFound;
use wfw\engine\lib\errors\PermissionDenied;

/**
 * Permet d'écrire des logs dans un/plusieur fichier(s).
 */
class FileLogger implements ILogger {
	/** @var string[] fichiers de log. */
	private $_files;
	/** @var array $_disabled Fichiers de log désactivés */
	private $_disabled;
	/** @var ILogFormater $_formater Formateur de logs */
	private $_formater;
	/** @var array $_redirections Redirections de logs */
	private $_redirections;
	/** @var array $_merges Copies de logs */
	private $_merges;

	/**
	 * FileLogger constructor.
	 *
	 * @param ILogFormater $formater Objet permettant de formater les lignes de log
	 * @param string       ...$files Fichiers de logs utilisables
	 * @throws FileNotFound
	 * @throws PermissionDenied
	 */
	public function __construct(ILogFormater $formater,string... $files) {
		foreach($files as $f){
			if(!file_exists($f)) throw new FileNotFound($f);
			if(!is_writable($f)) throw new PermissionDenied("no write access to $f");
		}
		$this->_files = $files;
		$this->_merges = [];
		$this->_disabled = [];
		$this->_redirections = [];
		$this->_formater = $formater;
	}

	/**
	 * @param string $message Message à écrire
	 * @param int    ...$type Type de log
	 */
	public final function log(string $message, int... $type): void {
		if(empty($type)) $type=[self::LOG];
		$alreadyLogged=[];
		foreach($type as $t){
			if(isset($this->_files[$t]) && $this->isEnabled($t)){
				if(!$this->applyRedirections($t,$message,$alreadyLogged)){
					$this->write($t,$message,$alreadyLogged);
				}
			} else $alreadyLogged["$t"] = true;
		}
	}

	/**
	 * Suit la/les redirection(s) configurée sur un fichier donné et ecrit dans le(s) concerné(s)
	 * @param int    $type       Fichier de log
	 * @param string $message    Message à écrire dans le slogs
	 * @param array  $exclusions Fichiers déjà traités
	 * @return bool True si au moins une redirection, false sinon
	 */
	private function applyRedirections(int $type,string $message,array &$exclusions):bool{
		$redirections = $this->getRedirections($type);
		$redirected = false;
		foreach($redirections as $r){
			if(!isset($exclusions["$r"]) && $this->isEnabled($r)){
				if(!$this->applyRedirections($r,$message,$exclusions)){
					$this->write($r,$message,$exclusions);
				}else $exclusions["$r"] = $redirected = true;
			}
		}
		return $redirected;
	}

	/**
	 * Applique la/les copie(s) de log dans le(s) fichier(s) concerné(s) en tant compte des éventuelles
	 * redirections
	 * @param int    $type       Fichier de log
	 * @param string $message    Message à écrire dans les logs
	 * @param array  $exclusions Liste des fichiers déjà traités
	 */
	private function applyMerges(int $type, string $message, array &$exclusions):void{
		$merges = $this->getMerges($type);
		foreach($merges as $m){
			if(!isset($exclusions["$m"]) && $this->isEnabled($m)){
				if(!$this->applyRedirections($m,$message,$exclusions)) {
					$this->write($m,$message,$exclusions);
				}
			}
		}
	}

	/**
	 * @param int $type Fichier à tester
	 * @return bool True si les logs vers ce fichiers sont activés
	 */
	private function isEnabled(int $type):bool{
		return !($this->_disabled["$type"] ?? false);
	}

	/**
	 * @param int $type Fichier concerné
	 * @return array Liste des redirections
	 */
	private function getRedirections(int $type):array{
		return array_unique($this->_redirections["$type"] ?? []);
	}

	/**
	 * @param int $type
	 * @return array Liste des copies
	 */
	private function getMerges(int $type):array{
		return array_unique($this->_merges["$type"] ?? []);
	}

	/**
	 * Ecrit le log dans le fichier concerné s'il est activé et pas encore traité, puis vérifie si
	 * des copies doivent être effectuées dans d'autres fichiers.
	 * @param int    $type    Fichier
	 * @param string $message Message à écrire
	 * @param array  $exclusions
	 */
	private function write(int $type,string $message, array &$exclusions):void{
		if($this->isEnabled($type) && !isset($exclusions["$type"])){
			file_put_contents(
				$this->_files[$type],
				$this->_formater->format($message),
				FILE_APPEND
			);
			$exclusions["$type"] = true;
			$this->applyMerges($type,$message,$exclusions);
		}
	}

	/**
	 * @param int ...$type Désactive les logs spécifiés
	 */
	public final function disable(int... $type): void {
		foreach($type as $t){
			$this->_disabled["$t"]=true;
		}
	}

	/**
	 * @param int ...$type Active les logs spécifiés s'ils sont désactivés.
	 */
	public final function enable(int... $type): void {
		foreach($type as $t){
			if(isset($this->_disabled["$t"])) unset($this->_disabled["$t"]);
		}
	}

	/**
	 * Redirigie tous les logs $from vers $to sans duplication
	 *
	 * @param int $to      Destination de la redirection
	 * @param int ...$from Cibles de la redirection
	 */
	public final function redirect(int $to, int... $from): void {
		$this->checkRecurrence(false,$to,...$from);
		foreach($from as $f){
			$this->addRecordTo($this->_redirections,$f,$to);
		}
	}

	/**
	 * Copie tous les logs $from vers $to sans duplication. Les entrées dans les logs de base seront
	 *
	 * @param int $to      Destination de la copie
	 * @param int ...$from Cibles de la copie
	 */
	public final function merge(int $to, int... $from): void {
		$this->checkRecurrence(true,$to,...$from);
		foreach($from as $f){
			$this->addRecordTo($this->_merges,$f,$to);
		}
	}

	/**
	 * @param array $array
	 * @param int   $from
	 * @param int   $to
	 */
	private function addRecordTo(array &$array, int $from, int $to):void{
		if(!isset($array["$from"])) $array["$from"] = [$to];
		else $array["$from"][]=$to;
	}

	/**
	 * @param bool $merge
	 * @param int  $simulatedTo
	 * @param int  ...$simulatedFroms
	 */
	private function checkRecurrence(
		bool $merge=true,
		int $simulatedTo,
		int... $simulatedFroms
	){
		$records = ($merge) ? $this->_merges : $this->_redirections;
		foreach($simulatedFroms as $f){
			$this->addRecordTo($records,$f,$simulatedTo);
		}
		foreach($records as $from => $tos){
			$consumed=[];
			if($this->hasRecursion($records,$from,$consumed))
				throw new \InvalidArgumentException(
					"Infinite ".($merge?"merge":"redirection")." recursion found, "
					."please check your logger config !"
				);
		}
	}

	/**
	 * Verifie s'il exise une boucle infinie dans les définitions
	 *
	 * @param array    $records
	 * @param int      $start
	 * @param array    $consumed
	 * @param int|null $checkFromsOf
	 * @return bool
	 */
	private function hasRecursion(
		array &$records,
		int $start,
		array &$consumed,
		?int $checkFromsOf=null
	):bool{
		$froms = (is_null($checkFromsOf)) ? $records["$start"]??[] : $records["$checkFromsOf"]??[];
		if(count($froms)>0){
			if(count(array_intersect($consumed,$froms))>0){
				return false;
			}else if(!in_array($start,$froms)){
				foreach($froms as $from){
					$consumed[]=$from;
					if(!$this->hasRecursion($records,$start,$consumed,$from)) return true;
				}
			}
		}
		return false;
	}

	/**
	 * Configure automatiquement un fichier de logs en fonction d'un niveau.
	 * Ex : autoConfByLevel(ILogger::ERR | ILogger::LOG | ILogger::WARN, ILogger::DEBUG)
	 * Permet de dupliquer tous les logs ERR,LOG et WARN dans DEBUG
	 *
	 * @param int  $level Niveau de logs
	 * @param int  $to    Destination des logs
	 * @param bool $merge Si true, merge. Sinon, redirections
	 */
	public final function auoConfFileByLevel(int $level, int $to, bool $merge = true){
		$bytes = array_reverse(str_split(decbin($level)));
		$froms=[];
		foreach($bytes as $k=>$b){ if($b==='1') $froms[]=pow(2,$k); }
		if(count($froms)>0){
			if($merge) $this->merge($to,...$froms);
			else $this->redirect($to,...$froms);
		}
	}

	/**
	 * Permet d'activer/désactiver des fichiers de log en fonction d'un niveau de log
	 * @param int  $level  Niveau de log
	 * @param bool $enable Si true, active les fichiers désigné par level. Les désactive sinon.
	 */
	public final function autoConfByLevel(int $level, bool $enable = true){
		$bytes = array_reverse(str_split(decbin($level)));
		$files=[];
		foreach($bytes as $k=>$b){
			if($b==='1') $files[]=pow(2,$k);
		}
		if($enable) $this->enable(...$files);
		else $this->disable(...$files);
	}
}