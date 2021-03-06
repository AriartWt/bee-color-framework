<?php
namespace wfw\engine\lib\data\string\serializer;

/**
 * Interface d'un serializer.
 */
interface ISerializer {
	/**
	 * @param mixed $data Données à sérialiser.
	 * @return string Données sérialisées
	 */
	public function serialize($data):string;

	/**
	 * @param string $data Donnée à désérialiser.
	 * @return mixed Données désérialsiées.
	 */
	public function unserialize(string $data);
}