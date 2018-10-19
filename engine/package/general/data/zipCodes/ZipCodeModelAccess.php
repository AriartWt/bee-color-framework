<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/07/18
 * Time: 13:20
 */

namespace wfw\engine\package\general\data\zipCodes;

/**
 * Utilise un dossier contenant les fichiers de codes postaux à retenir sous la forme pays.php
 * retournant un tableau indexé par code postaux.
 */
final class ZipCodeModelAccess implements IZipCodeModelAccess{
	/** @var array $_loaded */
	private $_loaded;
	/** @var string $_folder */
	private $_folder;

	/**
	 * ZipCodeModelAccess constructor.
	 *
	 * @param string $folder
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $folder = __DIR__."/codes"){
		$this->_loaded = [];
		if(!is_dir($folder))
			throw new \InvalidArgumentException("$folder is not a valid directory !");
		$this->_folder = $folder;
	}

	/**
	 * @param string $country pays concerné
	 * @param string $zipCode Code postal
	 * @return string[] Liste des villes correspondant au code postal pour le pays choisi
	 */
	public function getCities(string $country, string $zipCode): array {
		$country = strtolower($country);
		if(!isset($this->_loaded[$country]))
			$this->_loaded[$country] = require "$this->_folder/$country.php";
		return $this->_loaded[$country][$zipCode] ?? [];
	}
}