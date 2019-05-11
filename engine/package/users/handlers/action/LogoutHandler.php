<?php
namespace wfw\engine\package\users\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\notifier\INotifier;
use wfw\engine\core\notifier\Message;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\Redirection;
use wfw\engine\core\session\ISession;

/**
 * Deconnecte l'utilisateur courant
 */
final class LogoutHandler implements IActionHandler {
	/** @var ISession $_session */
	private $_session;
	/** @var INotifier $_notifier */
	private $_notifier;

	/**
	 * LogoutHandler constructor.
	 *
	 * @param ISession     $session Session
	 * @param INotifier    $notifier
	 */
	public function __construct(ISession $session,INotifier $notifier) {
		$this->_session = $session;
		$this->_notifier = $notifier;
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		if($this->_session->isLogged()){
			$this->_session->destroy();
			$this->_notifier->addMessage(new Message("Vous avez bien été déconnecté !"));
		}
		return new Redirection("/");
	}
}