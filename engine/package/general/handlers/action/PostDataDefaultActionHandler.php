<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 27/04/18
 * Time: 10:56
 */

namespace wfw\engine\package\general\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\request\IRequest;
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\core\security\data\IRule;
use wfw\engine\package\general\command\MultiCommand;
use wfw\engine\package\general\handlers\action\errors\DataError;
use wfw\engine\package\general\handlers\action\errors\IllegalOperation;
use wfw\engine\package\general\handlers\action\errors\NotFound;

/**
 * Action par défaut permettant d'intercepter une action de type POST.
 * Elle s'occupe de la validation des données POST, renvoie 404 not found si la méthode n'est pas
 * POST. Execute la commande retournée par createCommand()
 */
abstract class PostDataDefaultActionHandler implements IActionHandler {
	/** @var ICommandBus $_bus */
	private $_bus;
	/** @var IRule $_rule */
	private $_rule;
	/** @var int $_dataFlag */
	private $_dataFlag;
	/** @var bool $_requireAjax */
	private $_requireAjax;

	/**
	 * PutArticleOnlineHandler constructor.
	 *
	 * @param ICommandBus $bus Bus de commande.
	 * @param IRule $rule Regle de valdiation
	 * @param bool $withFiles Si true, inclue le tableau _FILES dans la liste des résultats
	 *                               passé à createCommand
	 * @param bool $requireAjax Rejette la requête si elle n'est pas ajax
	 * @param bool $withGet
	 */
	public function __construct(
		ICommandBus $bus,
		IRule $rule,
		bool $withFiles=false,
		bool $requireAjax=false,
		bool $withGet=false
	) {
		$this->_bus = $bus;
		$this->_rule = $rule;
		$this->_requireAjax = $requireAjax;
		$this->_dataFlag = IRequestData::POST;
		if($withFiles) $this->_dataFlag |= IRequestData::FILES;
		if($withGet) $this->_dataFlag |= IRequestData::GET;
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public final function handle(IAction $action): IResponse
	{
		if($action->getRequest()->getMethod() === IRequest::POST
			&& (!$this->_requireAjax || $this->_requireAjax && $action->getRequest()->isAjax())
		){
			$data = $action->getRequest()->getData()->get($this->_dataFlag,true);
			$res = $this->_rule->applyTo($data);
			if(!$res->satisfied())
				return new ErrorResponse(201,$res->message(),$res->errors());
			try{
				$command = $this->createCommand($data);
				if($command instanceof MultiCommand){
					foreach($command->commands() as $cmd){
						$this->_bus->execute($cmd);
					}
				}else $this->_bus->execute($command);
				return $this->successResponse();
			}catch(\Exception $e){
				return $this->handleException($e);
			}catch(\Error $e){
				return $this->handleError($e);
			}
		}else return new ErrorResponse(404,$this->getNotFoundResponseMessage());
	}

	/**
	 * @return string
	 */
	protected function getNotFoundResponseMessage():string{
		return "Page not found";
	}

	/**
	 * @param \Exception $e
	 * @return ErrorResponse
	 */
	protected function handleException(\Exception $e):ErrorResponse{
		if($e instanceof NotFound)
			return new ErrorResponse(404,$e->getMessage());
		else if($e instanceof DataError)
			return new ErrorResponse(201,$e->getMessage());
		else if($e instanceof IllegalOperation)
			return new ErrorResponse(409,$e->getMessage());
		return new ErrorResponse(500,$e);
	}

	/**
	 * @param \Error $e
	 * @return ErrorResponse
	 */
	protected function handleError(\Error $e):ErrorResponse{
		return new ErrorResponse(501,$e);
	}

	/**
	 * @return IResponse
	 */
	protected function successResponse():IResponse{
		return new Response();
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected abstract function createCommand(array $data):ICommand;
}