<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 05/11/17
 * Time: 02:55
 */

namespace wfw\engine\core\data\specification;

/**
 *  Spécification à implémenter
 */
abstract class LeafSpecification extends AbstractCompositeSpecification
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     *  Verifie que le candidat correspond à la spécification
     *        TODO : décomenter lors du passage PHP 7.2
     *
     * @param mixed $candidate Candidat à la specification
     *
     * @return bool
     */
    //public abstract function isSatisfiedBy($candidate): bool;
}