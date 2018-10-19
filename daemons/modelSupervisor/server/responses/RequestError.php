<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 07/01/18
 * Time: 05:09
 */

namespace wfw\daemons\modelSupervisor\server\responses;

/**
 *  Une erreur est survenue pendant la requête
 */
class RequestError extends AbastractMSServerResponse
{
    /**
     * @var string $_error
     */
    private $_error;

    /** @var string $_errorClass */
    private $_errorClass;

    /**
     * RequestError constructor.
     *
     * @param \Exception $error Erreur
     */
    public function __construct(\Exception $error)
    {
        $this->_error = (string) $error;
        $this->_errorClass = get_class($error);
    }

    /**
     * Type d'exception.
     * @return string
     */
    public function getError():string{
        return $this->_error;
    }

    /**
     * @return string
     */
    public function getErrorClass():string{
        return $this->_errorClass;
    }

    /**
     *
     * @param string $class Classe à tester
     * @return bool
     */
    public function instanceOf(string $class):bool{
        return is_a($this->_errorClass,$class,true);
    }
}