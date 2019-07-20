<?php
namespace wfw\cli\updator;

use wfw\cli\updator\conf\UpdatorConf;
use wfw\cli\updator\errors\UpdatorFailure;

/**
 * Execute les différent manoeuvre pour les mises à jour.
 */
final class Updator implements IUpdator {
	/** @var UpdatorConf $_conf */
	private $_conf;

	/**
	 * Updator constructor.
	 *
	 * @param UpdatorConf $conf Configurations
	 */
	public function __construct(UpdatorConf $conf) {
		$this->_conf = $conf;
	}

	/**
	 * Vérifie la disponibilité d'une mise à jour
	 *
	 * @return array Liste des mises à jour à installer
	 */
	public function check(): array {
		$crl = curl_init($this->_conf->getCheckUrl());
		curl_setopt($crl,CURLOPT_RETURNTRANSFER,true);
		$res = json_decode(curl_exec($crl),true)??[];
		curl_close($crl);
		$this->_conf->setLastCheck();
		return $res;
	}

	/**
	 * Telecharge les mises à jour disponibles
	 *
	 * @param null|string $dest Destination des mises à jour
	 * @return void
	 */
	public function download(?string $dest = null): void {
		$path = $this->_conf->getWorkingDir();
		if($dest) $path = $dest;
		if(is_dir("$path/updates")) $this->exec("rm -rf \"$path/updates\"");
		if(is_file("$path/updates.zip")) unlink("$path/updates.zip");
		$fp = fopen("$path/updates.zip",'w+');
		if($fp === false)
			throw new UpdatorFailure("Cann't open file $path/updates.zip in w+ mode !");
		$ch = curl_init($this->_conf->getDownloadUrl());

		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_exec($ch);

		if(curl_errno($ch)) throw new UpdatorFailure(curl_error($ch));

		$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if($statusCode !== 200)
			throw new UpdatorFailure(
				"Cannot download update from ".$this->_conf->getDownloadUrl()
				." server responded with $statusCode status code."
			);
	}

	/**
	 * @param null|string $source Dossier dans lequel se trouvent les mises à jour à installer.
	 */
	public function install(?string $source = null): void {
		$path = $this->_conf->getWorkingDir()."/updates.zip";
		if($source) $path = $source;
		if(!is_file($path)) throw new UpdatorFailure("$path : no such file or directory !");
		$this->exec("unzip \"$path\" -d \"".dirname($path).'"');
		$updatesFolder = str_replace('.zip','',$path);
		$updates = array_diff(scandir($updatesFolder), ['.','..']);
		natcasesort($updates);

		foreach($updates as $update){
			$this->installUpdate("$updatesFolder/$update");
		}
		$this->_conf->setLastUpdate(array_pop($updates));

		$this->exec('find '.$this->_conf->getWorkingDir().' -mindepth 1 -delete');
	}

	/**
	 * Crée un backup pour le projet courant
	 * @param string ...$opts options de backup (site dbs engine daemons cli
	 */
	protected function makeBackup(string ...$opts):void{
		if($this->_conf->allowBackups()){
			$this->exec("wfw ".$this->_conf->getProject()." backup -make ".implode(' ',$opts));
		}
	}

	/**
	 * Envoie une commande au gestionnaire de services wfw
	 * @param string $cmd
	 * @param string ...$daemons
	 */
	protected function sctl(string $cmd,string ...$daemons):void{
		if($this->_conf->allowSCTL()){
			if(!$this->_conf->hasGlobalSCTL())
				$this->exec("wfw ".$this->_conf->getProject()." service $cmd ".implode(' ',$daemons));
			else
				$this->exec("wfw self service $cmd ".implode(' ',$daemons));
		}
	}

	/**
	 * @param string $dir Dossier parent contenant les fichiers de la mise à jour.
	 */
	private function installUpdate(string $dir):void{
		$mainScript = "$dir/scripts/main.php";
		if(file_exists($mainScript)){
			try{
				require_once($mainScript);
			}catch(\Exception $e){
				error_log($e.PHP_EOL,3,$this->_conf->getWorkingDir()."/errors.log");
			}catch(\Error $e){
				error_log($e.PHP_EOL,3,$this->_conf->getWorkingDir()."/errors.log");
			}
		}
		$dirs = array_diff(scandir("$dir/src"),['.','..']);
		foreach ($dirs as $d){
			$this->exec("cp -R \"$dir/src/$d\" \"".dirname(__DIR__,2)."\"");
		}
	}

	/**
	 * @param string $cmd Commande à executer
	 * @throws BackupFailure
	 */
	private function exec(string $cmd):void{
		$outputs = []; $res = null;
		exec($cmd,$outputs,$res);
		if($res !== 0)
			throw new UpdatorFailure(
				"Error trying to exec '$cmd'".
				" code $res, outputs : ".implode("\n",$outputs)
			);
	}
}