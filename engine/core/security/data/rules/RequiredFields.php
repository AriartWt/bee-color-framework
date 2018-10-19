<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/03/18
 * Time: 02:32
 */

namespace wfw\engine\core\security\data\rules;

use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\RuleReport;

/**
 * Vérifie qu'une liste de champs est requise
 */
final class RequiredFields implements IRule
{
    /**
     * @var string $_message
     */
    private $_message;
    /**
     * @var string[] $_fields
     */
    private $_fields;
    /**
     * RequiredFields constructor.
     *
     * @param string   $message   Message en cas d'erreur
     * @param string[] ...$fields Liste des champs à surveiller
     */
    public function __construct(string $message,string ...$fields)
    {
        $this->_message = $message;
        $this->_fields = $fields;
    }

    /**
     * @param array $data Données auxquelles appliquer la règle.
     * @return IRuleReport
     */
    public function applyTo(array $data): IRuleReport
    {
        $errors = [];
        foreach ($this->_fields as $field) {
            if(!isset($data[$field])) $errors[$field] = $this->_message;
        }
        if(count($errors)>0){
            return new RuleReport(false,$errors);
        }else{
            return new RuleReport(true);
        }
    }
}