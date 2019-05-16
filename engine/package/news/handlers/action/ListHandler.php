<?php
namespace wfw\engine\package\news\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\lang\ITranslator;
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
	/** @var ITranslator $_translator */
	private $_translator;

	/**
	 * ListHandler constructor.
	 *
	 * @param IArticleModelAccess $access  Acces au model des articles
	 * @param ITranslator         $translator
	 * @param IJSONEncoder        $encoder Encodeur d'objets au format JSON
	 */
	public function __construct(
		IArticleModelAccess $access,
		ITranslator $translator,
		IJSONEncoder $encoder
	) {
		$this->_access = $access;
		$this->_encoder = $encoder;
		$this->_translator = $translator;
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse{
		if($action->getRequest()->isAjax()){
			return new Response($this->_encoder->jsonEncode($this->_access->getUnarchived()));
		}else return new ErrorResponse("404",$this->_translator->getAndReplace(
			"server/engine/core/app/404_NOT_FOUND",$action->getRequest()->getURI()
		));
	}
}