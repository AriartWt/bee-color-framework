<?php
namespace wfw\engine\core\cache;

/**
 * Système de cache
 */
interface ICacheSystem {
	/**
	 *   Obtient la valeur d'une clé en cache
	 * @param  string    $key Clé de la valeur à rechercher
	 * @return mixed          Valeur attribuée à la clé
	 */
	public function get(string $key);
	/**
	 *  Cache une variable
	 * @param string      $key     Clé de stockage
	 * @param mixed       $data    Donnée à stocker
	 * @param float 		  $timeout Temps de stockage en secondes (0 : pas de temps de vie, existe jusqu'à la suppression manuelle)
	 * @return bool True si l'opération a réussi, false sinon
	 */
	public function set(string $key,$data,float $timeout=0):bool;
	/**
	 *   Mets à jour une donnée en cache
	 * @param  string      $key     Clé de la donnée à changer
	 * @param  mixed       $data    Nouvelle données
	 * @param  float         $timeout Temps de stockage en secondes (0 : pas de temps de vie, existe jusqu'à la suppression manuelle)
	 * @return bool True si l'opération a réussi, false sinon
	 */
	public function update(string $key,$data,float $timeout=0):bool;
	/**
	 *   Supprime une donnée en cache
	 * @param  string    $key Clé de la donnée à supprimer du cache
	 * @return bool           True si l'opération a réussi, false sinon
	 */
	public function delete(string $key):bool;
	/**
	 *   Vide le cache
	 * @return bool    True si l'opération a réussi, false sinon
	 */
	public function clear():bool;
	/**
	 *   Teste l'existence d'une clé de donnée en cache
	 * @param  string    $key Clé à tester
	 * @return bool           True si la clé existe, false sinon
	 */
	public function contains(string $key):bool;
}