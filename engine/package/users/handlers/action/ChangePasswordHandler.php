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
use wfw\engine\package\users\command\ChangePassword;
use wfw\engine\package\users\data\model\DTO\User;
use wfw\engine\package\users\domain\events\UserPasswordChangedEvent;
use wfw\engine\package\users\security\data\ChangePasswordRule;

/**
 * Remet à 0 le mot de passe d'un utilisateur grâce un lien de confirmation reçu par mail
 */
final class ChangePasswordHandler extends DefaultUserActionHandler implements IDomainEventListener{
	/** @var null|UserPasswordChangedEvent $_event */
	private $_event;

	/**
	 * ChangePasswordHandler constructor.
	 *
	 * @param IQueryProcessor      $bus
	 * @param ChangePasswordRule   $rule
	 * @param ISession             $session
	 * @param IDomainEventObserver $observer
	 * @param ITranslator          $translator
	 */
	public function __construct(
		IQueryProcessor $bus,
		ChangePasswordRule $rule,
		ISession $session,
		IDomainEventObserver $observer,
		ITranslator $translator
	){
		parent::__construct($bus, $rule, $session,$translator);
		$observer->addDomainEventListener(UserPasswordChangedEvent::class, $this);
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveDomainEvent(IDomainEvent $e): void {
		if($e instanceof UserPasswordChangedEvent) $this->_event = $e;
	}

	/**
	 * @return IResponse
	 * @throws \Exception
	 */
	protected function successResponse(): IResponse {
		if(is_null($this->_event)) throw new \Exception($this->_translator->get(
			"server/engine/package/users/USER_PASSWORD_CHANGED_EVENT_NOT_RECIEVED"
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
		return new ChangePassword(
			$user->getId(),
			$data["old"],
			$data["password"],
			$user->getId()
		);
	}
}