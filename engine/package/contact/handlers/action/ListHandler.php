<?php
namespace wfw\engine\package\contact\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\lib\data\string\json\IJSONEncoder;
use wfw\engine\package\contact\data\model\IContactModelAccess;

/**
 * Class ListHandler
 *
 * @package wfw\engine\package\contact\handlers\action
 */
final class ListHandler implements IActionHandler{
	/** @var IContactModelAccess $_access */
	private $_access;
	/** @var IJSONEncoder $_encoder */
	private $_encoder;
	/** @var ITranslator $_translator */
	private $_translator;

	/**
	 * ListHandler constructor.
	 *
	 * @param ITranslator         $translator
	 * @param IContactModelAccess $access  Acces au model contact
	 * @param IJSONEncoder        $encoder Encodeur JSON
	 */
	public function __construct(
		ITranslator $translator,
		IContactModelAccess $access,
		IJSONEncoder $encoder
	){
		$this->_access = $access;
		$this->_encoder = $encoder;
		$this->_translator = $translator;
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		if($action->getRequest()->isAjax()){
			return new Response($this->_encoder->jsonEncode($this->_access->getUnarchived()));
		}else return new ErrorResponse("404",$this->_translator->getAndReplace(
			"server/engine/core/app/404_NOT_FOUND",$action->getRequest()->getURI()
		));
	}
}