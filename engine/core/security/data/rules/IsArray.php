<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 21/05/18
 * Time: 13:53
 */

namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Permet de savoir si la donnée est un tableau
 */
final class IsArray extends ForEachFieldRule
{
    /**
     * @param mixed $data Donnée sur laquelle appliquer la règle
     * @return bool
     */
    protected function applyOn($data): bool { return is_array($data); }
}