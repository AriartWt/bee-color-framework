<?php

namespace wfw\engine\lib\logger;

/**
 * Interface IRTSLogger.
 */
interface ILogger {
	public final const LOG=1;
	public final const ERR=2;
	public final const WARN=4;
	public final const DEBUG=8;

	/**
	 * @param string $message Message à écrire
	 * @param int    ...$type Type de log
	 */
	public function log(string $message,int... $type):void;

	/**
	 * @param int ...$type Désactive les logs spécifiés
	 */
	public function disable(int... $type):void;

	/**
	 * @param int ...$type Active les logs spécifiés s'ils sont désactivés.
	 */
	public function enable(int... $type):void;

	/**
	 * Redirigie tous les logs $from vers $to sans duplication
	 * @param int $to Destination de la redirection
	 * @param int ...$from Cibles de la redirection
	 */
	public function redirect(int $to, int... $from):void;

	/**
	 * Copie tous les logs $from vers $to sans duplication. Les entrées dans les logs de base seront
	 * conservées.
	 * @param int $to Destination de la copie
	 * @param int ...$from Cibles de la copie
	 */
	public function merge(int $to, int... $from):void;

	/**
	 * COnfigure automatique un fichier de logs en fonction d'un niveau
	 * TODO : limit explain
	 * @param int   $level Niveau de logs
	 * @param int   $to    Destination des logs
	 * @param bool  $merge Si true, merge. Sinon, redirections
	 * @param array $limit Limite pour les inclusions de log
	 * @return int[] Liste des levels pris en compte
	 */
	public function autoConfByLevel(
		int $level,
		int $to,
		bool $merge = true,
		array $limit = ["from"=>self::LOG,"to"=>self::WARN]
	):array;
}