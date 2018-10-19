<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/02/18
 * Time: 13:51
 */

namespace wfw\engine\core\security;

/**
 * Factory d'AccessRule
 */
interface IAccessRuleFactory
{
    /**
     * Créer une AccessRule
     * @param string $ruleClass Classe de l'AccessRule à créer
     * @param array  $params    Paramètres de construction
     * @return IAccessRule
     */
    public function create(string $ruleClass,array $params=[]):IAccessRule;
}