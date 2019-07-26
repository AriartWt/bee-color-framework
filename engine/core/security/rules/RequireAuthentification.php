<?php
namespace wfw\engine\core\security\rules;

use wfw\engine\core\action\IAction;
use wfw\engine\core\lang\ITranslator;
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
 * Protect URLS from being accessed publicly.
 * Can be set to match regexp against the full url (default)
 * Or can be set to match exact parts of the url splited on /
 * ex : [ "admin" => ["panel","logs" => [ "edit","remove" ]], "private" ]
 * Will deny public access to:
 *  - "panel" directory or handler in "admin" package
 *  - "edit","remove" handlers or directories in "logs" directory in "admin" package
 *  - "private" package
 */
final class RequireAuthentification implements IAccessRule {
	/** @var string[] $_paths */
	private $_paths;
	/** @var INotifier $_notifier */
	private $_notifier;
	/** @var ISession $_session */
	private $_session;
	/** @var null|string $_redirUrl */
	private $_redirUrl;
	/** @var null|IMessage $_message */
	private $_message;
	/** @var bool $_treeBased */
	private $_treeBased;

	/**
	 * RequireAuthentification constructor.
	 *
	 * @param ISession    $session
	 * @param INotifier   $notifier
	 * @param ITranslator $translator
	 * @param string[]    ...$pathsToProtect Chemins à protéger.
	 * @param null|string $redirUrl
	 * @param null|string $translationKey
	 * @param bool        $treeBased         (optionnal) MUST BE TRUE if you want a tree based rule set !
	 */
	public function __construct(
		ISession $session,
		INotifier $notifier,
		ITranslator $translator,
		array $pathsToProtect = [],
		?string $redirUrl = null,
		?string $translationKey = null,
		bool $treeBased = false
	){
		$this->_treeBased = !!$treeBased;
		if(!$this->_treeBased) $pathsToProtect = (function(string ...$paths){
			return $paths;
		})(...$pathsToProtect);

		$this->_paths = $pathsToProtect;
		$this->_notifier = $notifier;
		$this->_session = $session;
		$this->_redirUrl = $redirUrl ?? "users/login";
		$this->_message = new Message(
			$translator->getAndTranslate("server/engine/core/app/ACCESS_DENIED")." : "
			.$translator->getAndTranslate(
				$translationKey ?? "server/engine/core/app/MUST_BE_LOGGED"
			),
			MessageTypes::ERROR
		);
	}

	/**
	 * @param IAction $action Action à tester
	 * @return null|IAccessPermission Si null, action autorisée et interruption de la chaine des
	 *                        vérifications.
	 */
	public function check(IAction $action): ?IAccessPermission {
		if($this->_treeBased) return $this->treeCheck($action);
		else return $this->linearCheck($action);
	}

	/**
	 * Tree check (tree based rules set (no regexp) )
	 * ex : [ "admin" => ["panel","logs" => [ "edit","remove" ]], "private" ]
	 * Will deny public access to:
	 *  - "panel" directory or handler in "admin" package
	 *  - "edit","remove" handlers or directories in "logs" directory in "admin" package
	 *  - "private" package
	 *
	 * @param IAction $action
	 * @return null|AccessPermission
	 */
	private function treeCheck(IAction $action): ?AccessPermission{
		$internalPath = explode("/",$action->getInternalPath());
		$array = $this->_paths;
		foreach($internalPath as $pathPart){
			$continue = false;
			foreach($array as $k=>$path){
				if(is_int($k)){
					if($path === lcfirst($pathPart)) return $this->denyPublicAccess($action);
				}else if($k === lcfirst($pathPart)){
					if(is_array($path)){
						$array = $array[$k];
						$continue = true;
						break;
					} else if($path === lcfirst($pathPart)) return $this->denyPublicAccess($action);
				}
			}
			if(!$continue) break;
		}
		return null;
	}

	/**
	 * Linear check (non tree based regexp set)
	 * @param IAction $action
	 * @return null|AccessPermission
	 */
	private function linearCheck(IAction $action): ?AccessPermission{
		foreach($this->_paths as $path){
			if(preg_match("#".$path."#",$action->getInternalPath()))
				return $this->denyPublicAccess($action);
		}
		return null;
	}

	/**
	 * If user is not logged, then deny access.
	 * @param IAction $action
	 * @return AccessPermission
	 */
	private function denyPublicAccess(IAction $action):AccessPermission{
		if(!$this->_session->isLogged()){
			if(!$action->getRequest()->isAjax())
				$this->_notifier->addMessage($this->_message);
			return new AccessPermission(
				false,
				HTTPStatus::FORBIDDEN,
				(string) $this->_message,
				new Redirection($this->_redirUrl,HTTPStatus::FORBIDDEN)
			);
		}else return new AccessPermission(true);
	}
}