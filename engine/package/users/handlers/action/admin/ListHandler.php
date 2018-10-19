<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 02/07/18
 * Time: 08:52
 */

namespace wfw\engine\package\users\handlers\action\admin;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
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

	/**
	 * ListHandler constructor.
	 * @param IUserModelAccess $access
	 * @param IJSONEncoder $encoder
	 */
	public function __construct(IUserModelAccess $access,IJSONEncoder $encoder) {
		$this->_access = $access;
		$this->_encoder = $encoder;
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		if($action->getRequest()->isAjax()) return new Response(
			$this->_encoder->jsonEncode($this->_access->getAll())
		);
		else return new ErrorResponse(404,"Not found");
	}
}