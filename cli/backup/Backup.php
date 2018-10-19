<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 04/04/18
 * Time: 04:09
 */

namespace wfw\cli\backup;

use wfw\cli\backup\errors\BackupFailure;

/**
 * Aggrégat de backups
 */
final class Backup implements IBackup {
	/** @var IBackup[] $_backups */
	private $_backups;
	/** @var null|float $_date */
	private $_date;
	/** @var string $_folder */
	private $_folder;

	/**
	 * LocalBackup constructor.
	 *
	 * @param string    $folder  Dossier dans lequel seront enregistrés les backups (s'ils sont
	 *                           configurés pour)
	 * @param IBackup[] $backups Liste de backups
	 * @throws BackupFailure
	 */
	public function __construct(string $folder,IBackup ...$backups){
		if(!is_dir(dirname($folder)))
			throw new BackupFailure("$folder is not a valide folder");
		$this->_folder = $folder;
		$this->_backups = $backups;
	}

	/**
	 * Crée un backup
	 */
	public function make(): void {
		if(!is_dir($this->_folder))
			mkdir($this->_folder,0700);
		foreach($this->_backups as $backup){ $backup->make(); }
		$this->_date = microtime(true);
	}

	/**
	 * Restore l'application avec le backup courant
	 */
	public function load(): void { foreach($this->_backups as $backup){ $backup->load(); } }

	/**
	 * Supprime le backup courant
	 */
	public function remove(): void {
		foreach ($this->_backups as $backup){ $backup->remove(); }
		$this->exec("rm -rf \"$this->_folder\"");
	}

	/**
	 * @return null|float Date de création du backup avec la fonction make()
	 */
	public function makeDate(): ?float { return $this->_date; }

	/**
	 * @param string $cmd Commande à executer
	 * @throws BackupFailure
	 */
	private function exec(string $cmd):void{
		$outputs = []; $res = null;
		exec($cmd,$outputs,$res);
		if($res !== 0)
			throw new BackupFailure(
				"Error trying to exec '$cmd'".
				" code $res, outputs : ".implode("\n",$outputs)
			);
	}

	/**
	 * @return string Localisation du backup
	 */
	public function getLocation(): string
	{
		return $this->_folder;
	}

	/**
	 * Tente de reconfigurer les backups qui étaient contenus dans son repertoir pour qu'ils soient
	 * résolus avec la nouvelle localisation
	 * @param string $path nouvelle localisation du backup
	 */
	public function setLocation(string $path): void
	{
		foreach($this->_backups as $backup){
			if(strpos($backup->getLocation(),$this->_folder) === 0)
				$backup->setLocation(str_replace($this->_folder,$path,$backup->getLocation()));
		}
		$this->_folder = $path;
	}
}