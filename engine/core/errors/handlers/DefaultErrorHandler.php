<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 11/02/18
 * Time: 10:33
 */

namespace wfw\engine\core\errors\handlers;

use ErrorException;
use wfw\engine\core\errors\IErrorHandler;

/**
 * Gestionnaire d'erreurs par défaut.
 */
final class DefaultErrorHandler implements IErrorHandler
{
    /** @var bool $_turnWarningIntoException */
    private $_turnWarningIntoException;

    /**
     * DefaultErrorHandler constructor.
     *
     * @param bool $turnWarningsIntoException Si true, transforme tous les warning en exceptions.
     */
    public function __construct($turnWarningsIntoException=true)
    {
        $this->_turnWarningIntoException = $turnWarningsIntoException;
    }

    /**
     *  Transforme les erreur en exceptions
     *
     * @param string $errno      Numéro d'erreur
     * @param string $errstr     Message de l'erreur
     * @param string $errfile    Fichier source de l'erreur
     * @param string $errline    Ligne de l'erreur
     * @param array  $errcontext Contexte de l'erreur
     *
     * @return bool
     * @throws ErrorException
     */
    public function php_warning_error_to_exception(string $errno, string $errstr, string $errfile, string $errline, array $errcontext) {
        if((int) $errno === E_WARNING && !$this->_turnWarningIntoException) return false;
        throw new ErrorException(str_replace("\n","<\br>",$errstr), 0, $errno, $errfile, $errline);
    }

    /**
     *  Initialise les différents handlers
     */
    public function handle(): void
    {
        set_error_handler(array($this,"php_warning_error_to_exception"));
    }
}