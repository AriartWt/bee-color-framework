<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 05/12/17
 * Time: 05:47
 */

namespace wfw\engine\core\data\specification;

/**
 *  Spécification de base
 */
abstract class Specification implements ISpecification
{
    /**
     * @return string (représentation hexadécimale de la serialisation de l'instance courante)
     */
    public final function __toString()
    {
        return unpack("H*",serialize($this))[1];
    }
}