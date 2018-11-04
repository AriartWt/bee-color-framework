<?php
namespace wfw\engine\package\users\handlers\action\admin;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\session\ISession;
use wfw\engine\package\users\command\CancelPasswordRetrieving;
use wfw\engine\package\users\domain\events\UserPasswordRetrievingCanceledEvent;
use wfw\engine\package\users\handlers\action\DefaultUserActionHandler;
use wfw\engine\package\users\security\data\UserIdRule;

/**
 * Applique la commande d'annulation d'une procédure de récupèration de mot de passe.
 */
final class CancelResetPasswordHandler extends DefaultUserActionHandler implements IDomainEventListener{
	/** @var null|UserPasswordRetrievingCanceledEvent $_event */
	private $_event;

	/**
	 * CancelResetPasswordHandler constructor.
	 * @param ICommandBus $bus
	 * @param UserIdRule $rule
	 * @param ISession $session
	 * @param IDomainEventObserver $observer
	 */
	public function __construct(
		ICommandBus $bus,
		UserIdRule $rule,
		ISession $session,
		IDomainEventObserver $observer
	){
		parent::__construct($bus, $rule, $session);
		$observer->addEventListener(
			UserPasswordRetrievingCanceledEvent::class,
			$this
		);
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveEvent(IDomainEvent $e): void {
		if($e instanceof UserPasswordRetrievingCanceledEvent) $this->_event = $e;
	}

	/**
	 * @return IResponse
	 * @throws \InvalidArgumentException
	 */
	protected function successResponse(): IResponse {
		if(is_null($this->_event)) throw new \InvalidArgumentException(
			"UserPasswordRetrievingCanceledEvent not recieved !"
		);
		return parent::successResponse();
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand {
		return new CancelPasswordRetrieving(
			$data["id"],
			$this->_session->get('user')->getId()
		);
	}
}