<?php
namespace wfw\engine\core\app;

use wfw\engine\core\action\errors\ActionResolutionFailure;
use wfw\engine\core\notifier\Message;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\errors\ResponseResolutionFailure;
use wfw\engine\core\response\responses\StaticResponse;
use wfw\engine\core\app\context\IWebAppContext;
use wfw\engine\core\response\responses\Redirection;
use wfw\engine\core\view\ILayout;
use wfw\engine\lib\network\http\HTTPStatus;

/**
 * Application par défaut.
 */
final class WebApp {
	/** @var IWebAppContext $_context */
	private $_context;

	/**
	 * WebApp constructor.
	 *
	 * @param IWebAppContext $context Contexte de l'application.
	 */
	public function __construct(IWebAppContext $context){
		$this->_context = $context;
		$this->_context->getErrorHandler()->handle();
		$this->run();
	}

	private function run():void{
		$action = $this->_context->getAction();
		$permission = $this->_context->getAccessControlCenter()->checkPermissions($action);
		$response = $this->_context->getActionHook()->hook($action,$permission);
		if(is_null($response)){
			if($permission->isGranted()){
				try{
					$actionRouter = $this->_context->getActionRouter();
					$handler = $actionRouter->findActionHandler($action);
					$response = $handler->handle($action);
				}catch(ActionResolutionFailure $e){
					if($action->getRequest()->isAjax()){
						$response = new ErrorResponse(
							500,
							"No action handler found for ".$action->getInternalPath()
						);
					}else{
						$response = new StaticResponse($action);
					}
				}catch(\Error $e){
					$response = new ErrorResponse(
						500,
						"Internal error $e"
					);
				}
			}else{
				if(is_null($permission->getResponse())){
					if($action->getRequest()->isAjax()){
						$response = new ErrorResponse(
							100,
							'Access denied : you must be logged !'
						);
					}else{
						$this->_context->getNotifier()->addMessage(new Message(
							$permission->getCode()." : ".$permission->getMessage(),
							'error'
						));
						$response = $permission->getResponse() ?? new Redirection("users/login");
					}
				}else{
					$response = $permission->getResponse();
				}
			}
		}

		if($response instanceof Redirection){
			if($action->getRequest()->isAjax()){
				$response = new ErrorResponse(
					$response->getCode(),
					$permission->getMessage()
				);
			}else $this->redirect($response->getUrl(),$response->getCode());
		}

		$responseRouter = $this->_context->getResponseRouter();
		try{
			$handler = $responseRouter->findResponseHandler($action,$response);
		}catch(ResponseResolutionFailure $e){
			$response = new ErrorResponse(
				404,
				"Page not found : no response handler for ".$action->getInternalPath()
			);
			$handler = $responseRouter->findResponseHandler($action,$response);
		}

		$layout = $this->_context->getLayoutResolver()->resolve($action);
		$view = $handler->handleResponse($response);
		if(!($view instanceof ILayout)){
			$layout->setView($view);
		}else{
			$layout = $view;
		}
		$this->_context->getRenderer()->render($layout);
		if(!$action->getRequest()->isAjax()
			&& $this->_context->getConf()->getBoolean("server/display_loading_time")){
			echo "<div style=\"background-color:red;position:fixed;color:white;text-align:center;width:100%;bottom:0;\">Page générée en ".((microtime(1)-START_TIME)*1000)."ms</div>";
		}
		$this->_context->getSession()->set('previous_action',$action);
	}

	/**
	 * @param string   $url  URL de redirection
	 * @param int|null $code Code de redirection
	 */
	private function redirect(string $url,?int $code = null){
		if(HTTPStatus::existsValue($code)){
			http_response_code($code);
		}
		header("Location: ".$this->_context->getRouter()->url($url));
		exit(0);
	}
}