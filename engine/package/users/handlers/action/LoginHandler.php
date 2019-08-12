<?php
namespace wfw\engine\package\users\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\notifier\INotifier;
use wfw\engine\core\notifier\Message;
use wfw\engine\core\notifier\MessageTypes;
use wfw\engine\core\request\IRequest;
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\Redirection;
use wfw\engine\core\response\responses\StaticResponse;
use wfw\engine\core\router\IRouter;
use wfw\engine\core\session\ISession;
use wfw\engine\lib\HTML\helpers\forms\errors\HoneypotFilled;
use wfw\engine\lib\HTML\helpers\forms\errors\TooShortSubmissionTime;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\users\command\CancelPasswordRetrieving;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\domain\states\UserWaitingForPasswordReset;
use wfw\engine\package\users\lib\HTML\LoginForm;

/**
 * Class LoginHandler
 *
 * @package wfw\site\package\web\handlers\action
 */
final class LoginHandler implements IActionHandler {
	/** @var IUserModelAccess $_userModel */
	private $_userModel;
	/** @var string $_errorIcon */
	private $_errorIcon;
	/** @var INotifier $_notifier */
	private $_notifier;
	/** @var ISession $_session */
	private $_session;
	/** @var LoginForm $_form */
	private $_form;
	/** @var ICommandBus $_bus */
	private $_bus;
	/** @var ITranslator $_translator */
	private $_translator;

	/**
	 * LoginHandler constructor.
	 *
	 * @param INotifier        $notifier
	 * @param ISession         $session
	 * @param IRouter          $router
	 * @param IUserModelAccess $userModel
	 * @param ICommandBus      $bus
	 * @param ITranslator      $translator
	 */
	public function __construct(
		INotifier $notifier,
		ISession $session,
		IRouter $router,
		IUserModelAccess $userModel,
		ICommandBus $bus,
		ITranslator $translator
	){
		$this->_translator = $translator;
		$this->_notifier = $notifier;
		$this->_session = $session;
		$this->_errorIcon = $router->webroot('Image/Icons/delete.png');
		$this->_userModel = $userModel;
		$this->_bus = $bus;

		if(!$session->exists('login_form')){
			$this->_form = $this->createForm();
			$session->set('login_form',$this->_form);
		}else{
			$this->_form = $session->get('login_form');
		}
	}

	/**
	 * @return LoginForm
	 */
	private function createForm():LoginForm{
		return new LoginForm($this->_translator, $this->_errorIcon);
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		$key = "server/engine/package/users";
		if($action->getRequest()->getMethod() === IRequest::POST) {
			$data = $action->getRequest()->getData()->get(IRequestData::POST, true);
			$res = false; $except = false;
			try{
				$res = $this->_form->validates($data);
			}catch (HoneypotFilled $e){
				$except = true;
				$this->_notifier->addMessage(new Message(
					$this->_translator->get("$key/forms/SPAM_ERROR"),MessageTypes::ERROR
				));
			}catch (TooShortSubmissionTime $e){
				$except = true;
				$this->_notifier->addMessage(new Message(
					$this->_translator->get("$key/forms/MINTIME_ERROR"), MessageTypes::ERROR
				));
			}
			if($res) {
				try{
					$user = $this->_userModel->getEnabledByLogin($data["login"]);
				}catch(\Exception | \Error $e){
					$this->_notifier->addMessage(new Message($this->_translator->get(
						"$key/SERVICE_UNAVAILABLE"
					),MessageTypes::ERROR));
					return new StaticResponse($action);
				}
				if(!is_null($user) && $user->getPassword()->equals($data["password"])){
					//Si l'utilisateur avait fait une demande de récupération de mot de passe
					//sans aller jusqu'au bout, on l'annule.
					if($user->getState() instanceof UserWaitingForPasswordReset)
						$this->_bus->executeCommand(new CancelPasswordRetrieving(
							$user->getId(),$user->getId()
						));
					$this->_notifier->addMessage(new Message(
						$this->_translator->get("$key/CONNECTION_ACCEPTED")
					));
					$this->_session->set('login_form',$this->createForm());
					/** @var IAction $action */
					$action = $this->_session->get('previous_action');
					$this->_session->set("user",$user);
					$this->_session->set("csrfToken",new UUID(UUID::V4));
					return new Redirection(substr($action->getRequest()->getURL(),1));
				}else{
					if(is_null($user)) $this->_notifier->addMessage(new Message(
						$this->_translator->get("$key/UNKNOWN_USER"),
						MessageTypes::ERROR
					));
					else $this->_notifier->addMessage(new Message(
						$this->_translator->get("$key/WRONG_PASSWORD"),
						MessageTypes::ERROR
					));
				}
			}else{
				if(!$except) $this->_notifier->addMessage(new Message(
					$this->_translator->get("$key/CONNECTION_FAILED"),
					MessageTypes::ERROR
				));
			}
		}else if($this->_session->isLogged()) return new Redirection("web/home");
		else $this->_session->set('login_form',$this->createForm());
		return new StaticResponse($action);
	}
}