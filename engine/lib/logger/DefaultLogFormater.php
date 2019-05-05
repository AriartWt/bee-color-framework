<?php

namespace wfw\engine\lib\logger;

/**
 * Formateur de log par défaut
 */
final class DefaultLogFormater implements ILogFormater {
	/**
	 * @param string $message Message à formater
	 * @param int    $loglevel
	 * @return string Log formaté
	 */
	public function format(string $message, int $loglevel=-1): string {
		return "[".\DateTime::createFromFormat('0.u00 U',microtime())->format("d-m-Y H:i:s.u")
			."][".(self::LEVEL["$loglevel"]??"UNKNOWN")."] $message\n";
	}
}