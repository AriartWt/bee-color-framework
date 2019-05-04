<?php
namespace wfw\daemons\sctl\conf;
use wfw\engine\core\conf\FileBasedConf;
use wfw\engine\core\conf\io\adapters\JSONConfIOAdapter;
use wfw\engine\lib\logger\ILogger;
use wfw\engine\lib\PHP\types\PHPString;

/**
 * Configurations du daemons sctl
 */
final class SCTLConf {
	/** @var FileBasedConf $_conf */
	private $_conf;
	/** @var string $_basepath */
	private $_basepath;
	/** @var null|string $_user */
	private $_user;
	/** @var string $_dir */
	private $_dir;
	/** @var string[] $_daemons */
	private $_daemons;

	/**
	 * BackupManagerConf constructor.
	 *
	 * @param string      $engineConf Chemin d'accés aux configurations par defaut
	 * @param null|string $siteConf   Chemin d'accés aux configurations du projet
	 * @param string      $basepath   Chemin de base pour la résolution de chemins relatifs
	 * @param null|string $user       Utilisateur propriétaire du fichier auth.pwd
	 * @param null|string $dir        Repertoir de travail recevant le fichier auth.pwd, sctl.pid,
	 *                                sem_file.semaphore
	 * @param string      ...$daemons Liste de daemons à gérer
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		string $engineConf,
		?string $siteConf,
		string $basepath = DAEMONS,
		?string $user = null,
		?string $dir = null,
		string ...$daemons
	){
		$this->_basepath = $basepath;
		$confIo = new JSONConfIOAdapter();
		$conf = new FileBasedConf($engineConf,$confIo);
		if(file_exists($siteConf))
			$conf->merge(new FileBasedConf($siteConf,$confIo));

		$sctlConf = $conf->get('server/daemons/sctl');
		if(is_null($sctlConf))
			throw new \InvalidArgumentException("Config key server/daemons/sctl not found");
		if(!(new PHPString($sctlConf))->startBy("/"))
			$sctlConf = "$basepath/$sctlConf";
		if(!file_exists($sctlConf))
			throw new \InvalidArgumentException("Conf file $sctlConf not found");
		$this->_conf = new FileBasedConf($sctlConf,$confIo);

		$this->_daemons = $this->_conf->getArray("daemons")??[];
		$this->_daemons = array_unique(array_merge($this->_daemons,$daemons,['sctl']));

		$this->_dir = $this->_conf->getString('working_dir');
		if(!is_null($dir)) $this->_dir = $dir;
		if(!(new PHPString($this->_dir))->startBy("/"))
			$this->_dir = "$basepath/$this->_dir";

		$this->_user = $this->_conf->getString('auth.pwd_owner');
		if(!is_null($user)) $this->_user = $user;
	}

	/**
	 * @return string Utilisateur propriétaire du fichier auth.pwd
	 */
	public function getUser():string{
		return $this->_user;
	}

	/**
	 * @return string Dossier de travail
	 */
	public function getWorkingDir():string{
		return $this->_dir;
	}

	/**
	 * @return array Liste des daemons à gérer
	 */
	public function getDaemons():array{
		return $this->_daemons;
	}

	/**
	 * @return int
	 */
	public function getLogLevel():int{
		return $this->_conf->getInt("logs/level") ?? ILogger::ERR;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	public function getLogFile(string $key):string{
		$dest = $this->_conf->getString("logs/files") ?? "sctl-$key.log";
		if(strpos($dest,"/")!==0) $dest = $this->getWorkingDir()."/$dest";
		return $dest;
	}
}