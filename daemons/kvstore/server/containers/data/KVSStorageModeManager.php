<?php
namespace wfw\daemons\kvstore\server\containers\data;

/**
 *  Gére un mode de stockage
 */
interface KVSStorageModeManager {
	/**
	 *  Obtient la valeur associées à une clé
	 * @param string $key Clé dont on souhaite obtenir les données
	 * @return mixed
	 */
	public function get(string $key);

	/**
	 *  Enregistre une valeur par une clé
	 * @param string      $key  Clé de stockage
	 * @param mixed       $data Données associées
	 */
	public function set(string $key,$data);

	/**
	 *  Supprime une clé et les données associées
	 * @param string $key Clé à supprimer
	 */
	public function remove(string $key);

	/**
	 * @param string $key
	 * @return bool True si la clé existe, false sinon
	 */
	public function exists(string $key):bool;
}