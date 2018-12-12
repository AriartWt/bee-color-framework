<?php

namespace wfw\engine\lib\logger;

/**
 * Log nothing.
 */
final class NullLogger implements ILogger {
	/**
	 * @param string $message Message à écrire
	 * @param int    ...$type Type de log
	 */
	public function log(string $message, int... $type): void {}

	/**
	 * @param int ...$type Désactive les logs spécifiés
	 */
	public function disable(int... $type): void {}

	/**
	 * @param int ...$type Active les logs spécifiés s'ils sont désactivés.
	 */
	public function enable(int... $type): void {}

	/**
	 * Redirigie tous les logs $from vers $to sans duplication
	 *
	 * @param int $to      Destination de la redirection
	 * @param int ...$from Cibles de la redirection
	 */
	public function redirect(int $to, int... $from): void {}

	/**
	 * Copie tous les logs $from vers $to sans duplication. Les entrées dans les logs de base seront
	 * conservées.
	 *
	 * @param int $to      Destination de la copie
	 * @param int ...$from Cibles de la copie
	 */
	public function merge(int $to, int... $from): void {}
}