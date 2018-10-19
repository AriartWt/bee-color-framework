<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 21/05/18
 * Time: 13:55
 */

namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Permet de savoir si tous les éléments d'un tableau passer un certain filtre
 */
final class IsArrayOf extends ForEachFieldRule
{
    /**
     * @var callable $_filter
     */
    private $_filter;

    /**
     * IsArrayOf constructor.
     *
     * @param string   $message
     * @param callable $filter
     * @param string   ...$fields
     */
    public function __construct(string $message,callable $filter, string... $fields) {
        parent::__construct($message, ...$fields);
        $this->_filter = $filter;
    }

    /**
     * @param mixed $data Donnée sur laquelle appliquer la règle
     * @return bool
     */
    protected function applyOn($data): bool
    {
        if(!is_array($data)) return false;
        foreach($data as $d){if(!call_user_func($this->_filter,$d))return false;}
        return true;
    }
}