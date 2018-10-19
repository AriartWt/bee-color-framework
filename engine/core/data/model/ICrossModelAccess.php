<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 13/06/18
 * Time: 16:53
 */

namespace wfw\engine\core\data\model;

/**
 * Permet d'envoyer des requêtes sur d'autres models.
 */
interface ICrossModelAccess {

    /**
     * Permet d'éxecuter une requete cross-models. Cette requête retourne une specification qui
     * sera appliquée à un subset de données ou à toutes les données du model à l'origine de la
     * requête.
     *
     * @param ICrossModelQuery $query Requête
     * @return CrossModelSpecification
     */
    public function execute(ICrossModelQuery $query): CrossModelSpecification;
}