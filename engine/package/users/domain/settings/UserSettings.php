<?php
namespace wfw\engine\package\users\domain\settings;

use stdClass;
use wfw\engine\core\conf\FileBasedConf;
use wfw\engine\core\conf\IConf;

/**
 *  Décorateur de classe conf n'exposant que la partie lecture et les fonctions set/merge
 */
abstract class UserSettings {
	/** @var FileBasedConf $_conf */
	private $_conf;

	/**
	 * UserSettings constructor.
	 *
	 * @param FileBasedConf $conf
	 */
	public function __construct(FileBasedConf $conf) {
		$this->_conf = $conf;
	}

	/**
	 *  Retourne l'objet stdClass contenant les configurations
	 * @return stdClass
	 */
	public function getRaw():stdClass{
		return $this->_conf->getRawConf();
	}

	/**
	 *  Retourne une configuration
	 *
	 * @param string $path Clé d'accés à la valeur de la configuration
	 *
	 * @return mixed
	 */
	public function get(string $path){
		return $this->_conf->get($path);
	}

	/**
	 *  Renvoie true si la clé de configuratione existe, false sinon
	 * @param string $key Clé à tester
	 *
	 * @return bool
	 */
	public function existsKey(string $key):bool{
		return $this->_conf->existsKey($key);
	}

	/**
	 *  Supprime une clé de configuration
	 *
	 * @param string $key Clé à supprimer
	 */
	public function removeKey(string $key){
		$this->_conf->removeKey($key);
	}

	/**
	 *  Retourne une clé de configuration booléenne
	 *
	 * @param string $key Clé
	 *
	 * @return bool|null
	 */
	public function getBoolean(string $key):?bool{
		return $this->_conf->getBoolean($key);
	}

	/**
	 *  Retourne une clé de configuration entière
	 *
	 * @param string $key Clé
	 *
	 * @return int|null
	 */
	public function getInt(string $key):?int{
		return $this->_conf->getInt($key);
	}

	/**
	 *  Retourne une clé de configuration chaine de cractère
	 * @param string $key Clé
	 *
	 * @return null|string
	 */
	public function getString(string $key):?string{
		return $this->_conf->getString($key);
	}

	/**
	 *  Retourne la valeur d'un clé de configuration float
	 * @param string $key Clé
	 *
	 * @return float|null
	 */
	public function getFloat(string $key):?float{
		return $this->_conf->getFloat($key);
	}

	/**
	 *  Retourne la valeur d'une clé de configuration stdClass
	 *
	 * @param string $key Clé
	 *
	 * @return null|stdClass
	 * @throws InvalidTypeException
	 */
	public function getObject(string $key):?stdClass{
		return $this->_conf->getObject($key);
	}

	/**
	 *  Retourne un tableau
	 *
	 * @param string $key
	 *
	 * @return array|null
	 */
	public function getArray(string $key):?array{
		return $this->_conf->getArray($key);
	}

	/**
	 *  Modifie une clé de configuration et l'enregistre
	 * @param string $path  Clé de configuration à modifier
	 * @param mixed  $value Nouvelle valeur de la clé de configuration
	 */
	public function set(string $path, $value):void{
		$this->_conf->set($path,$value);
	}
	/**
	 *  Intégre (fusionne) la configuration passée en paramètre avec la configuration courante
	 *
	 * @param IConf $conf Configuration à intégrer
	 */
	public function merge(IConf $conf):void{
		$this->_conf->merge($conf);
	}
}