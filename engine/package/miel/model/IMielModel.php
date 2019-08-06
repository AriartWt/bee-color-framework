<?php
namespace wfw\engine\package\miel\model;

/**
 * Liste de valeurs associées à des clés.
 */
interface IMielModel extends \ArrayAccess,\Iterator {
	public const CACHE_KEY = "WFW/WebApp/packages/miel/model";
	/**
	 * @param string $key Clé à tester.
	 * @return bool True si la clé existe, false sinon
	 */
	public function exists(string $key):bool;

	/**
	 * @param string $key Clé d'accès
	 * @return mixed Données
	 */
	public function get(string $key);

	/**
	 * @param string $key Clé concernée
	 * @return array Paramètres de la clé
	 */
	public function getParams(string $key):array;

	/**
	 * Ajoute ou modifie une clé
	 * @param string $key  Clé d'accès
	 * @param mixed  $data Données
	 */
	public function set(string $key, $data):void;

	/**
	 * @param string $key    Clé concernée
	 * @param array  $params Paramètres à appliquer
	 */
	public function setParams(string $key, array $params):void;

	/**
	 * Supprime une clé.
	 * @param string $key Clé d'accès.
	 */
	public function remove(string $key):void;

	/**
	 * Remet à 0 un tableau de paramètres pour une clé.
	 * @param string     $key Clé concernée
	 * @param array|null $params (optionnel) Index des paramètres à supprimer
	 */
	public function resetParams(string $key,?array $params=null):void;

	/**
	 * Demande la sauvegarde des modifications effectuées sur les données.
	 */
	public function save():void;
}