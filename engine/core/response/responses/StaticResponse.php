<?php
namespace wfw\engine\core\response\responses;

use wfw\engine\core\action\IAction;
use wfw\engine\core\response\IResponse;

/**
 * Action de lecture.
 */
final class StaticResponse implements IResponse {
	/** @var IAction $_action */
	private $_action;

	/**
	 * ReadAction constructor.
	 *
	 * @param IAction $action Action
	 */
	public function __construct(IAction $action) {
		$this->_action = $action;
	}

	/**
	 * @return mixed Données de la réponse
	 */
	public function getData() {
		return $this->_action;
	}
}