<?php

namespace wfw\engine\lib\logger;

/**
 * Formate un log
 */
interface ILogFormater {
	/**
	 * @param string $message Message à formater
	 * @return string Log formaté
	 */
	public function format(string $message):string;
}