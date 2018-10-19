<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 13/06/18
 * Time: 21:14
 */

namespace wfw\engine\core\data\model;

/**
 * Permet de comparer deux ModelObjects
 */
interface IComparable {
    /**
     * @param IModelObject $o
     * @return int 1 si l'objet courant est plus grand que $0, -1 sinon. 0 s'ils sont égaux.
     */
    public function compareTo(IModelObject $o):int;
}