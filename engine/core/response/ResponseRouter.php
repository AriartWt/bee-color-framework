<?php
namespace wfw\engine\core\response;

use wfw\engine\core\action\IAction;
use wfw\engine\core\response\errors\InvalidResponseHandler;
use wfw\engine\core\response\errors\ResponseHandlerNotFound;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\package\general\handlers\response\AjaxHandler;
use wfw\engine\package\general\handlers\response\ErrorHandler;

/**
 * Route une réponse vers son handler
 */
final class ResponseRouter implements IResponseRouter {
	/** @var IResponseHandlerFactory $_factory */
	private $_factory;
	/** @var int $_foldingLimit */
	private $_foldingLimit;

	/**
	 * ActionResponseRouter constructor.
	 *
	 * @param IResponseHandlerFactory $factory Factory pour la création des ResponseHandlers
	 * @param int $foldingLimit (optionnel defaut : 5) Limite du nombre de sous
	 *                          repertoirs de recherche. Si la limite est atteinte, et que le
	 *                          handler n'est pas trouvé, l'exception HandlerNotFound sera levée.
	 */
	public function __construct(IResponseHandlerFactory $factory, int $foldingLimit = 5) {
		$this->_factory = $factory;
		$this->_foldingLimit = $foldingLimit;
	}

	/**
	 * @param IAction   $action   Action à l'origine de la réponse.
	 * @param IResponse $response Réponse à router.
	 * @return IResponseHandler Response handler destinataire de $response
	 */
	public function findResponseHandler(
		IAction $action,
		IResponse $response
	): IResponseHandler {
		if($response instanceof ErrorResponse && !$action->getRequest()->isAjax()){
			return $this->_factory->create(ErrorHandler::class);
		}else{
			if($action->getRequest()->isAjax()){
				return $this->_factory->create(AjaxHandler::class);
			}else{
				$path = explode('/',$action->getInternalPath());
				if(!is_null($package = array_shift($path))){
					$handlerClass = "package\\$package\\handlers\\response";
					$handlerFound = false;
					$folding = 0;
					while(!is_null($part = array_shift($path)) && $folding<$this->_foldingLimit){
						$handlerClass.="\\";
						$tmpName = ucfirst($part)."Handler";
						if(class_exists("wfw\\site\\".$handlerClass.$tmpName)){
							$handlerClass = "wfw\\site\\".$handlerClass.$tmpName;
							$handlerFound = true;
							break;
						}else if(class_exists("wfw\\engine\\".$handlerClass.$tmpName)){
							$handlerClass = "wfw\\engine\\".$handlerClass.$tmpName;
							$handlerFound = true;
							break;
						}else{
							$handlerClass.=$part;
						}
					}
					if($handlerFound){
						try{
							return $this->_factory->create(
								$handlerClass,
								$path
							);
						}catch(\InvalidArgumentException $e){
							throw new InvalidResponseHandler(
								"The given action cannot be resolved to a valid response handler : "
								.$action->getInternalPath()
							);
						}
					}else{
						throw new ResponseHandlerNotFound(
							"No handler found for action : ".$action->getInternalPath()
						);
					}
				}else{
					throw new ResponseHandlerNotFound(
						"No handler found for action : ".$action->getInternalPath()
					);
				}
			}
		}
	}
}