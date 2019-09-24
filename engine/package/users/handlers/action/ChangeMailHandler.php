<?php
namespace wfw\engine\package\users\handlers\action;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\session\ISession;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\command\ChangeUserMail;
use wfw\engine\package\users\data\model\DTO\User;
use wfw\engine\package\users\domain\events\AskedForEmailChangeEvent;
use wfw\engine\package\users\security\data\ChangeMailRule;

/**
 * Permet à un utilisateur de changer son mot adresse mail.
 * Requiert une authentification.
 */
final class ChangeMailHandler extends DefaultUserActionHandler implements IDomainEventListener{
	/** @var null|AskedForEmailChangeEvent $_event */
	private $_event;

	/**
	 * ChangeMailHandler constructor.
	 *
	 * @param ICommandBus          $bus
	 * @param ChangeMailRule       $rule
	 * @param ISession             $session
	 * @param IDomainEventObserver $observer
	 * @param ITranslator          $translator
	 */
	public function __construct(
		ICommandBus $bus,
		ChangeMailRule $rule,
		ISession $session,
		IDomainEventObserver $observer,
		ITranslator $translator
	){
		parent::__construct($bus, $rule, $session,$translator);
		$observer->addDomainEventListener(AskedForEmailChangeEvent::class, $this);
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveDomainEvent(IDomainEvent $e): void {
		if($e instanceof AskedForEmailChangeEvent) $this->_event = $e;
	}

	/**
	 * @return IResponse
	 * @throws \Exception
	 */
	protected function successResponse(): IResponse {
		if(is_null($this->_event)) throw new \Exception($this->_translator->get(
			"server/engine/package/users/ASKED_FOR_MAIL_CHANGE_EVENT_NOT_RECIEVED"
		));
		return parent::successResponse();
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand {
		/** @var User $user */
		$user = $this->_session->get('user');
		return new ChangeUserMail(
			$user->getId(),
			new Email($data["email"]),
			$user->getId()
		);
	}
}