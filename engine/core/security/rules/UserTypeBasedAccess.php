<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 01/07/18
 * Time: 08:19
 */

namespace wfw\engine\core\security\rules;

use wfw\engine\core\action\IAction;
use wfw\engine\core\notifier\IMessage;
use wfw\engine\core\notifier\INotifier;
use wfw\engine\core\notifier\Message;
use wfw\engine\core\response\responses\Redirection;
use wfw\engine\core\security\AccessPermission;
use wfw\engine\core\security\IAccessPermission;
use wfw\engine\core\security\IAccessRule;
use wfw\engine\core\session\ISession;
use wfw\engine\lib\network\http\HTTPStatus;
use wfw\engine\package\users\data\model\DTO\User;
use wfw\engine\package\users\domain\types\Admin;
use wfw\engine\package\users\domain\types\UserType;

/**
 * Base les régle d'accés sur le type d'utilisateur
 */
final class UserTypeBasedAccess implements IAccessRule{
	/** @var ISession $_session */
	private $_session;
	/** @var INotifier $_notifier */
	private $_notifier;
	/** @var null|string $_key */
	private $_key;
	/** @var null|IMessage|Message $_message */
	private $_message;
	/** @var array $_roles */
	private $_roles;
	/** @var string $_redirUrl */
	private $_redirUrl;

	/**
	 * UserTypeBasedAccess constructor.
	 * @param ISession $session
	 * @param INotifier $notifier
	 * @param array $roles Liste de regexp permettant d'autoriser un type d'utilisateur à accéder à
	 *                     une action
	 * @param null|string $redir_url Url de redirection lorsque l'accés est refusé
	 * @param null|string $sessionKey Cle de session contenant l'utilisateur loggé
	 * @param null|IMessage $message
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		ISession $session,
		INotifier $notifier,
		array $roles,
		?string $redir_url=null,
		?string $sessionKey = null,
		?IMessage $message=null
	){
		$this->_notifier = $notifier;
		$this->_session = $session;
		$this->_key = $sessionKey ?? "user";
		$this->_redirUrl = "/";
		$this->_message = $message ?? new Message("Permission denied !");
		foreach($roles as $r=>$rules){
			if(!class_exists($r) || !is_a($r,UserType::class,true)
			) throw new \InvalidArgumentException("$r doesn't implements ".UserType::class);
			(function(string... $rules){return $rules;})(...$rules);
		}
		$this->_roles = $roles;
	}

	/**
	 * @param IAction $action Action à tester
	 * @return null|IAccessPermission Si null, action autorisée et interruption de la chaine des
	 * vérifications.
	 */
	public function check(IAction $action): ?IAccessPermission {
		/** @var User $user */
		$user = $this->_session->get($this->_key);
		$type = get_class($user->getType());
		//les administrateurs ont accés à tout.
		if($user->getType() instanceof Admin) return new AccessPermission(true);
		else if(!isset($this->_roles[$type])){
			//Si le type d'utilisateur n'est pas défini, on autorise aucune action
			return $this->getDeniedPermission($action);
		}else{
			foreach($this->_roles[$type] as $pattern){
				if(preg_match("#$pattern#",$action->getInternalPath()))
					return new AccessPermission(true);
			}
			//Si aucun pattern ne match le role courant, on rejette
			return $this->getDeniedPermission($action);
		}
	}

	/**
	 * @param IAction $action
	 * @return AccessPermission
	 */
	private function getDeniedPermission(IAction $action):AccessPermission{
		if(!$action->getRequest()->isAjax())
			$this->_notifier->addMessage($this->_message);
		return new AccessPermission(
			false,
			HTTPStatus::FORBIDDEN,
			"Access denied : insufficient user privileges",
			new Redirection($this->_redirUrl,HTTPStatus::FORBIDDEN)
		);
	}
}