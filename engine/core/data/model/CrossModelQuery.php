<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 13/06/18
 * Time: 17:20
 */

namespace wfw\engine\core\data\model;

/**
 * Class CrossModelQuery
 *
 * @package wfw\engine\core\data\model
 */
final class CrossModelQuery implements ICrossModelQuery {
    /** @var string $_model */
    private $_model;
    /** @var mixed $_search */
    private $_search;
    /** @var string $_resultSpecClass */
    private $_resultSpecClass;

    /**
     * CrossModelQuery constructor.
     *
     * @param string $model
     * @param        $search
     * @param string $resultSpecClass
     * @throws \InvalidArgumentException
     */
    public function __construct(string $model, $search, string $resultSpecClass) {
        if(!is_a($model,IModel::class,true))
            throw new \InvalidArgumentException("$model doesn't implements ".IModel::class);
        if(!is_a($resultSpecClass,CrossModelSpecification::class,true))
            throw new \InvalidArgumentException(
                "$resultSpecClass doesn't implements ".CrossModelSpecification::class
            );
        $this->_model = $model;
        $this->_search = $search;
        $this->_resultSpecClass = $resultSpecClass;
    }

    /**
     * @return string Model concerné par la requête
     */
    public function getModel(): string {
        return $this->_model;
    }

    /**
     * @return string Requête dont on souhaite obtenir le résultat
     */
    public function getQuery() {
        return $this->_search;
    }

    /**
     * @param array $data Données reçue une fois execution de la requête.
     * @return CrossModelSpecification
     */
    public function createSpec(array $data): CrossModelSpecification {
        return new $this->_resultSpecClass($data);
    }

    /**
     * @return string (représentation hexadécimale de la serialisation de l'instance courante)
     */
    public final function __toString() {
        return unpack("H*",serialize($this))[1];
    }
}