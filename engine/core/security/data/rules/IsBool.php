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
final class IsBool extends ForEachFieldRule
{
    /**
     * @param mixed $data Donnée sur laquelle appliquer la règle
     * @return bool
     */
    protected function applyOn($data): bool
    {
        return !is_null(filter_var($data,FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE));
    }
}