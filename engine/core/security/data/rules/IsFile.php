<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/05/18
 * Time: 11:09
 */

namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Vérifie si le fichier existe et s'il est conforme aux spécifications
 */
final class IsFile extends ForEachFieldRule
{
	/** @var int $_maxSize */
	private $_maxSize;
	/** @var array $_acceptedMimes */
	private $_acceptedMimes;

	/**
	 * IsFile constructor.
	 *
	 * @param string $message       Message en cas d'echec
	 * @param int    $maxSize       Taille maximum du fichier (en octets) (-1 = pas de limite)
	 * @param array  $acceptedMimes Liste de regexp de validation de type mimes.
	 * @param string ...$fields     Liste des champs concernés par la régle
	 */
	public function __construct(
		string $message,
		int $maxSize = -1,
		$acceptedMimes = [],
		string ...$fields
	){
		parent::__construct($message, ...$fields);
		$this->_maxSize = $maxSize;
		$this->_acceptedMimes = $acceptedMimes;
	}

	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool
	{
		if(!is_array($data)) return false;
		if(!isset($data["tmp_name"])) return false;
		if(!file_exists($data["tmp_name"])) return false;
		if($this->_maxSize >= 0){
			if($this->_maxSize < filesize($data["tmp_name"])) return false;
		}
		$mime = mime_content_type($data["tmp_name"]);
		foreach($this->_acceptedMimes as $rule){
			if(preg_match($rule,$mime)) return true;
		}
		return false;
	}
}