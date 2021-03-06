<?php
namespace wfw\engine\core\app;

use wfw\engine\core\action\errors\ActionHandlerNotEnabled;
use wfw\engine\core\action\errors\ActionResolutionFailure;
use wfw\engine\core\notifier\Message;
use wfw\engine\core\response\errors\ResponseHandlerNotEnabled;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\errors\ResponseResolutionFailure;
use wfw\engine\core\response\responses\FileResponse;
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
		$cacheKey = $this->_context::VIEWS."/".$action->getLang()."::".$action->getRequest()->getURI();

		if( !$session->isLogged()
			&& $action->getRequest()->getMethod() === "GET"
			&& $cache->contains($cacheKey)
		) $layout = $cache->get($cacheKey);
		else{
			$translator = $this->_context->getTranslator();
			$permission = $this->_context->getAccessControlCenter()->checkAccessPermission($action);
			$response = $this->_context->getActionHook()->hook($action,$permission);
			if(is_null($response)){
				if($permission->isGranted()){
					try{
						$actionRouter = $this->_context->getActionRouter();
						$handler = $actionRouter->findActionHandler($action);
						$response = $handler->handle($action);
					}catch(ActionHandlerNotEnabled $e){
						$response = new ErrorResponse(
							HTTPStatus::FORBIDDEN,
							$translator->get("server/engine/core/app/DISABLED_MODULE")
						);
					}catch(ActionResolutionFailure $e){
						if($action->getRequest()->isAjax()) $response = new ErrorResponse(
							HTTPStatus::INTERNAL_SERVER_ERROR,
							$translator->getAndReplace(
								"server/engine/core/app/ACTION_HANDLER_NOT_FOUND",
								$action->getInternalPath()
							)
						);
						else $response = new StaticResponse($action);
					}catch(\Error $e){
						$response = new ErrorResponse(
							HTTPStatus::INTERNAL_SERVER_ERROR,
							$translator->getAndReplace(
								"server/engine/core/app/INTERNAL_ERROR",$e
							)
						);
					}
				}else{
					if(is_null($permission->getResponse())){
						if($action->getRequest()->isAjax()){
							$response = new ErrorResponse(
								HTTPStatus::FORBIDDEN,
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
				}else $this->redirect($response->getUrl(),$response->getCode(), $response->isAbsolute());
			}else if($response instanceof FileResponse){
				$this->sendFile($response->getData());
			}

			$responseRouter = $this->_context->getResponseRouter();
			try{
				$handler = $responseRouter->findResponseHandler($action,$response);
			}catch(ResponseHandlerNotEnabled $e){
				$response = new ErrorResponse(
					HTTPStatus::FORBIDDEN,
					$translator->get("server/engine/core/app/DISABLED_MODULE")
				);
				$handler = $responseRouter->findResponseHandler($action,$response);
			}catch(ResponseResolutionFailure $e){
				$response = new ErrorResponse(
					HTTPStatus::NOT_FOUND,
					$translator->getAndReplace(
						"server/engine/core/app/404_NOT_FOUND",
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
	 * @param string    $url  URL de redirection
	 * @param int|null  $code Code de redirection
	 * @param bool|null $absolute
	 */
	private function redirect(string $url,?int $code = null, ?bool $absolute = false){
		if(HTTPStatus::existsValue($code)){
			http_response_code($code);
		}
		$url = $absolute ? $url : $this->_context->getRouter()->url($url);
		header("Location: $url");
		exit(0);
	}

	/**
	 * @param string $filename
	 */
	private function sendFile(string $filename):void{
		//Get file type and set it as Content Type
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		header('Content-Type: '.finfo_file($finfo, $filename));
		finfo_close($finfo);

		//Use Content-Disposition: attachment to specify the filename
		header('Content-Disposition: attachment; filename='.basename($filename));

		//No cache
		header('Expires: 0');
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		//Define file size
		header('Content-Length: ' . filesize($filename));

		ob_clean();
		flush();
		readfile($filename);
		exit;
	}
}