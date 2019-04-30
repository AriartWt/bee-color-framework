<?php

namespace wfw\engine\lib\logger;

/**
 * Formate un log
 */
interface ILogFormater {
	public const LEVEL=[
		ILogger::LOG => "LOG",
		ILogger::ERR => "ERR",
		ILogger::WARN => "WARN",
		ILogger::DEBUG => "DEBUG"
	];

	/**
	 * @param string $message Message à formater
	 * @param int    $loglevel
	 * @return string Log formaté
	 */
	public function format(string $message,int $loglevel=-1):string;
}