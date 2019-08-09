<?php
namespace wfw\engine\core\action;

use wfw\engine\core\action\errors\ActionHandlerNotEnabled;
use wfw\engine\core\action\errors\ActionHandlerNotFound;
use wfw\engine\core\action\errors\InvalidActionHandler;
use wfw\engine\core\conf\IConf;

/**
 * Router d'actions de base.
 */
final class ActionRouter implements IActionRouter {
	/** @var string[] $_enabledPackages */
	private $_enabledPackages;
	/** @var IActionHandlerFactory $_factory */
	private $_factory;
	/** @var int $_foldingLimit */
	private $_foldingLimit;

	/**
	 * ActionRouter constructor.
	 *
	 * @param IConf                 $conf
	 * @param IActionHandlerFactory $factory      Factory pour les ActionHandlers
	 * @param int                   $foldingLimit (optionnel defaut : 5) Limite du nombre de sous
	 *                                            repertoirs de recherche. Si la limite est atteinte, et que le
	 *                                            handler n'est pas trouvé, l'exception HandlerNotFound sera levée.
	 */
	public function __construct(IConf $conf,IActionHandlerFactory $factory,int $foldingLimit = 5) {
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
	 * @param IAction $action Action à router
	 * @return IActionHandler Handler destinataire de l'action
	 */
	public function findActionHandler(IAction $action): IActionHandler {
		$path = explode('/',$action->getInternalPath());
		if(!empty($this->_enabledPackages) && !is_null($package = array_shift($path))){
			$handlerClass = "handlers\\action";
			$modulePackage = $package;
			$moduleHandlerClass = $handlerClass;
			$handlerFound = false;
			$folding = 0;
			while(!is_null($part = array_shift($path)) && $folding<$this->_foldingLimit){
				$handlerClass.="\\";
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
				}else{
					$handlerClass.=$part;
					if($this->enabledPackage($modulePackage,"modules"))
						$moduleHandlerClass.="\\$part\\";
					else $modulePackage.="\\$part";
				}
			}
			if($handlerFound && !(new \ReflectionClass($handlerClass))->isAbstract()){
				if(!$this->enabledPackage($package) && !$this->enabledPackage($modulePackage,"modules")) {
					throw new ActionHandlerNotEnabled(
						"Action found : $handlerClass, but the '$package' package or the module '$modulePackage' haven't been enabled. "
						."Please check your project configuration and add this package to the 'server/packages' list."
					);
				}
				try{
					return $this->_factory->create(
						$handlerClass,
						$path
					);
				}catch(\InvalidArgumentException $e){
					throw new InvalidActionHandler(
						"The given action cannot be resolved to a valid action handler : "
						.$action->getInternalPath()
					);
				}
			}else{
				throw new ActionHandlerNotFound(
					"No handler found for action : ".$action->getInternalPath()
				);
			}
		}else{
			throw new ActionHandlerNotFound(
				"No handler found for action : ".$action->getInternalPath()
			);
		}
	}
}