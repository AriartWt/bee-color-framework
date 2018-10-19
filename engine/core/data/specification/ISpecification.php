<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 05/11/17
 * Time: 02:07
 */

namespace wfw\engine\core\data\specification;

/**
 *  Spécification de base
 */
interface ISpecification
{
    /**
     *  Verifie que le candidat correspond à la spécification
     *
     * @param mixed $candidate Candidat à la specification
     *
     * @return bool
     */
    public function isSatisfiedBy($candidate):bool;

    /**
     *  Combine la specification courante avec la specification $spec sous la forme d'un OU logique
     * @param ISpecification[] ...$specs Specification à combiner
     *
     * @return ISpecification
     */
    public function or(ISpecification ...$specs):ISpecification;

    /**
     *  Combine la specification courante avec la spécification $spec sous la forme d'un ET logique
     *
     * @param ISpecification[] ...$specs
     *
     * @return ISpecification
     */
    public function and(ISpecification ...$specs):ISpecification;

    /**
     *  Retourne l'inverse de la specification courante.
     *
     * @return mixed
     */
    public function not();
}