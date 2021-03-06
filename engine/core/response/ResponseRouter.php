<?php
namespace wfw\engine\core\response;

use wfw\engine\core\action\IAction;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\response\errors\InvalidResponseHandler;
use wfw\engine\core\response\errors\ResponseHandlerNotEnabled;
use wfw\engine\core\response\errors\ResponseHandlerNotFound;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\package\general\handlers\response\AjaxHandler;
use wfw\engine\package\general\handlers\response\ErrorHandler;

/**
 * Route une réponse vers son handler.
 * Si une vue sans handler existe, elle sera chargée par défaut par le GenericResponseHandler
 * afin d'éviter de créer trop de handlers simples
 */
final class ResponseRouter implements IResponseRouter {
	/** @var IResponseHandlerFactory $_factory */
	private $_factory;
	/** @var int $_foldingLimit */
	private $_foldingLimit;
	/** @var array $_enabledPackage */
	private $_enabledPackages;

	/**
	 * ActionResponseRouter constructor.
	 *
	 * @param IConf                   $conf
	 * @param IResponseHandlerFactory $factory      Factory pour la création des ResponseHandlers
	 * @param int                     $foldingLimit (optionnel defaut : 5) Limite du nombre de sous
	 *                                              repertoirs de recherche. Si la limite est atteinte, et que le
	 *                                              handler n'est pas trouvé, l'exception HandlerNotFound sera levée.
	 */
	public function __construct(IConf $conf,IResponseHandlerFactory $factory, int $foldingLimit = 5) {
		$this->_enabledPackages = array_flip($conf->getArray("server/packages") ?? []);
		$this->_factory = $factory;
		$this->_foldingLimit = $foldingLimit;
	}

	/**
	 * @param string      $package
	 * @param null|string $location
	 * @return bool
	 */
	private function enabledPackage(string $package, ?string $location = null):bool{
		$package = str_replace("\\","/",$package);
		if(is_null($location)) return isset($this->_enabledPackages["site/$package"])
			|| isset($this->_enabledPackages["modules/$package"])
			|| isset($this->_enabledPackages["engine/$package"]);
		else return isset($this->_enabledPackages["$location/$package"]);
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
					$handlerClass = "handlers\\response";
					$modulePackage = $package;
					$moduleHandlerClass = $handlerClass;
					$handlerFound = false;
					$handlerArgs = [];
					$folding = 0;
					while(!is_null($part = array_shift($path)) && $folding<$this->_foldingLimit){
						$handlerClass.="\\";
						$viewClass = str_replace("handlers\\response","views","package\\$package\\".$handlerClass)
							.lcfirst($part)."\\".ucfirst($part);
						$moduleViewClass = str_replace("handlers\\response","views","wfw\\modules\\$modulePackage\\".$moduleHandlerClass)
							.lcfirst($part)."\\".ucfirst($part);
						$tmpName = ucfirst($part)."Handler";
						if(class_exists("wfw\\site\\package\\$package\\".$handlerClass.$tmpName)){
							$handlerClass = "wfw\\site\\package\\$package\\".$handlerClass.$tmpName;
							$handlerFound = true;
							break;
						}else if(class_exists("wfw\\modules\\$modulePackage\\".$moduleHandlerClass."\\".$tmpName)){
							$handlerClass = "wfw\\modules\\$modulePackage\\".$moduleHandlerClass."\\".$tmpName;
							$handlerFound = true;
							break;
						}else if(class_exists("wfw\\engine\\package\\$package\\".$handlerClass.$tmpName)){
							$handlerClass = "wfw\\engine\\package\\$package\\".$handlerClass.$tmpName;
							$handlerFound = true;
							break;
						}else if(class_exists("wfw\\site\\$viewClass")){
							$handlerClass = GenericResponseHandler::class;
							$handlerArgs[] = "wfw\\site\\$viewClass";
							$handlerFound = true;
							break;
						}else if(class_exists("wfw\\modules\\$moduleViewClass")){
							$handlerClass = GenericResponseHandler::class;
							$handlerArgs[] = "wfw\\modules\\$moduleViewClass";
							$handlerFound = true;
							break;
						}else if(class_exists("wfw\\engine\\$viewClass")){
							$handlerClass = GenericResponseHandler::class;
							$handlerArgs[] = "wfw\\engine\\$viewClass";
							$handlerFound = true;
							break;
						}else{
							$handlerClass.=$part;
							if($this->enabledPackage($modulePackage,"modules"))
								$moduleHandlerClass.="\\$part\\";
							else $modulePackage.="\\$part";
						}
					}
					if($handlerFound){
						if(!$this->enabledPackage($package) && !$this->enabledPackage($modulePackage,"modules")) {
							throw new ResponseHandlerNotEnabled(
								"Response found : $handlerClass, but the '$package' package or the module '$modulePackage' haven't been enabled. "
								."Please check your project configuration and add this package to the 'server/packages' list."
							);
						}
						try{
							return $this->_factory->create(
								$handlerClass,
								array_merge($handlerArgs,$path)
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