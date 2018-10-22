<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/03/18
 * Time: 02:25
 */

namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Verifie si une donnée peut-être interprêtée comme un booléen
 */
final class IsBool extends ForEachFieldRule {
	/** @var null|bool $_requireValue */
	private $_requireValue = null;

	/**
	 * @param bool|null $val Valeur requise. Si null, aucune valeur particulière n'est requise
	 * @return IsBool
	 */
	public function requireValue(?bool $val):IsBool{
		$this->_requireValue = $val;
		return $this;
	}
    /**
     * @param mixed $data Donnée sur laquelle appliquer la règle
     * @return bool
     */
    protected function applyOn($data): bool {
    	$parse = !is_null($v = filter_var($data,FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE));
	    if(!is_null($this->_requireValue) && !is_null($parse)) return $v === $this->_requireValue;
	    else return $parse;
    }
}