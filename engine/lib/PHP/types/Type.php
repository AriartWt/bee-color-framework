<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 13/10/17
 * Time: 15:33
 */

namespace wfw\engine\lib\PHP\types;

use ReflectionClass;

/**
 *  Permet de faire des opérations sur les types
 */
class Type
{
    /**
     * @var mixed $_var
     */
    protected $_var;

    /**
     * @var string $_type
     */
    protected $_type;

    /**
     *  Constructor.
     *
     * @param mixed $var    Variable dont ont souhaite connaitre le type
     * @param bool  $isType
     */
    public function __construct($var,bool $isType=false)
    {
        if($isType){
            $this->_type = $var;
        }else{
            $this->_var = $var;
        }
    }

    /**
     *  Retourne le type de la variable courante
     * @return string
     */
    public function get():string{
        if($this->_type){
            return $this->_type;
        }
        if(is_object($this->_var)){
            return get_class($this->_var);
        }else{
            return gettype($this->_var);
        }
    }

    /**
     *  Permet de savoir si la variable courante est exactement d'un certain type
     *
     * @param string $type Type à tester
     *
     * @return bool
     */
    public function isA(string $type):bool{
        return $this->get() == $type;
    }

    /**
     *   Permet de savoir si une classe étend une autre classe ou implémente une interface
     *
     * @param  string    $test  Classe étendue ou interface implémentée
     *
     * @return boolean 			True si $class implémente ou hérite de la classe $test
     */
    public function extendsOrImplements(string $test):bool{
        $class = $this->get();
        $def=array("boolean",
            "integer",
            "double",
            "string",
            "array",
            "object",
            "resource",
            "NULL",
            "unknown type");
        if(in_array($class,$def) || in_array($test,$def)){
            return $class==$test;
        }
        $reflected= new ReflectionClass($class);
        if($reflected){
            while($reflected){
                $name=$reflected->getName();
                if($test == $name){
                    return true;
                }
                $interfaces = $reflected->getInterfaces();
                if(is_array($interfaces)){
                    foreach($interfaces as $interface){
                        if($interface->getName() == $test){
                            return true;
                        }
                    }
                }
                $reflected=$reflected->getParentClass();
            }
            return false;
        }else{
            return false;
        }
    }
}