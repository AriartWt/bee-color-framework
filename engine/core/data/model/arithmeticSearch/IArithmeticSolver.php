<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 13/06/18
 * Time: 16:36
 */

namespace wfw\engine\core\data\model\arithmeticSearch;

use wfw\engine\core\data\model\ICrossModelAccess;

/**
 * Permet de résoudre une expression arithmetique.
 */
interface IArithmeticSolver {
    /**
     * @param string                 $expr    Expression à parser
     * @param array                  $all     Liste de tous les éléments
     * @param array                  $indexes Liste des indexes
     * @param null|ICrossModelAccess $access  Acces cross-models pour les cross-models query
     * @return array
     */
    public function solve(string $expr, array $all, array $indexes,?ICrossModelAccess $access = null):array;
}