<?php
namespace wfw\daemons\kvstore\server\containers\data;

/**
 *  Clé de registre
 */
interface IKVSRegisteryKey {
	/**
	 * @return string Nom de la clé
	 */
	public function getName():string;

	/**
	 * @return float Temps d'expiration définit
	 */
	public function getTtl():float;

	/**
	 * @param float $ttl   Nouveau ttl
	 * @param bool  $touch (optionnel défaut : true) modifie la date d'expiration en appliquant le nouveau ttl.
	 */
	public function changeTtl(float $ttl=0,bool $touch = true);

	/**
	 * @return bool True si la clé a expiré, false sinon
	 */
	public function expired():bool;

	/**
	 * @param float|null $ttl (optionnel) Temps en secondes avant expiration de la clé..
	 *                        Si non précisé rajoute le ttl de base.
	 *                        Si aucun ttl n'était défini, la fonction n'a aucun effet.
	 */
	public function touch(?float $ttl=null);

	/**
	 * @return int Mode de stockage de la clé
	 */
	public function getStorageMode():int;

	/**
	 * @param int $mode Nouveau mode de stockage.
	 */
	public function changeStorageMode(int $mode);
}