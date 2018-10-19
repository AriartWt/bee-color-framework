<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/03/18
 * Time: 02:08
 */

namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Vérifie si un champ est un email valide
 */
final class IsEmail extends ForEachFieldRule
{
    /**
     * @param mixed $data Donnée sur laquelle appliquer la règle
     * @return bool
     */
    protected function applyOn($data): bool
    {
        return !is_null(filter_var($data,FILTER_VALIDATE_EMAIL,FILTER_NULL_ON_FAILURE));
    }
}