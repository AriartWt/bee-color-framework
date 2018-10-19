<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 05/11/17
 * Time: 02:30
 */

namespace wfw\engine\core\data\specification;

/**
 *  Abastraction d'un composite de specifications
 */
abstract class AbstractCompositeSpecification extends Specification
{
    /**
     *  Liste des specifications
     * @var ISpecification[] $_specs
     */
    protected $_specs;

    /**
     *  AbstractCompositeSpecification constructor.
     *
     * @param ISpecification[] ...$specs Liste des specifications
     */
    public function __construct(ISpecification ...$specs)
    {
        $this->_specs = $specs;
    }

    /**
     *  Verifie que le candidat correspond à la spécification
     *
     * @param mixed $candidate Candidat à la specification
     *
     * @return bool
     */
    public abstract function isSatisfiedBy($candidate): bool;

    /**
     *  Combine la specification courante avec la specification $spec sous la forme d'un OU logique
     *
     * @param ISpecification[] $specs Specification à combiner
     *
     * @return ISpecification
     */
    public function or (ISpecification ...$specs): ISpecification
    {
        return new OrSpecification(...array_merge([$this],$specs));
    }

    /**
     *  Combine la specification courante avec la spécification $spec sous la forme d'un ET logique
     *
     * @param ISpecification[] $specs Specification à combiner
     *
     * @return ISpecification
     */
    public function and (ISpecification ...$specs): ISpecification
    {
        return new AndSpecification(...array_merge([$this],$specs));
    }

    /**
     *  Retourne l'inverse de la specification courante.
     *
     * @return mixed
     */
    public function not()
    {
        return new NotSpecification($this);
    }
}