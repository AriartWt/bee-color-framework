<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 13/06/18
 * Time: 17:59
 */

namespace wfw\engine\core\data\model;

/**
 * Model sorter de base
 */
abstract class AbstractModelSorter implements IArraySorter {
    /**
     * @return string (représentation hexadécimale de la serialisation de l'instance courante)
     */
    public final function __toString()
    {
        return unpack("H*",serialize($this))[1];
    }
}