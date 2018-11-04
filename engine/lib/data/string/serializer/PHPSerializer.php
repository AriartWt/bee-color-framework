<?php
namespace wfw\engine\lib\data\string\serializer;

/**
 * Utilise le serializer PHP
 */
class PHPSerializer implements ISerializer {
	/**
	 * @param mixed $data Données à sérialiser.
	 *
	 * @return string Données sérialisées
	 */
	public function serialize($data): string {
		return serialize($data);
	}

	/**
	 * @param string $data Donnée à désérialiser.
	 *
	 * @return mixed Données désérialsiées.
	 */
	public function unserialize(string $data) {
		return unserialize($data);
	}
}