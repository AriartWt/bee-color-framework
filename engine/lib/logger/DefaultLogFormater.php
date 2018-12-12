<?php

namespace wfw\engine\lib\logger;

/**
 * Formateur de log par défaut
 */
final class DefaultLogFormater implements ILogFormater {
	/**
	 * @param string $message Message à formater
	 * @return string Log formaté
	 */
	public function format(string $message): string {
		return "[".date("d-m-Y H:i:s",time())."] $message\n";
	}
}