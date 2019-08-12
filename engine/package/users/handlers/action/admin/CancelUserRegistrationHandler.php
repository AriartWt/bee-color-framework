<?php
namespace wfw\engine\package\users\handlers\action\admin;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\IQueryProcessor;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\session\ISession;
use wfw\engine\package\users\command\CancelUserRegistration;
use wfw\engine\package\users\domain\events\UserRegistrationProcedureCanceledEvent;
use wfw\engine\package\users\handlers\action\DefaultUserActionHandler;
use wfw\engine\package\users\security\data\UserIdRule;

/**
 * Annule l'enregistrement d'un utilisateur et le palce dans l'état désactivé.
 */
final class CancelUserRegistrationHandler extends DefaultUserActionHandler implements IDomainEventListener{
	/** @var null|UserRegistrationProcedureCanceledEvent */
	private $_event;

	/**
	 * CancelUserRegistrationHandler constructor.
	 *
	 * @param IQueryProcessor      $bus
	 * @param UserIdRule           $rule
	 * @param ISession             $session
	 * @param ITranslator          $translator
	 * @param IDomainEventObserver $observer
	 */
	public function __construct(
		IQueryProcessor $bus,
		UserIdRule $rule,
		ISession $session,
		ITranslator $translator,
		IDomainEventObserver $observer
	){
		parent::__construct($bus, $rule, $session, $translator);
		$observer->addDomainEventListener(
			UserRegistrationProcedureCanceledEvent::class,
			$this
		);
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand {
		return new CancelUserRegistration(
			$data["id"],
			$this->_session->get("user")->getId(),
			false
		);
	}

	/**
	 * @return IResponse
	 * @throws \InvalidArgumentException
	 */
	protected function successResponse(): IResponse {
		if(is_null($this->_event)) throw new \InvalidArgumentException(
			$this->_translator->get("server/engine/package/users/CANCEL_REGISTRATION_EVENT_NOT_RECIEVED")
		);
		return parent::successResponse();
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveDomainEvent(IDomainEvent $e): void {
		if($e instanceof UserRegistrationProcedureCanceledEvent) $this->_event = $e;
	}
}