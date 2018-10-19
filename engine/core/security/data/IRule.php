<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 06/03/18
 * Time: 23:41
 */

namespace wfw\engine\core\security\data;

/**
 * Règle de validation de données.
 */
interface IRule
{
    /**
     * @param array $data Données auxquelles appliquer la règle.
     * @return IRuleReport
     */
    public function applyTo(array $data):IRuleReport;
}