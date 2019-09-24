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
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Redirection;
use wfw\engine\core\response\responses\Response;
use wfw\engine\core\session\ISession;
use wfw\engine\package\users\command\ConfirmUserRegistration;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\domain\events\UserConfirmedEvent;
use wfw\engine\package\users\domain\states\UserWaitingForRegisteringConfirmation;
use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;
use wfw\engine\package\users\security\data\ConfirmRule;

/**
 * Confirme l'inscription d'un utilisateur
 */
final class ConfirmUserRegistrationHandler implements IActionHandler,IDomainEventListener{
	/** @var null|UserConfirmedEvent $_event */
	private $_event;
	/** @var ICommandBus $_bus */
	private $_bus;
	/** @var INotifier $_notifier */
	private $_notifier;
	/** @var IUserModelAccess $_access */
	private $_access;
	/** @var ConfirmRule $_rule */
	private $_rule;
	/** @var ISession $_session */
	private $_session;
	/** @var ITranslator $_translator */
	private $_translator;

	/**
	 * ConfirmUserRegistrationHandler constructor.
	 *
	 * @param ICommandBus          $bus
	 * @param INotifier            $notifier
	 * @param IUserModelAccess     $access
	 * @param IDomainEventObserver $observer
	 * @param ISession             $session
	 * @param ITranslator          $translator
	 * @param ConfirmRule          $rule
	 */
	public function __construct(
		ICommandBus $bus,
		INotifier $notifier,
		IUserModelAccess $access,
		IDomainEventObserver $observer,
		ISession $session,
		ITranslator $translator,
		ConfirmRule $rule
	){
		$this->_translator = $translator;
		$this->_bus = $bus;
		$this->_notifier = $notifier;
		$this->_access = $access;
		$this->_rule = $rule;
		$this->_session = $session;
		$observer->addDomainEventListener(UserConfirmedEvent::class, $this);
	}

	/**
	 * @param IAction $action Action
	 * @return ErrorResponse
	 */
	public function handle(IAction $action): IResponse {
		$key = "server/engine/package/users";
		$data = $action->getRequest()->getData()->get(IRequestData::GET);
		$report = $this->_rule->applyTo($data);
		if($report->satisfied()){
			$user = $this->_access->getById($data["id"]);
			if(is_null($user))
				return new ErrorResponse(201,$this->_translator->getAndReplace(
					"$key/NOT_FOUND",$data["id"]
				));
			if(!($user->getState() instanceof UserWaitingForRegisteringConfirmation))
				return new ErrorResponse(403,$this->_translator->get(
					"$key/INVALID_USER_STATE"
				));
			/** @var UserWaitingForRegisteringConfirmation $state */
			$state = $user->getState();
			if(!$state->isValide(new UserConfirmationCode($data["code"])))
				return new ErrorResponse(403,$this->_translator->get(
					"$key/BAD_CONFIRM_CODE"
				));

			$this->_bus->executeCommand(new ConfirmUserRegistration(
				$data["id"],
				new UserConfirmationCode($data["code"]),
				$data["id"]
			));
			if(is_null($this->_event)) return new ErrorResponse(500,$this->_translator->get(
				"$key/USER_CONFIRMED_EVENT_NOT_RECIEVED"
			));
			if($action->getRequest()->isAjax()) return new Response();
			else $this->_notifier->addMessage(new Message($this->_translator->get(
				"$key/REGISTRATION_SUCCESS"
			)));
		}else return new Redirection("/",403);
		return new Redirection("/");
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveDomainEvent(IDomainEvent $e): void {
		if($e instanceof UserConfirmedEvent) $this->_event = $e;
	}
}