<?php
namespace wfw\engine\package\users\handlers\action;


use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\session\ISession;
use wfw\engine\package\general\command\MultiCommand;
use wfw\engine\package\users\command\CancelMailChange;
use wfw\engine\package\users\command\ChangeUserMail;
use wfw\engine\package\users\data\model\DTO\User;
use wfw\engine\package\users\domain\events\AskedForEmailChangeEvent;
use wfw\engine\package\users\security\data\ChangeMailRule;

/**
 * Réenvoi le mail permettant à un utilisateur de confirmer son changement d'email.
 * Necessite une authentification
 */
final class ResendChangeMailHandler extends DefaultUserActionHandler implements IDomainEventListener {
	/** @var null|AskedForEmailChangeEvent $_event */
	private $_event;

	/**
	 * ResendChangeMailMailHandler constructor.
	 * @param ICommandBus $bus
	 * @param ChangeMailRule $rule
	 * @param ISession $session
	 * @param IDomainEventObserver $observer
	 */
	public function __construct(
		ICommandBus $bus,
		ChangeMailRule $rule,
		ISession $session,
		IDomainEventObserver $observer
	){
		parent::__construct($bus, $rule, $session);
		$observer->addEventListener(AskedForEmailChangeEvent::class,$this);
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveEvent(IDomainEvent $e): void {
		if($e instanceof AskedForEmailChangeEvent) $this->_event = $e;
	}

	/**
	 * @return IResponse
	 * @throws \Exception
	 */
	protected function successResponse(): IResponse {
		if(is_null($this->_event)) throw new \Exception(
			"AskedForEmailChangeEvent not recieved !"
		);
		return parent::successResponse();
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand {
		/** @var User $user */
		$user = $this->_session->get("user");
		return new MultiCommand(
			new CancelMailChange(
				$user->getId(),$user->getId()
			),
			new ChangeUserMail(
				$user->getId(),
				$data["email"],
				$user->getId()
			)
		);
	}
}