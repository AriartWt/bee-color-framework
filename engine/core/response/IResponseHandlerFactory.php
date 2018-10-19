<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/02/18
 * Time: 11:59
 */

namespace wfw\engine\core\response;

/**
 * Factory de ResponseHandler
 */
interface IResponseHandlerFactory
{
    /**
     * Crée un handler $class avec les paramètres $params
     * @param string $class  handler
     * @param array  $params Paramètres de construction du handler
     * @return IResponseHandler
     */
    public function create(string $class,array $params=[]):IResponseHandler;
}