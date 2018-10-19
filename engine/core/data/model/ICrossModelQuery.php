<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 13/06/18
 * Time: 16:54
 */

namespace wfw\engine\core\data\model;

/**
 * Requete cross-models
 */
interface ICrossModelQuery {
    /**
     * @return string Model concerné par la requête
     */
    public function getModel():string;

    /**
     * @return mixed Requête dont on souhaite obtenir le résultat
     */
    public function getQuery();

    /**
     * @param array $data Données reçue une fois execution de la requête.
     * @return CrossModelSpecification
     */
    public function createSpec(array $data):CrossModelSpecification;
}