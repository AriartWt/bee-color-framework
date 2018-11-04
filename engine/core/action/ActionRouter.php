<?php
namespace wfw\engine\core\action;

use wfw\engine\core\action\errors\ActionHandlerNotFound;
use wfw\engine\core\action\errors\InvalidActionHandler;

/**
 * Router d'actions de base.
 */
final class ActionRouter implements IActionRouter {
	/** @var IActionHandlerFactory $_factory */
	private $_factory;
	/** @var int $_foldingLimit */
	private $_foldingLimit;

	/**
	 * ActionRouter constructor.
	 *
	 * @param IActionHandlerFactory $factory Factory pour les ActionHandlers
	 * @param int $foldingLimit (optionnel defaut : 5) Limite du nombre de sous
	 *                          repertoirs de recherche. Si la limite est atteinte, et que le
	 *                          handler n'est pas trouvé, l'exception HandlerNotFound sera levée.
	 */
	public function __construct(IActionHandlerFactory $factory,int $foldingLimit = 5) {
		$this->_factory = $factory;
		$this->_foldingLimit = $foldingLimit;
	}

	/**
	 * @param IAction $action Action à router
	 * @return IActionHandler Handler destinataire de l'action
	 */
	public function findActionHandler(IAction $action): IActionHandler {
		$path = explode('/',$action->getInternalPath());
		if(!is_null($package = array_shift($path))){
			$handlerClass = "package\\$package\\handlers\\action";
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
			if($handlerFound && !(new \ReflectionClass($handlerClass))->isAbstract()){
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