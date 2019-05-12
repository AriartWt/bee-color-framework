<?php
namespace wfw\engine\package\users\handlers\action\admin;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\lib\data\string\json\IJSONEncoder;
use wfw\engine\package\users\data\model\IUserModelAccess;

/**
 * Liste tous les utilisateurs
 */
final class ListHandler implements IActionHandler{
	/** @var IUserModelAccess $_access */
	private $_access;
	/** @var IJSONEncoder $_encoder */
	private $_encoder;
	private $_translator;

	/**
	 * ListHandler constructor.
	 *
	 * @param IUserModelAccess $access
	 * @param IJSONEncoder     $encoder
	 * @param ITranslator      $translator
	 */
	public function __construct(
		IUserModelAccess $access,
		IJSONEncoder $encoder,
		ITranslator $translator
	) {
		$this->_access = $access;
		$this->_encoder = $encoder;
		$this->_translator = $translator;
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		if($action->getRequest()->isAjax()) return new Response(
			$this->_encoder->jsonEncode($this->_access->getAll())
		);
		else return new ErrorResponse(404,$this->_translator->getAndReplace(
			"server/engine/core/app/404_NOT_FOUND",$action->getRequest()->getURI()
		));
	}
}