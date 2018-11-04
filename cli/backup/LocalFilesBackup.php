<?php
namespace wfw\cli\backup;

use wfw\cli\backup\errors\BackupFailure;
use wfw\cli\backup\errors\BackupNotFound;

/**
 * Crée un backup de fichiers locaux
 */
final class LocalFilesBackup implements IBackup {
	/** @var string $_target */
	private $_target;
	/** @var string $_dest */
	private $_dest;
	/** @var null|float $_date */
	private $_date;

	/**
	 * LocalFilesDump constructor.
	 *
	 * @param string $target Cible du backup
	 * @param string $dest   Destination du backup
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $target,string $dest) {
		if(!is_dir($target))
			throw new \InvalidArgumentException("$target is not a valide directory");
		$this->_dest = $dest;
		$this->_target = $target;
	}

	/**
	 * Crée un backup
	 */
	public function make(): void{
		$this->remove();
		if(!is_dir($this->_dest))
			mkdir($this->_dest,0700,true);
		$this->exec("cp -Rp \"$this->_target\" \"$this->_dest\"");
		$this->_date = microtime(true);
	}

	/**
	 * Restore l'application avec le backup courant
	 */
	public function load(): void{
		if(!is_dir($this->_dest.'/'.basename($this->_target)))
			throw new BackupNotFound("Backup of $this->_target failed : $this->_dest is empty");
		$this->exec("rm -rf \"$this->_target\"");
		$this->exec(
			'cp -Rp "'.$this->_dest.'/'.basename($this->_target).'" "'
			.dirname($this->_target).'"'
		);
	}

	/**
	 * Supprime le backup courant
	 */
	public function remove(): void {
		if(is_dir($this->_dest.'/'.basename($this->_target)))
			$this->exec("rm -rf $this->_dest/".basename($this->_target));
	}

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
	 * @return null|float Date de création du backup avec la fonction make()
	 */
	public function makeDate(): ?float { return $this->_date; }

	/**
	 * @return string Localisation du backup
	 */
	public function getLocation(): string
	{
		return $this->_dest;
	}

	/**
	 * @param string $path nouvelle localisation du backup
	 */
	public function setLocation(string $path): void
	{
		$this->_dest = $path;
	}
}