<?php

namespace wfw\engine\lib\logger;

use wfw\engine\lib\errors\FileNotFound;
use wfw\engine\lib\errors\PermissionDenied;

/**
 * Permet d'écrire des logs dans un/plusieur fichier(s).
 */
final class FileLogger implements ILogger {
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
	public function log(string $message, int... $type): void {
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
	public function applyRedirections(int $type,string $message,array &$exclusions):bool{
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
	public function applyMerges(int $type, string $message, array &$exclusions):void{
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
	public function disable(int... $type): void {
		foreach($type as $t){
			$this->_disabled["$t"]=true;
		}
	}

	/**
	 * @param int ...$type Active les logs spécifiés s'ils sont désactivés.
	 */
	public function enable(int... $type): void {
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
	public function redirect(int $to, int... $from): void {
		$toRedirections = $this->getRedirections($to);
		$recursives = array_intersect($toRedirections,$from);
		if(count($recursives))
			throw new \InvalidArgumentException(
				"Can't set a recursive redirection. Attempting to redirect ".
				implode(",",$from)." to $to, but $to is already redirected to "
				.implode(",",$recursives)
			);
		if(is_bool(array_search($to,$from))) throw new \InvalidArgumentException(
			"Can't set a recursive rediection: $to can't be in from args !"
		);
		foreach($from as $f){
			if(!isset($this->_redirections["$f"])) $this->_redirections["$f"] = [$to];
			else $this->_redirections["$f"][]=$to;
		}
	}

	/**
	 * Copie tous les logs $from vers $to sans duplication. Les entrées dans les logs de base seront
	 * conservées.
	 *
	 * @param int $to      Destination de la copie
	 * @param int ...$from Cibles de la copie
	 */
	public function merge(int $to, int... $from): void {
		$toMerges = $this->getMerges($to);
		$recursives = array_intersect($toMerges,$from);
		if(count($recursives))
			throw new \InvalidArgumentException(
				"Can't set a recursive merge. Attempting to merges ".
				implode(",",$from)." to $to, but $to is already merged into "
				.implode(",",$recursives)
			);
		if(is_bool(array_search($to,$from))) throw new \InvalidArgumentException(
			"Can't set a recursive merge: $to can't be in from args !"
		);
		foreach($from as $f){
			if(!isset($this->_merges["$f"])) $this->_merges["$f"] = [$to];
			else $this->_merges["$f"][]=$to;
		}
	}
}