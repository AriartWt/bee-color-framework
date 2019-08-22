<?php
namespace wfw\engine\package\users\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\notifier\INotifier;
use wfw\engine\core\notifier\Message;
use wfw\engine\core\request\IRequest;
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Redirection;
use wfw\engine\core\response\responses\Response;
use wfw\engine\core\response\responses\StaticResponse;
use wfw\engine\core\router\IRouter;
use wfw\engine\core\session\ISession;
use wfw\engine\package\users\command\ResetPassword;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\domain\events\UserPasswordResetedEvent;
use wfw\engine\package\users\domain\states\UserWaitingForPasswordReset;
use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;
use wfw\engine\package\users\lib\HTML\ResetPasswordForm;
use wfw\engine\package\users\security\data\ResetPasswordRule;

/**
 * Permet de remettre à 0 un mot de passe.
 */
final class ResetPasswordHandler implements IActionHandler,IDomainEventListener{
	/** @var string $_errorIcon */
	private $_errorIcon;
	/** @var ISession $_session */
	private $_session;
	/** @var ResetPasswordForm $_form */
	private $_form;
	/** @var null|UserPasswordResetedEvent $_event */
	private $_event;
	/** @var INotifier $_notifier */
	private $_notifier;
	/** @var IUserModelAccess $_access */
	private $_access;
	/** @var ResetPasswordRule $_resetPasswordRule */
	private $_resetPasswordRule;
	/** @var IRouter $_router */
	private $_router;
	/** @var ICommandBus $_bus */
	private $_bus;
	/** @var ITranslator $_translator */
	private $_translator;

	/**
	 * ResetPasswordHandler constructor
	 *
	 * @param IRouter              $router
	 * @param ISession             $session
	 * @param IDomainEventObserver $observer
	 * @param IUserModelAccess     $access
	 * @param INotifier            $notifier
	 * @param ICommandBus          $bus
	 * @param ITranslator          $translator
	 * @param ResetPasswordRule    $rule
	 */
	public function __construct(
		IRouter $router,
		ISession $session,
		IDomainEventObserver $observer,
		IUserModelAccess $access,
		INotifier $notifier,
		ICommandBus $bus,
		ITranslator $translator,
		ResetPasswordRule $rule
	){
		$this->_bus = $bus;
		$this->_router = $router;
		$this->_access = $access;
		$this->_session = $session;
		$this->_resetPasswordRule = $rule;
		$this->_notifier = $notifier;
		$this->_translator = $translator;
		$this->_errorIcon = $router->webroot("Image/Icons/delete.png");
		$observer->addDomainEventListener(UserPasswordResetedEvent::class, $this);
		if(!$session->exists("reset_password_form")){
			$this->_form = $this->createForm();
			$session->set("reset_password_form",$this->_form);
		}else{
			$this->_form = $session->get("reset_password_form");
		}
	}

	/**
	 * @return ResetPasswordForm
	 */
	private function createForm():ResetPasswordForm{
		return new ResetPasswordForm(
			$this->_translator,
			$this->_resetPasswordRule,
			$this->_errorIcon
		);
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		$key = "server/engine/package/users";
		if($action->getRequest()->getMethod() === IRequest::POST){
			$data = $action->getRequest()->getData()->get(IRequestData::GET,true);
			$result = $this->_resetPasswordRule->applyTo($data);
			if($result->satisfied()){
				$user = $this->_access->getById($data["id"]);
				if(is_null($user))
					return new ErrorResponse(201,$this->_translator->getAndReplace(
						"$key/NOT_FOUND",$data["id"]
					));
				if(!($user->getState() instanceof UserWaitingForPasswordReset))
					return new ErrorResponse(403,$this->_translator->get(
						"$key/INVALID_USER_STATE"
					));
				/** @var UserWaitingForPasswordReset $state */
				$state = $user->getState();
				if(!$state->isValide(new UserConfirmationCode($data["code"])))
					return new ErrorResponse(403,$this->_translator->get(
						"$key/BAD_CONFIRM_CODE"
					));
				$post = $action->getRequest()->getData()->get(IRequestData::POST,true);
				if($this->_form->validates($post)){
					$this->_bus->executeCommand(new ResetPassword(
						$data["id"],
						$data["id"],
						$post["password"],
						$data["code"]
					));
					if(is_null($this->_event)) return new ErrorResponse(500,
						$this->_translator->get("$key/RESET_PASSWORD_CONFIRMED")
					);
					if($action->getRequest()->isAjax()) return new Response();
					else $this->_notifier->addMessage(new Message($this->_translator->get(
						"$key/RESET_PASSWORD_CONFIRMED"
					)));
				}
			}else return new Redirection($this->_router->url("/"),403);
		}else $this->_session->set("reset_password_form",$this->createForm());
		return new StaticResponse($action);
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveDomainEvent(IDomainEvent $e): void {
		if($e instanceof UserPasswordResetedEvent) $this->_event = $e;
	}
}