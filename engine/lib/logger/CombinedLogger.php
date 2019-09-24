<?php

namespace wfw\engine\lib\logger;

/**
 * Combine loggers
 */
final class CombinedLogger implements ILogger {
	/** @var ILogger[] $_loggers */
	private $_loggers;

	/**
	 * CombinedLogger constructor.
	 *
	 * @param ILogger ...$loggers
	 */
	public function __construct(ILogger ...$loggers) {
		$this->_loggers = $loggers;
	}

	/**
	 * @param string $message Message à écrire
	 * @param int    ...$type Type de log
	 */
	public function log(string $message, int... $type): void {
		foreach($this->_loggers as $logger) $logger->log($message, ...$type);
	}

	/**
	 * @param int ...$type Désactive les logs spécifiés
	 */
	public function disable(int... $type): void {
		foreach($this->_loggers as $logger) $logger->disable(...$type);
	}

	/**
	 * @param int ...$type Active les logs spécifiés s'ils sont désactivés.
	 */
	public function enable(int... $type): void {
		foreach($this->_loggers as $logger) $logger->enable(...$type);
	}

	/**
	 * Redirigie tous les logs $from vers $to sans duplication
	 *
	 * @param int $to      Destination de la redirection
	 * @param int ...$from Cibles de la redirection
	 */
	public function redirect(int $to, int... $from): void {
		foreach($this->_loggers as $logger) $logger->redirect($to,...$from);
	}

	/**
	 * Copie tous les logs $from vers $to sans duplication. Les entrées dans les logs de base seront
	 * conservées.
	 *
	 * @param int $to      Destination de la copie
	 * @param int ...$from Cibles de la copie
	 */
	public function merge(int $to, int... $from): void {
		foreach($this->_loggers as $logger) $logger->merge($to,...$from);
	}

	/**
	 * Configure automatiquement un fichier de logs en fonction d'un niveau.
	 * Ex : autoConfByLevel(ILogger::ERR | ILogger::LOG | ILogger::WARN, ILogger::DEBUG)
	 * Permet de dupliquer tous les logs ERR,LOG et WARN dans DEBUG
	 *
	 * @param int  $level Niveau de logs
	 * @param int  $to    Destination des logs
	 * @param bool $merge Si true, merge. Sinon, redirections
	 * @return ILogger
	 */
	public function autoConfFileByLevel(int $level, int $to, bool $merge = true): ILogger {
		foreach($this->_loggers as $logger) $logger->autoConfFileByLevel($level,$to,$merge);
		return $this;
	}

	/**
	 * Permet d'activer/désactiver des fichiers de log en fonction d'un niveau de log
	 *
	 * @param int  $level  Niveau de log
	 * @param bool $enable Si true, active les fichiers désigné par level. Les désactive sinon.
	 * @return ILogger
	 */
	public function autoConfByLevel(int $level, bool $enable = true): ILogger {
		foreach($this->_loggers as $logger) $logger->autoConfByLevel($level,$enable);
		return $this;
	}
}