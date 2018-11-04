<?php
namespace wfw\engine\core\security\rules;

use wfw\engine\core\action\IAction;
use wfw\engine\core\notifier\IMessage;
use wfw\engine\core\notifier\INotifier;
use wfw\engine\core\notifier\Message;
use wfw\engine\core\notifier\MessageTypes;
use wfw\engine\core\response\responses\Redirection;
use wfw\engine\core\security\AccessPermission;
use wfw\engine\core\security\IAccessPermission;
use wfw\engine\core\security\IAccessRule;
use wfw\engine\core\session\ISession;

use wfw\engine\lib\network\http\HTTPStatus;

/**
 * Permet de protéger des URL. Applique des
 */
final class RequireAuthentification implements IAccessRule {
	/** @var string[] $_paths */
	private $_paths;
	/** @var INotifier $_notifier */
	private $_notifier;
	/** @var ISession $_session */
	private $_session;
	/** @var null|string $_sessionKey */
	private $_sessionKey;
	/** @var null|string $_redirUrl */
	private $_redirUrl;
	/** @var null|IMessage $_message */
	private $_message;

	/**
	 * RequireAuthentification constructor.
	 *
	 * @param ISession      $session
	 * @param INotifier     $notifier
	 * @param string[]      ...$pathsToProtect Chemins à protéger.
	 * @param string        $sessionKey
	 * @param null|string   $redirUrl
	 * @param null|IMessage $message
	 */
	public function __construct(
		ISession $session,
		INotifier $notifier,
		array $pathsToProtect = [],
		?string $sessionKey = null,
		?string $redirUrl = null,
		?IMessage $message = null
	){
		$pathsToProtect = (function(string ...$paths){
			return $paths;
		})(...$pathsToProtect);

		$this->_sessionKey = $sessionKey ?? "user";
		$this->_paths = $pathsToProtect;
		$this->_notifier = $notifier;
		$this->_session = $session;
		$this->_redirUrl = $redirUrl ?? "users/login";
		$this->_message = $message ?? new Message(
			"You must be logged to perform this action !",
			MessageTypes::ERROR
		);
	}

	/**
	 * @param IAction $action Action à tester
	 * @return null|IAccessPermission Si null, action autorisée et interruption de la chaine des
	 *                        vérifications.
	 */
	public function check(IAction $action): ?IAccessPermission {
		foreach($this->_paths as $path){
			if(preg_match("#".$path."#",$action->getInternalPath())){
				if(!$this->_session->exists($this->_sessionKey)){
					if(!$action->getRequest()->isAjax())
						$this->_notifier->addMessage($this->_message);
					return new AccessPermission(
						false,
						HTTPStatus::FORBIDDEN,
						"Access denied : you must be logged",
						new Redirection($this->_redirUrl,HTTPStatus::FORBIDDEN)
					);
				}else return new AccessPermission(true);
			}
		}
		return null;
	}
}