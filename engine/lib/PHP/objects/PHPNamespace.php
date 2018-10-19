<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 13/10/17
 * Time: 09:36
 */

namespace wfw\engine\lib\PHP\objects;

use wfw\engine\lib\PHP\types\Type;

/**
 *  Permet des opérations sur les namespaces
 */
class PHPNamespace
{
    /**
     *  Namespace complet
     * @var string $_namespace
     */
    protected $_namespace;
    /**
     *  Namespace sous forme de tableau
     * @var array
     */
    protected $_exploded;

    /**
     *  Constructor.
     *
     * @param mixed $namespace namespace
     */
    public function __construct($namespace){
        if(is_string($namespace)){
            $this->_namespace = $namespace;
            $this->_exploded = explode("\\",$namespace);
        }else if(is_array($namespace)){
            $this->_namespace=implode("\\",$namespace);
            $this->_exploded = $namespace;
        }else{
            throw new \InvalidArgumentException("Array or string but ".(new Type($namespace))->get()." given");
        }
    }

    /**
     *  Converti le namespace en tableau
     * @return array
     */
    public function toArray():array{
        return $this->_exploded;
    }

    /**
     *  To string
     * @return string
     */
    public function __toString()
    {
        return $this->_namespace;
    }

    /**
     *  Permet de savoir si le namespace est valide
     * @return bool
     */
    public function isValide():bool{
        return preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*$/",$this->_namespace);
    }
}