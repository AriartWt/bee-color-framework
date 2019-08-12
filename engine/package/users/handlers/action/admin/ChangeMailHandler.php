<?php
namespace wfw\engine\package\users\handlers\action\admin;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\session\ISession;
use wfw\engine\package\general\command\MultiCommand;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\command\ChangeLogin;
use wfw\engine\package\users\command\ChangeUserMail;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\domain\events\UserMailConfirmedEvent;
use wfw\engine\package\users\domain\Login;
use wfw\engine\package\users\domain\states\UserWaitingForEmailConfirmation;
use wfw\engine\package\users\handlers\action\DefaultUserActionHandler;
use wfw\engine\package\users\security\data\UserMailRule;

/**
 * Change le mail d'un utilisateur sans demande de confirmation
 */
final class ChangeMailHandler extends DefaultUserActionHandler implements IDomainEventListener{
	/** @var null|UserMailConfirmedEvent $_event */
	private $_event;
	/** @var IUserModelAccess $_access */
	private $_access;

	/**
	 * ChangeMailHandler constructor.
	 *
	 * @param ICommandBus          $bus
	 * @param UserMailRule         $rule
	 * @param ISession             $session
	 * @param IDomainEventObserver $observer
	 * @param IUserModelAccess     $access
	 * @param ITranslator          $translator
	 */
	public function __construct(
		ICommandBus $bus,
		UserMailRule $rule,
		ISession $session,
		IDomainEventObserver $observer,
		IUserModelAccess $access,
		ITranslator $translator
	){
		parent::__construct($bus, $rule, $session,$translator);
		$this->_access = $access;
		$observer->addDomainEventListener(UserMailConfirmedEvent::class, $this);
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveDomainEvent(IDomainEvent $e): void {
		if($e instanceof UserMailConfirmedEvent) $this->_event = $e;
	}

	/**
	 * @return IResponse
	 * @throws \InvalidArgumentException
	 */
	protected function successResponse(): IResponse {
		if(is_null($this->_event)) throw new \InvalidArgumentException(
			$this->_translator->get("server/engine/package/users/CONFIRM_MAIL_EVENT_NOT_RECIEVED")
		);
		return parent::successResponse();
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand {
		$user = $this->_access->getById($data["id"]);
		$changeMailCommand =new ChangeUserMail(
			$data["id"],
			new Email($data["email"]),
			$this->_session->get('user')->getId(),
			false,
			$user->getState() instanceof UserWaitingForEmailConfirmation ? null : $user->getState()
		);
		if((string)$user->getLogin() === (string)$user->getEmail()){
			return new MultiCommand(
				new ChangeLogin(
					$data["id"],
					new Login($data["email"]),
					$this->_session->get('user')->getId()
				),
				$changeMailCommand
			);
		}else return $changeMailCommand;
	}
}