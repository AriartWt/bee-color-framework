<?php
namespace wfw\engine\package\lang\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\core\session\ISession;
use wfw\engine\package\lang\security\data\TranslationPathRule;

/**
 * Gère le dépot de traductions pour les chaines JavaScript
 */
final class TranslationsRepositoryHandler implements IActionHandler {
	/** @var \stdClass $_trads */
	private $_translator;
	/** @var string $_langPath */
	private $_langPath;
	/** @var TranslationPathRule $_rule */
	private $_rule;

	/**
	 * TranslationsRepositoryHandler constructor.
	 *
	 * @param ITranslator         $translator
	 * @param TranslationPathRule $rule Régle de validation
	 */
	public function __construct(ITranslator $translator,TranslationPathRule $rule) {
		$this->_translator = $translator;
		$this->_langPath = "client/";
		$this->_rule = $rule;
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		$data = $action->getRequest()->getData()->get(IRequestData::POST,true);
		$report = $this->_rule->applyTo($data);
		if(!$report->satisfied()) return new ErrorResponse(
			201,$report->message(),$report->errors()
		);
		$path = $data["lang_path"];
		if(strlen($path) > 0){
			$trads = $this->_translator->getAll($this->_langPath.$path);
			if($action->getRequest()->isAjax() && $trads instanceof \stdClass){
				return new Response($trads);
			}else{
				return new ErrorResponse(404,"Translations not found for $path");
			}
		}else{
			return new ErrorResponse(201,"Bad path given : $path !");
		}
	}
}