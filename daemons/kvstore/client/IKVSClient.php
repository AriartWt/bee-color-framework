<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/01/18
 * Time: 09:12
 */

namespace wfw\daemons\kvstore\client;

/**
 *  Interface d'un client du KVS
 */
interface IKVSClient
{
	/**
	 *  Obtient la valeur associée à une clé
	 * @param string $key Clé
	 * @return mixed
	 */
	public function get(string $key);

	/**
	 * @param string   $key         Clé d'enregistrement
	 * @param mixed    $data        Données associées à la clé
	 * @param float    $ttl         (optionnel défaut : 0) Temps de vie de la clé. Si 0 : pas de limite.
	 * @param int|null $storageMode Nouveau mode de stockage de la clé.
	 *                              Si null, le stockage de la clé sera celui de l'instance courante.
	 *                              Si null aussi, ce sera le mode de stockage par défaut du container.
	 */
	public function set(string $key,$data,float $ttl=0, ?int $storageMode=null);

	/**
	 *  Applique une durée de vie à une clé.
	 * @param string $key Clé concernée
	 * @param float  $ttl Nouveau temps de vie. Si <0 : la clé n'a plus de limite de vie.
	 */
	public function setTtl(string $key,float $ttl);

	/**
	 *  Change le mod ede stockage de la clé.
	 *
	 * @param string   $key         Clé concernée
	 * @param int|null $storageMode Nouveau mode de stockage de la clé.
	 *                              Si null, le stockage de la clé sera celui de l'instance courante.
	 *                              Si null aussi, ce sera le mode de stockage par défaut du container.
	 */
	public function changeStorageMode(string $key,?int $storageMode);

	/**
	 *  Supprime une clé
	 * @param string $key Clé à supprimer
	 */
	public function remove(string $key);

	/**
	 *  Teste l'existence d'une clé
	 * @param string $key Clé à tester
	 * @return bool
	 */
	public function exists(string $key):bool;

	/**
	 *  Supprime toutes les données du container.
	 */
	public function purge():void;

	/**
	 * @return null|int Mode de stockage par défaut de l'instance courante.
	 */
	public function getDefaultStorageMode():?int;

	/**
	 *  Connecte l'instance courante au serveur KVS
	 */
	public function login();

	/**
	 *  Deconnecte l'instance courante du serveur KVS
	 */
	public function logout():void;

	/**
	 * @return bool True si le client est loggé sur le serveur, false sinon.
	 */
	public function isLogged():bool;
}