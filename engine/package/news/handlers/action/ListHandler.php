<?php
namespace wfw\engine\package\news\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\lib\data\string\json\IJSONEncoder;
use wfw\engine\package\news\data\model\IArticleModelAccess;

/**
 * Class ListHandler
 *
 * @package wfw\engine\package\news\handlers\action
 */
final class ListHandler implements IActionHandler {
	/** @var IArticleModelAccess $_access */
	private $_access;
	/** @var IJSONEncoder $_encoder */
	private $_encoder;

	/**
	 * ListHandler constructor.
	 *
	 * @param IArticleModelAccess $access Acces au model des articles
	 * @param IJSONEncoder        $encoder Encodeur d'objets au format JSON
	 */
	public function __construct(IArticleModelAccess $access,IJSONEncoder $encoder) {
		$this->_access = $access;
		$this->_encoder = $encoder;
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse{
		if($action->getRequest()->isAjax()){
			return new Response($this->_encoder->jsonEncode($this->_access->getUnarchived()));
		}else return new ErrorResponse("404","Page not found");
	}
}