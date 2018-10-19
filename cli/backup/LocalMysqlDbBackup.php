<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 04/04/18
 * Time: 03:07
 */

namespace wfw\cli\backup;

use wfw\cli\backup\errors\BackupFailure;

/**
 * Backup d'une table de base de données dans un fichier .sql local
 */
final class LocalMysqlDbBackup implements IBackup {
	/** @var string */
	private $_path;
	/** @var string */
	private $_host;
	/** @var string */
	private $_db;
	/** @var string */
	private $_user;
	/** @var string */
	private $_password;
	/** @var string $_mysqlPath */
	private $_mysqlPath;
	/** @var string $_mysqldumpPath */
	private $_mysqldumpPath;
	/** @var null|float $_date */
	private $_date;

	/**
	 * LocalDbBackup constructor.
	 *
	 * @param string $path     Chemin complet du backup
	 * @param string $host     Hote de la base de données
	 * @param string $db       base de données à sauvegarder
	 * @param string $user     utilisateur
	 * @param string $password mot de passe
	 * @param string $mysqldumpPath (optionnel defaut = mysqldump) Chemin d'accés à mysqldump
	 * @param string $mysqlPath     (optionnel defaut = mysql) Chemin d'accés à mysql
	 */
	public function __construct(
		string $path,
		string $host,
		string $db,
		string $user,
		string $password,
		string $mysqldumpPath = 'mysqldump',
		string $mysqlPath = 'mysql'
	){
		$this->_path = $path;
		$this->_host = $host;
		$this->_db = $db;
		$this->_user = $user;
		$this->_password = $password;
		$this->_mysqlPath = $mysqlPath;
		$this->_mysqldumpPath = $mysqldumpPath;
	}

	/**
	 * Crée un backup
	 */
	public function make(): void{
		$this->createTmpFile(false);
		if(!is_dir(dirname($this->_path))) mkdir($this->_path,0700,true);
		$this->exec(
			"\"$this->_mysqldumpPath\" "
			."--defaults-file=\"".$this->tmpPath(false)."\" "
			."-h \"$this->_host\" -u \"$this->_user\" "
			."\"$this->_db\" > \"$this->_path\""
		);
		$this->_date = microtime(true);
		$this->removeTmpFile(false);
	}

	/**
	 * Restore l'application avec le backup courant
	 */
	public function load(): void{
		if(!file_exists($this->_path))
			throw new BackupFailure("Cannot load backup $this->_path : no such file or directory");
		$this->createTmpFile();
		$this->exec(
			"\"$this->_mysqlPath\" --defaults-file=\"{$this->tmpPath()}\" "
			."-h \"$this->_host\" -u \"$this->_user\" "
			."\"$this->_db\" < \"$this->_path\""
		);
		$this->removeTmpFile();
	}

	/**
	 * Chemin vers le fichier temporaire contenant le mot de passe.
	 * @param bool $mysql Fichier pour mysql, sinon mysqldump
	 * @return string
	 */
	private function tmpPath(bool $mysql =true):string{
		return "/tmp/".getmypid()."_".(($mysql)?"mysql":"mysqldump")."_dump.cnf";
	}

	/**
	 * Crée un fichier temporaire contenant le mot de passe pour ne pas le passer en cli
	 * @param bool $mysql Crée le fichier pour mysql, sinon mysqldump
	 */
	private function createTmpFile(bool $mysql=true):void{
		$tmp = $this->tmpPath($mysql);
		if(!file_exists($tmp)){
			touch($tmp);
			chmod($tmp,0600);
		}
		$cmd = (($mysql)?"mysql":"mysqldump");
		file_put_contents($tmp,"[$cmd]\npassword=$this->_password");
	}

	/**
	 * Supprime le fichier temporaire créé pour le mot de passe.
	 * @param bool $mysql Supprime le fichier pour mysql, sinon mysqldump
	 */
	private function removeTmpFile(bool $mysql=true):void{
		unlink($this->tmpPath($mysql));
	}

	/**
	 * Supprime le backup courant
	 */
	public function remove(): void{
		if(file_exists($this->_path))
			$this->exec("rm -rf \"$this->_path\"");
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
		return $this->_path;
	}

	/**
	 * @param string $path nouvelle localisation du backup
	 */
	public function setLocation(string $path): void
	{
		$this->_path = $path;
	}
}