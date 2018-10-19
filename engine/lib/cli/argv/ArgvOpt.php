<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/12/17
 * Time: 08:48
 */

namespace wfw\engine\lib\cli\argv;
use wfw\engine\lib\PHP\types\PHPString;

/**
 * Class ArgvOpt
 *
 * @package wfw\engine\lib\cli\argv
 */
class ArgvOpt
{
    /**
     * @var string
     */
    private $_name;
    /**
     * @var string
     */
    private $_description;
    /**
     * @var int|null
     */
    private $_length;
    /**
     * @var callable
     */
    private $_validator;
    /**
     * @var bool
     */
    private $_optionnal;
    /**
     * @var string
     */
    private $_validationFailMessage;

    /**
     *  ArgvOpt constructor.
     *
     * @param string        $name                   Nom de l'argument
     * @param string        $description            Description de l'argument
     * @param int|null      $length                 (optionnel défaut : 0) Nombre d'arguments attendus. Si null, nombre d'arguments variable.
     * @param callable|null $validator              Validateur de l'argument
     * @param bool          $optionnal              Argument optionnel ou non
     * @param null|string   $validatorFailMessage   (optionnel) Message du validateur en cas d'echec
     */
    public function __construct(string $name, string $description, ?int $length=0, ?callable $validator=null, bool $optionnal = false, ?string $validatorFailMessage=null)
    {
        $this->_name = $name;
        $this->_length = $length;
        $this->_description = $description;
        $this->_validator = $validator??function(){
            return true;
        };
        $this->_optionnal = $optionnal;
        $this->_validationFailMessage = $validatorFailMessage ?? "The given argument doesn't match the $name's validator";
    }

    /**
     * @return bool
     */
    public function isOptionnal():bool{
        return $this->_optionnal;
    }

    /**
     *  Retourne le nombre de valeurs attendues
     * @return int|null
     */
    public function getLength():?int{
        return $this->_length;
    }

    /**
     *  Valide les données
     * @param string $value Valeur à tester
     *
     * @return bool
     */
    public function validates(string $value):bool{
        return call_user_func($this->_validator,$value);
    }

    /**
     * @return string
     */
    public function getName():string{
        return $this->_name;
    }

    /**
     * @return string
     */
    public function getDescription():string{
        return $this->_description;
    }

    /**
     * @param array $replacements Remplacements. 0 : argument , 1 : nom de l'option, 2 : longueur attendue, 3 : description
     *
     * @return string
     */
    public function getValidatorFailMessage(array $replacements):string{
        $message = new PHPString($this->_validationFailMessage);
        foreach($replacements as $k=>$v){
            $message = $message->replaceFirst("[$$k]",$v??'');
        }
        return "\n\n$message\n\n";
    }
}