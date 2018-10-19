<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 07/12/17
 * Time: 06:31
 */

namespace wfw\engine\core\data\model;

/**
 *  Permet d'effectuer des recherches dans un repository
 */
interface IModelSearcher
{
    /**
     *  Effectue une recherche sur les objets contenus dans un repository
     *
     * @param mixed                  $request Requête
     * @param array                  $all     La totalité des objets du repository
     * @param array                  $indexed Indexes ([ "moreThanTen" => [11,12,13,...] ]
     * @param null|ICrossModelAccess $access  Acces cross-models pour les cross-models queries
     * @return array
     */
    public function search($request, array $all, array $indexed,?ICrossModelAccess $access = null):array;
}