<?php
namespace wfw\engine\package\users\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\command\IQueryProcessor;
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
use wfw\engine\package\users\command\ChangeLogin;
use wfw\engine\package\users\command\ConfirmUserMailChange;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\domain\events\UserMailConfirmedEvent;
use wfw\engine\package\users\domain\Login;
use wfw\engine\package\users\domain\states\UserWaitingForEmailConfirmation;
use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;
use wfw\engine\package\users\security\data\ConfirmRule;

/**
 * permet de cofirmer un changement de mail.
 */
final class ChangeMailConfirmationHandler implements IActionHandler,IDomainEventListener{
	/** @var null|UserMailConfirmedEvent $_event */
	private $_event;
	/** @var IQueryProcessor $_bus */
	private $_bus;
	/** @var ConfirmRule $_rule */
	private $_rule;
	/** @var INotifier $_notifier */
	private $_notifier;
	/** @var IUserModelAccess $_access */
	private $_access;
	/** @var ITranslator $_translator */
	private $_translator;

	/**
	 * ChangeMailConfirmationHandler constructor.
	 *
	 * @param IQueryProcessor      $bus
	 * @param IDomainEventObserver $observer
	 * @param INotifier            $notifier
	 * @param IUserModelAccess     $access
	 * @param ITranslator          $translator
	 * @param ConfirmRule          $rule
	 */
	public function __construct(
		IQueryProcessor $bus,
		IDomainEventObserver $observer,
		INotifier $notifier,
		IUserModelAccess $access,
		ITranslator $translator,
		ConfirmRule $rule
	){
		$this->_bus = $bus;
		$this->_rule = $rule;
		$this->_access = $access;
		$this->_notifier = $notifier;
		$this->_translator = $translator;
		$observer->addDomainEventListener(UserMailConfirmedEvent::class, $this);
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		$key = "server/engine/package/users";
		$data = $action->getRequest()->getData()->get(IRequestData::GET,true);
		$report = $this->_rule->applyTo($data);
		if($report->satisfied()){
			$user = $this->_access->getById($data["id"]);
			if(is_null($user))
				return new ErrorResponse(404,$this->_translator->getAndReplace(
					"$key/USER_NOT_FOUND",$data["id"])
				);

			$state = $user->getState();
			$accordLogin = (string)$user->getLogin() === (string)$user->getEmail();
			//Si l'utilisateur avait pour nom d'utilisateur son login, on le met à jour
			//lors du changement de mail.
			if($accordLogin && $state instanceof UserWaitingForEmailConfirmation){
				$this->_bus->executeCommand(new ChangeLogin(
					$data["id"],
					new Login($state->getEmail()),
					$data["id"]
				));
			}

			//On effectue le changement de l'email.
			$this->_bus->executeCommand(new ConfirmUserMailChange(
				$data["id"],
				new UserConfirmationCode($data["code"]),
				$data["id"]
			));

			if(is_null($this->_event)) return new ErrorResponse(
				500,
				$this->_translator->get("$key/CONFIRM_MAIL_EVENT_NOT_RECIEVED")
			);

			if($action->getRequest()->isAjax()) return new Response();
			else $this->_notifier->addMessage(new Message(
				$this->_translator->get("$key/MAIL_CONFIRMED")
			));
		}else return new Redirection("/",403);
		return new Redirection("/");
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveDomainEvent(IDomainEvent $e): void {
		if($e instanceof UserMailConfirmedEvent) $this->_event = $e;
	}
}