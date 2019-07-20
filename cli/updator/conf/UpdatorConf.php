<?php
namespace wfw\cli\updator\conf;
use stdClass;
use wfw\engine\core\conf\FileBasedConf;
use wfw\engine\core\conf\io\adapters\JSONConfIOAdapter;
use wfw\engine\core\conf\MemoryConf;
use wfw\engine\lib\PHP\types\PHPString;


/**
 * COnfiguration de l'updator
 */
final class UpdatorConf {
	private const WORKING_DIR = "working_dir";
	private const SERVER = "server/addr";
	private const CHECK = "server/check";
	private const DOWNLOAD = "server/download";
	private const SCTL = "permissions/allow_sctl";
	private const BACKUPS = "permissions/allow_backups";
	private const GLOBAL_SCTL = "global_sctl";
	private const PROJECT = "project";

	/** @var FileBasedConf $_conf */
	private $_conf;
	/** @var string $_basepath */
	private $_basepath;
	/** @var FileBasedConf $_engineConf */
	private $_engineConf;
	/** @var FileBasedConf $_siteConf */
	private $_siteConf;

	/**
	 * BackupManagerConf constructor.
	 *
	 * @param string      $engineConf Chemin d'accés aux configurations par defaut
	 * @param null|string $siteConf   Chemin d'accés aux configurations du projet
	 * @param string      $basepath   Chemin de base pour la résolution de chemins relatifs
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $engineConf,?string $siteConf, ?string $basepath = null) {
		if(is_null($basepath)) $basepath = dirname(__DIR__,2);
		$this->_basepath = $basepath;
		$confIo = new JSONConfIOAdapter();
		$this->_engineConf = new FileBasedConf($engineConf,$confIo);

		$conf = new MemoryConf(new stdClass());
		$conf->merge($this->_engineConf);
		if(file_exists($siteConf)){
			$this->_siteConf = new FileBasedConf($siteConf,$confIo);
			$conf->merge($this->_siteConf);
		}

		$backupConf = $conf->get('server/cli/updator');
		if(is_null($backupConf))
			throw new \InvalidArgumentException("Config key server/cli/updator not found");
		if(!(new PHPString($backupConf))->startBy("/"))
			$backupConf = "$basepath/$backupConf";
		if(!file_exists($backupConf))
			throw new \InvalidArgumentException("Conf file $backupConf not found");
		$this->_conf = new FileBasedConf($backupConf,$confIo);
	}

	/**
	 * @return string Dossier de travail par défaut
	 */
	public function getWorkingDir():string{
		$path = $this->_conf->getString(self::WORKING_DIR);
		if(!(new PHPString($path))->startBy('/'))
			$path = "$this->_basepath/$path";
		return $path;
	}

	/**
	 * @return string retourne l'adresse du serveur de mise à jour
	 */
	public function getUpdateServer():string{
		return $this->_conf->getString(self::SERVER);
	}

	/**
	 * @return string
	 */
	public function getCheckUrl():string{
		return $this->getUpdateServer().$this->_conf->getString(self::CHECK);
	}
	/**
	 * @return string
	 */
	public function getDownloadUrl():string{
		return $this->getUpdateServer().$this->_conf->getString(self::DOWNLOAD)
			."?current=".urlencode($this->_engineConf->getString("server/framework/version"));
	}

	/**
	 * Met à jour la date de derniere verification dans le fichier de configuration engine.
	 */
	public function setLastCheck():void{
		$this->_engineConf->set("server/framework/last_check",microtime(true));
		$this->_engineConf->save();
	}

	/**
	 * Met à jour la date de derniere mise à jour et la version du framework dans le fichier
	 * engine.
	 * @param string $version Derniéer version installée
	 */
	public function setLastUpdate(string $version):void{
		$this->_engineConf->set("server/framework/last_update",microtime(true));
		$this->_engineConf->set("server/framework/version",$version);
		$this->_engineConf->save();
	}

	/**
	 * Permet de savoir si ce projet authorise le contrôle de ses services par l'assistant de mise
	 * à jour.
	 * @return bool
	 */
	public function allowSCTL():bool{
		return $this->_conf->getBoolean(self::SCTL);
	}

	/**
	 * Permet de savoir si le systeme courant dispose d'un systeme de gestion de services global
	 * @return bool
	 */
	public function hasGlobalSCTL():bool{
		return $this->_conf->getBoolean(self::GLOBAL_SCTL);
	}

	/**
	 * Permet de savoir si ce projet authorise la création de backups par l'assistant de mises
	 * à jour
	 * @return bool
	 */
	public function allowBackups():bool{
		return $this->_conf->getBoolean(self::BACKUPS);
	}

	/**
	 * Retourne le nom du projet utilisé pour les commandes sur wfw.
	 * @return string
	 */
	public function getProject():string{
		return $this->_conf->getString(self::PROJECT);
	}
}