<?php
namespace wfw\cli\backup;

/**
 * Gestionnaire de backups
 */
interface IBackupManager extends \ArrayAccess,\Iterator,\Countable {
	/**
	 * @param string $name Nom du backup à récupérer
	 * @return IBackup
	 */
	public function get(string $name):IBackup;

	/**
	 * @param string  $name Nom du backup à ajouter au manager
	 * @param IBackup $backup Ba
	 */
	public function add(string $name,IBackup $backup):void;

	/**
	 * @param string $name Nom du backup à supprimer
	 */
	public function remove(string $name):void;

	/**
	 * @param string $name Nom du backup à tester
	 * @return bool True si le backup existe, false sinon
	 */
	public function exists(string $name):bool;

	/**
	 * @param int $max Nombre maximum de backups conservés.
	 */
	public function changeMaxBackup(int $max):void;
}