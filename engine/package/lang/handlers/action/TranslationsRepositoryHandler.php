<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/02/18
 * Time: 02:19
 */

namespace wfw\engine\package\lang\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Response;

/**
 * Gère le dépot de traductions pour les chaines JavaScript
 */
final class TranslationsRepositoryHandler implements IActionHandler
{
	/**
	 * @var \stdClass $_trads
	 */
	private $_translator;
	/**
	 * @var string $_langPath
	 */
	private $_langPath;

	/**
	 * TranslationsRepositoryHandler constructor.
	 *
	 * @param ITranslator $translator
	 */
	public function __construct(ITranslator $translator)
	{
		$this->_translator = $translator;
		$this->_langPath = "client/";
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse
	{
		$path = $action->getRequest()->getData()->get(IRequestData::POST)->lang_path??'';
		if(strlen($path) > 0){
			$trads = $this->_translator->getAll($this->_langPath.$path);
			if($action->getRequest()->isAjax() && $trads instanceof \stdClass){
				return new Response($trads);
			}else{
				return new ErrorResponse(404,"Translations not found for $path");
			}
		}else{
			return new ErrorResponse(200,"Bad path given : $path !");
		}
	}
}