<?php
namespace wfw\engine\package\general\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Response;

/**
 * Sert à maintenir une session PHP en vie aussi longtemps qu'une interface en a besoin.
 */
final class HeartBeatHandler implements IActionHandler {
	/** @var ITranslator $_translator */
	private $_translator;

	/**
	 * HeartBeatHandler constructor.
	 *
	 * @param ITranslator $translator
	 */
	public function __construct(ITranslator $translator) {
		$this->_translator = $translator;
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		if($action->getRequest()->isAjax()) return new Response();
		return new ErrorResponse("404",$this->_translator->getAndReplace(
			"server/engine/core/app/404_NOT_FOUND",$action->getRequest()->getURI()
		));
	}
}