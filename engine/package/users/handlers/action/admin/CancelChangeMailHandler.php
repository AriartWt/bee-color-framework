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
use wfw\engine\package\users\command\CancelMailChange;
use wfw\engine\package\users\domain\events\CanceledUserMailChangeEvent;
use wfw\engine\package\users\handlers\action\DefaultUserActionHandler;
use wfw\engine\package\users\security\data\UserIdRule;

/**
 * Annule la procédure de changement d'email d'un utilisateur.
 */
final class CancelChangeMailHandler extends DefaultUserActionHandler implements IDomainEventListener{
	/** @var null|CanceledUserMailChangeEvent $_event */
	private $_event;

	/**
	 * CancelChangeMailHandler constructor.
	 *
	 * @param IQueryProcessor      $bus
	 * @param UserIdRule           $rule
	 * @param ISession             $session
	 * @param IDomainEventObserver $observer
	 * @param ITranslator          $translator
	 */
	public function __construct(
		IQueryProcessor $bus,
		UserIdRule $rule,
		ISession $session,
		IDomainEventObserver $observer,
		ITranslator $translator
	){
		parent::__construct($bus, $rule, $session, $translator);
		$observer->addDomainEventListener(CanceledUserMailChangeEvent::class, $this);
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand {
		return new CancelMailChange(
			$data["id"],
			$this->_session->get("user")->getId()
		);
	}

	/**
	 * @return IResponse
	 * @throws \InvalidArgumentException
	 */
	protected function successResponse(): IResponse {
		if(is_null($this->_event)) throw new \InvalidArgumentException(
			$this->_translator->get("server/engine/package/users/CANCEL_MAIL_CHANGE_EVENT_NOT_RECIEVED")
		);
		return parent::successResponse();
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveDomainEvent(IDomainEvent $e): void {
		if($e instanceof CanceledUserMailChangeEvent) $this->_event = $e;
	}
}