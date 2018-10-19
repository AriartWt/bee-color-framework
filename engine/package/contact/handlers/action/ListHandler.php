<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 02/10/18
 * Time: 14:53
 */

namespace wfw\engine\package\contact\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
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

	/**
	 * ListHandler constructor.
	 *
	 * @param IContactModelAccess $access  Acces au model contact
	 * @param IJSONEncoder        $encoder Encodeur JSON
	 */
	public function __construct(IContactModelAccess $access, IJSONEncoder $encoder) {
		$this->_access = $access;
		$this->_encoder = $encoder;
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		if($action->getRequest()->isAjax()){
			return new Response($this->_encoder->jsonEncode($this->_access->getUnarchived()));
		}else return new ErrorResponse("404","Page not found");
	}
}