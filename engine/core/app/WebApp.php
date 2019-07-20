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
		$this->run();
	}

	private function run():void{
		$action = $this->_context->getAction();
		$session = $this->_context->getSession();
		$session->start();
		$cache = $this->_context->getCacheSystem();
		$cacheKey = $this->_context::CACHE_KEYS[$this->_context::VIEWS]
					."/".$action->getLang()."::".$action->getRequest()->getURI();

		if( !$session->isLogged()
			&& $action->getRequest()->getMethod() === "GET"
			&& $cache->contains($cacheKey)
		) $layout = $cache->get($cacheKey);
		else{
			$translator = $this->_context->getTranslator();
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
								$translator->getTranslateAndReplace(
									"server/engine/core/app/ACTION_HANDLER_NOT_FOUND",
									null,
									$action->getInternalPath()
								)
							);
						}else{
							$response = new StaticResponse($action);
						}
					}catch(\Error $e){
						$response = new ErrorResponse(
							500,
							$translator->getTranslateAndReplace(
								"server/engine/core/app/INTERNAL_ERROR",
								null,
								$e
							)
						);
					}
				}else{
					if(is_null($permission->getResponse())){
						if($action->getRequest()->isAjax()){
							$response = new ErrorResponse(
								100,
								$translator->getAndTranslate(
									"server/engine/core/app/MUST_BE_LOGGED"
								)
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
					$translator->getTranslateAndReplace(
						"server/engine/core/app/404_NOT_FOUND",
						null,
						$action->getInternalPath()
					)
				);
				$handler = $responseRouter->findResponseHandler($action,$response);
			}

			$layout = $this->_context->getLayoutResolver()->resolve($action);
			$view = $handler->handleResponse($response);
			if(!($view instanceof ILayout)) $layout->setView($view);
			else $layout = $view;

			if($layout->allowCache() && !$session->isLogged())
				$cache->set($cacheKey,$layout);
		}
		//caching layout
		$this->_context->getRenderer()->render($layout);
		if(!$action->getRequest()->isAjax()
			&& $this->_context->getConf()->getBoolean("server/display_loading_time")){
			echo "<div style=\"background-color:red;position:fixed;color:white;text-align:center;"
				."width:100%;bottom:0;\">Generated in ".((microtime(1)-START_TIME)*1000)
				."ms</div>";
		}
		if(in_array(http_response_code(),[
			HTTPStatus::OK, HTTPStatus::ACCEPTED, HTTPStatus::CREATED, HTTPStatus::MOVED_PERMANENTLY,
			HTTPStatus::PERMANENT_REDIRECT, HTTPStatus::TEMPORARY_REDIRECT
		])) $session->set('previous_action',$action);

		$this->_context->close();
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