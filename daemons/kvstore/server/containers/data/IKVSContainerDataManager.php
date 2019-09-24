<?php
namespace wfw\daemons\kvstore\server\containers\data;

/**
 *  Gestionnaire de données d'un container
 */
interface IKVSContainerDataManager {
	/**
	 * @param string $key Clé de stockage
	 * @return mixed Données associées à la clé
	 */
	public function get(string $key);

	/**
	 *  Crée ou modifie une clé
	 *
	 * @param string      $key  Clé de stockage
	 * @param mixed       $data Données à sauvegarder
	 * @param float       $ttl  Durée de vie de la clé
	 * @param int         $mode Mode de stockage
	 */
	public function set(string $key,$data,float $ttl=0,?int $mode=null);

	/**
	 *  Ajoute une date de péremption sur une clé
	 * @param string $key Clé à modifier
	 * @param float  $ttl Nouveau temps de vie
	 */
	public function setTtl(string $key, float $ttl);

	/**
	 *  Supprime une clé du container
	 * @param string $key Clé à supprimer
	 */
	public function remove(string $key);

	/**
	 * @param string $key Clé à tester
	 * @return bool True si la clé existe et est valide, false sinon
	 */
	public function exists(string $key):bool;

	/**
	 *  Supprime toutes les clés du container
	 */
	public function purge():void;

	/**
	 *  Réinitialise le temps de vie d'une clé
	 * @param string   $key Clé concernée
	 * @param int|null $ttl (optionnel) Nouveau temps de vie. Si non spécifié,
	 *                      l'ancien TTL défini pour la clé sera utilisé.
	 *                      Si aucun ttl n'était définit, la fonction n'a aucun effet.
	 */
	public function touch(string $key,?int $ttl=null):void;

	/**
	 *  Change le mode de stockage d'une clé.
	 * @param string $key            Clé à changer de stockage.
	 * @param int    $newStorageMode Nouveau systeme de stockage (voir KVSMode)
	 */
	public function changeStorageMode(string $key,int $newStorageMode);
}