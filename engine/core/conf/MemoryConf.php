<?php
namespace wfw\engine\core\conf;

use wfw\engine\lib\PHP\errors\IllegalInvocation;

/**
 * Configuration en mémoire
 */
final class MemoryConf extends AbstractConf {
	/**
	 * Persiste la configuration courante
	 */
	public function save(): void {
		throw new IllegalInvocation("Cannot save memory conf !");
	}
}