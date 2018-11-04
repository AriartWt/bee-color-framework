<?php
namespace wfw\engine\package\users\handlers\action;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\session\ISession;
use wfw\engine\package\general\handlers\action\errors\DataError;
use wfw\engine\package\users\command\RetrievePassword;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\domain\events\AskedForPasswordRetrievingEvent;
use wfw\engine\package\users\security\data\RetrievePasswordRule;

/**
 * Permet à un utilisateur de retrovuer son mot de passe.
 */
final class ForgottenPasswordHandler extends DefaultUserActionHandler implements IDomainEventListener{
	/** @var null|AskedForPasswordRetrievingEvent $_event */
	private $_event;
	/** @var IUserModelAccess $_access */
	private $_access;

	/**
	 * ForgottenPasswordHandler constructor.
	 * @param ICommandBus $bus
	 * @param RetrievePasswordRule $rule
	 * @param ISession $session
	 * @param IDomainEventObserver $observer
	 * @param IUserModelAccess $access
	 */
	public function __construct(
		ICommandBus $bus,
		RetrievePasswordRule $rule,
		ISession $session,
		IDomainEventObserver $observer,
		IUserModelAccess $access
	){
		parent::__construct($bus, $rule, $session);
		$observer->addEventListener(AskedForPasswordRetrievingEvent::class,$this);
		$this->_access = $access;
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveEvent(IDomainEvent $e): void {
		if($e instanceof AskedForPasswordRetrievingEvent) $this->_event = $e;
	}

	/**
	 * @return IResponse
	 * @throws \Exception
	 */
	protected function successResponse(): IResponse {
		if(is_null($this->_event)) throw new \Exception(
			"AskedForPasswordRetrievingEvent not recieved !"
		);
		return parent::successResponse();
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand {
		$user = $this->_access->getByLogin($data["login"]);
		if(is_null($user)) throw new DataError(
			"L'utilisateur ".$data["login"]." n'a pas été trouvé !"
		);
		return new RetrievePassword(
			$user->getId(),
			$user->getId()
		);
	}
}