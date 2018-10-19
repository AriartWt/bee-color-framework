<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/02/18
 * Time: 10:03
 */

namespace wfw\engine\package\general\handlers\response;

use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\IResponseHandler;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\view\IView;
use wfw\engine\package\general\views\error\Error;

/**
 * Handler de réponses
 */
final class ErrorHandler implements IResponseHandler
{
    /**
     * @var null|string $_ajaxViewPath
     */
    private $_errorViewPath;

    /**
     * AjaxHandler constructor.
     *
     * @param null|string $errorViewPath Chemin d'accés à la vue de rendu de l'erreur.
     */
    public function __construct(?string $errorViewPath = null)
    {
        $this->_errorViewPath = $errorViewPath;
    }

    /**
     * @param IResponse $response Réponse créer par l'ActionHandler
     * @return IView Vue à retourner au client
     */
    public function handleResponse(IResponse $response): IView
    {
        $code = null;
        $message = "";
        if($response instanceof ErrorResponse){
            $code = $response->getCode();
            $message = $response->getMessage();
        }else{
            try{
                $message = (string) $response->getData();
            }catch(\Error $e){
                $message = $e;
            }
        }
        return new Error($message,$code,$this->_errorViewPath);
    }
}