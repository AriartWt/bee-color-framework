<?php

namespace wfw\engine\core\action;

use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\security\IAccessPermission;

/**
 * Redirect all matching urls to a 404 not found error.
 */
final class NotFoundHook implements IActionHook {
	/** @var ITranslator $_translator */
	private $_translator;
	/** @var string $_translationKey */
	private $_translationKey;
	/** @var string[] $_rules */
	private $_rules;

	/**
	 * NotFoundHook constructor.
	 *
	 * @param ITranslator $translator
	 * @param array       $rules
	 * @param string      $translationKey
	 */
	public function __construct(
		ITranslator $translator,
		array $rules,
		string $translationKey="server/engine/core/app/404_NOT_FOUND"
	){
		$this->_translator = $translator;
		$this->_translationKey = $translationKey;
		$this->_rules = (function(string... $rules){return $rules;})(...$rules);
	}

	/**
	 * @param IAction           $action     User action
	 * @param IAccessPermission $permission User permission
	 * @return null|IResponse Response
	 */
	public function hook(IAction $action, IAccessPermission $permission): ?IResponse {
		foreach ($this->_rules as $rule){
			if(preg_match("#$rule#",$action->getInternalPath()))
				return new ErrorResponse(
					"404",
					$this->_translator->getAndReplace(
						$this->_translationKey,
						$action->getInternalPath()
					)
				);
		}
		return null;
	}
}