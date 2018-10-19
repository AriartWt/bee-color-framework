<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/07/18
 * Time: 13:18
 */

namespace wfw\engine\package\general\data\zipCodes;

/**
 * Accés au model
 */
interface IZipCodeModelAccess {
	/**
	 * @param string $country pays concerné
	 * @param string $zipCode Code postal
	 * @return string[] Liste des villes correspondant au code postal pour le pays choisi
	 */
	public function getCities(string $country,string $zipCode):array;
}