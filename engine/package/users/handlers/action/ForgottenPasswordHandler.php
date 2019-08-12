<?php
namespace wfw\engine\package\users\handlers\action;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\IQueryProcessor;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\lang\ITranslator;
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
	 *
	 * @param IQueryProcessor      $bus
	 * @param RetrievePasswordRule $rule
	 * @param ISession             $session
	 * @param IDomainEventObserver $observer
	 * @param IUserModelAccess     $access
	 * @param ITranslator          $translator
	 */
	public function __construct(
		IQueryProcessor $bus,
		RetrievePasswordRule $rule,
		ISession $session,
		IDomainEventObserver $observer,
		IUserModelAccess $access,
		ITranslator $translator
	){
		parent::__construct($bus, $rule, $session,$translator);
		$observer->addDomainEventListener(AskedForPasswordRetrievingEvent::class, $this);
		$this->_access = $access;
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveDomainEvent(IDomainEvent $e): void {
		if($e instanceof AskedForPasswordRetrievingEvent) $this->_event = $e;
	}

	/**
	 * @return IResponse
	 * @throws \Exception
	 */
	protected function successResponse(): IResponse {
		if(is_null($this->_event)) throw new \Exception($this->_translator->get(
			"server/engine/package/users/ASKED_FOR_PASSWORD_RETRIEVING_EVENT_NOT_RECIEVED"
		));
		return parent::successResponse();
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand {
		$user = $this->_access->getByLogin($data["login"]);
		if(is_null($user)) throw new DataError(
			$this->_translator->getAndReplace(
				"server/engine/package/users/NOT_FOUND",$data["login"]
			));
		return new RetrievePassword(
			$user->getId(),
			$user->getId()
		);
	}
}