<?php
namespace wfw\engine\package\users\handlers\action\admin;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\session\ISession;
use wfw\engine\package\users\command\RetrievePassword;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\domain\events\UserPasswordResetedEvent;
use wfw\engine\package\users\domain\Password;
use wfw\engine\package\users\handlers\action\DefaultUserActionHandler;
use wfw\engine\package\users\security\data\PasswordResetRule;

/**
 * Permet de changer le mot de passe d'un autre utilisateur
 */
final class ResetPasswordHandler extends DefaultUserActionHandler implements IDomainEventListener{
	/** @var null|UserPasswordResetedEvent $_event */
	private $_event;
	/** @var IUserModelAccess $_access */
	private $_access;

	/**
	 * ResetPassword constructor.
	 * @param ICommandBus $bus
	 * @param PasswordResetRule $rule
	 * @param ISession $session
	 * @param IDomainEventObserver $observer
	 * @param IUserModelAccess $access
	 */
	public function __construct(
		ICommandBus $bus,
		PasswordResetRule $rule,
		ISession $session,
		IDomainEventObserver $observer,
		IUserModelAccess $access
	){
		parent::__construct($bus,$rule, $session);
		$this->_access = $access;
		$observer->addEventListener(UserPasswordResetedEvent::class,$this);
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveEvent(IDomainEvent $e): void {
		if($e instanceof UserPasswordResetedEvent) $this->_event = $e;
	}

	/**
	 * @return IResponse
	 * @throws \Exception
	 */
	protected function successResponse(): IResponse {
		if(is_null($this->_event)) throw new \Exception(
			"UserPasswordResetedEvent not recieved !"
		);
		return parent::successResponse();
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand {
		$user = $this->_access->getById($data["id"]);
		return new RetrievePassword(
			$data["id"],
			$this->_session->get("user")->getId(),
			new Password($data["password"]),
			$user ? $user->getState() : null
		);
	}
}