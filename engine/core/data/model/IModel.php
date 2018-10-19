<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 12/12/17
 * Time: 07:43
 */

namespace wfw\engine\core\data\model;

use wfw\engine\core\data\specification\ISpecification;

/**
 *  Modele de lecture des données
 */
interface IModel
{
    /**
     *  Renvoie tous les objets du repository qui correspondent à la requete
     * @param mixed                  $search Recherche
     * @param ICrossModelAccess|null $access (optionnel) Acces cross-models
     * @return array
     */
    public function find($search,?ICrossModelAccess $access = null): array;

    /**
     * @return ISpecification[]
     */
    public function getIndexes():array;

    /**
     * @return array
     */
    public function getPopulatedIndexes():array;

    /**
     *  Crée un index sur le critère $spec lors de l'ajout d'un nouvel objet. Trie les aggrégat déjà présent pour
     *        rechercher ceux qui correspondent à l'index.
     * @param string                 $key  Clé de l'index
     * @param ISpecification $spec Spec permettant de tester l'objet.
     */
    public function createIndex(string $key, ISpecification $spec);

    /**
     *  Supprime un index du repository
     * @param string $key Index à supprimer
     */
    public function removeIndex(string $key);

    /**
     *  Teste l'existence d'un index
     *
     * @param string $key Indexe à tester
     *
     * @return bool
     */
    public function existsIndex(string $key):bool;

    /**
     * @return int Nombre d'éléments contenus dans le model.
     */
    public function getLength():int;
}