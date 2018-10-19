<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/06/18
 * Time: 14:34
 */

namespace wfw\engine\package\users\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
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
use wfw\engine\package\users\domain\states\EnabledUser;
use wfw\engine\package\users\domain\states\UserWaitingForEmailConfirmation;
use wfw\engine\package\users\lib\confirmationCode\UserConfirmationCode;
use wfw\engine\package\users\security\data\ConfirmRule;

/**
 * permet de cofirmer un changement de mail.
 */
final class ChangeMailConfirmationHandler implements IActionHandler,IDomainEventListener{
	/** @var null|UserMailConfirmedEvent $_event */
	private $_event;
	/** @var ICommandBus $_bus */
	private $_bus;
	/** @var ConfirmRule $_rule */
	private $_rule;
	/** @var INotifier $_notifier */
	private $_notifier;
	/** @var IUserModelAccess $_access */
	private $_access;

	/**
	 * ChangeMailConfirmationHandler constructor.
	 * @param ICommandBus $bus
	 * @param IDomainEventObserver $observer
	 * @param INotifier $notifier
	 * @param IUserModelAccess $access
	 * @param ConfirmRule $rule
	 */
	public function __construct(
		ICommandBus $bus,
		IDomainEventObserver $observer,
		INotifier $notifier,
		IUserModelAccess $access,
		ConfirmRule $rule
	){
		$this->_bus = $bus;
		$this->_rule = $rule;
		$this->_access = $access;
		$this->_notifier = $notifier;
		$observer->addEventListener(UserMailConfirmedEvent::class,$this);
	}

	/**
	 * @param IAction $action Action
	 * @return IResponse Réponse
	 */
	public function handle(IAction $action): IResponse {
		$data = $action->getRequest()->getData()->get(IRequestData::GET,true);
		$report = $this->_rule->applyTo($data);
		if($report->satisfied()){
			$user = $this->_access->getById($data["id"]);
			if(is_null($user))
				return new ErrorResponse(404,"User ".$data["id"]." not found");

			$state = $user->getState();
			$accordLogin = (string)$user->getLogin() === (string)$user->getEmail();
			//Si l'utilisateur avait pour nom d'utilisateur son login, on le met à jour
			//lors du changement de mail.
			if($accordLogin && $state instanceof UserWaitingForEmailConfirmation){
				$this->_bus->execute(new ChangeLogin(
					$data["id"],
					new Login($state->getEmail()),
					$data["id"]
				));
			}

			//On effectue le changement de l'email.
			$this->_bus->execute(new ConfirmUserMailChange(
				$data["id"],
				new UserConfirmationCode($data["code"]),
				$data["id"]
			));

			if(is_null($this->_event)) return new ErrorResponse(
				500,
				"UserMailConfirmedEvent not recieved"
			);

			if($action->getRequest()->isAjax()) return new Response();
			else $this->_notifier->addMessage(new Message(
				"Votre mail a été confirmé avec succès !"
			));
		}else return new Redirection("/",403);
		return new Redirection("/");
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveEvent(IDomainEvent $e): void {
		if($e instanceof UserMailConfirmedEvent) $this->_event = $e;
	}
}