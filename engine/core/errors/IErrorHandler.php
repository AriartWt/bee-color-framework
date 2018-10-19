<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 11/02/18
 * Time: 10:33
 */

namespace wfw\engine\core\errors;

/**
 * Gestionnaire d'erreurs.
 */
interface IErrorHandler
{
    /**
     *  Initialise les différents handlers
     */
    public function handle():void;
}