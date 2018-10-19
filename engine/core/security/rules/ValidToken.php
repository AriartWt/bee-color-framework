<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 19/02/18
 * Time: 08:54
 */

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
 * Vérifie la validité des token. Pour que la régle s'applique, un token doit être défini dans la
 * session sous le label csrftoken.
 * Si aucun token défini, la régle interromp les vérification et renvoie null.
 * Si un token est défini, il doit être présent et bon, sinon la régle renvoie false.
 */
final class ValidToken implements IAccessRule
{
	/**
	 * @var string|null $_csrf
	 */
	private $_csrf;
	/**
	 * @var null|string $_redirUrl
	 */
	private $_redirUrl;
	/**
	 * @var INotifier $_notifier
	 */
	private $_notifier;
	/**
	 * @var null|IMessage $_message
	 */
	private $_message;

	/**
	 * ValidTokenRule constructor.
	 *
	 * @param ISession      $session  Session
	 * @param INotifier     $notifier Notifier
	 * @param null|string   $redirUrl (optionnel) URL de redirection en cas d'echec
	 * @param null|IMessage $message  (optionnel) Message à afficher à l'utilisateur pour
	 *                                information sur la redirection en cas d'erreur. Si null, un
	 *                                message par défaut est créé.
	 * @param null|string $sessionKey (optionnel default : "csrftoken") Clé de la session
	 *                                permettant de récupérer le token csrf.
	 */
	public function __construct(
		ISession $session,
		INotifier $notifier,
		?string $redirUrl = null,
		?IMessage $message = null,
		?string $sessionKey = null)
	{
		$this->_csrf = $session->get($sessionKey ?? "csrfToken");
		$this->_notifier = $notifier;
		$this->_redirUrl = $redirUrl ?? "users/login";
		$this->_message = $message ?? new Message(
			"Bad csrf token supplied !",
			MessageTypes::ERROR
		);
	}

	/**
	 * @param IAction $action Action à tester
	 * @return null|IAccessPermission Si null, action autorisée et interruption de la chaine des
	 *                        vérifications.
	 */
	public function check(IAction $action): ?IAccessPermission{
		if(!is_null($this->_csrf)){
			if($action->getRequest()->getCSRFToken() !== (string) $this->_csrf){
				$this->_notifier->addMessage($this->_message);
				return new AccessPermission(
					false,
					HTTPStatus::FORBIDDEN,
					"Access denied : invalid token",
					new Redirection($this->_redirUrl,HTTPStatus::FORBIDDEN)
				);
			}else{
				return new AccessPermission(true);
			}
		}
		return null;
	}
}