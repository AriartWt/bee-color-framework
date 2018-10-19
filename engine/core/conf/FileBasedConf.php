<?php
namespace wfw\engine\core\conf;

use stdClass;

use wfw\engine\core\conf\io\adapters\JSONConfIOAdapter;
use wfw\engine\core\conf\io\IConfIOAdapter;

/**
 *  Classe de gestion des configurations de l'application.
 */
final class FileBasedConf extends AbstractConf{

	/**
	 *  Contient le chemin d'accés à la configuration du fichier
	 * @var string $_confPath
	 */
	protected $_confPath;

	/**
	 *  Contient la classe capable de lire un fichier de configurations
	 * @var IConfIOAdapter $_confIO
	 */
	private $_confIO;

	/**
	 *  Constructeur
	 *
	 * @param string                      $path   Configuration du fichier à initialiser
	 * @param IConfIOAdapter|null $reader Lecteur de fichiers de configuration
	 */
	public function __construct(string $path, IConfIOAdapter $reader=null){
		if(!is_null($reader)){
			$this->_confIO = $reader;
		}else{
			$this->_confIO = new JSONConfIOAdapter();
		}
		parent::__construct($this->loadConfFile($path));
		$this->_confPath=$path;
	}

	/**
	 *  Retourne le chemin d'accés au fichier de configurations
	 * @return string
	 */
	public function getConfPath():string{
		return $this->_confPath;
	}

	/**
	 *  Charge un fichier de configuration
	 *
	 * @param string                      $path    Chemind d'accés au fichier
	 * @param IConfIOAdapter|null $adapter Adapter (si non précisé, choisi l'adapter courant
	 *
	 * @return stdClass
	 */
	private function loadConfFile(string $path, IConfIOAdapter $adapter=null):stdClass{
		if(!is_null($adapter)){
			return $adapter->parse($path);
		}else{
			return $this->_confIO->parse($path);
		}
	}

	/**
	 *  Enregistre le fichier de configuration courant (écrase l'ancien)
	 */
	public function save():void{
		$this->_confIO->save($this);
	}
}