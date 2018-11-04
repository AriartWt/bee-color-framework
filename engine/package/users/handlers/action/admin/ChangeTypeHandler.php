<?php
namespace wfw\engine\package\users\handlers\action\admin;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\session\ISession;
use wfw\engine\package\users\command\ChangeType;
use wfw\engine\package\users\domain\events\UserTypeChangedEvent;
use wfw\engine\package\users\domain\types\Admin;
use wfw\engine\package\users\domain\types\Basic;
use wfw\engine\package\users\domain\types\Client;
use wfw\engine\package\users\handlers\action\DefaultUserActionHandler;
use wfw\engine\package\users\security\data\ChangeUserTypeRule;

/**
 * Permet de changer le type d'un utilisateur
 */
final class ChangeTypeHandler extends DefaultUserActionHandler implements IDomainEventListener{
	/** @var null|UserTypeChangedEvent $_event */
	private $_event;

	/**
	 * ChangeTypeHandler constructor.
	 * @param ICommandBus $bus
	 * @param ChangeUserTypeRule $rule
	 * @param ISession $session
	 * @param IDomainEventObserver $observer
	 */
	public function __construct(
		ICommandBus $bus,
		ChangeUserTypeRule $rule,
		ISession $session,
		IDomainEventObserver $observer
	){
		parent::__construct($bus, $rule, $session);
		$observer->addEventListener(UserTypeChangedEvent::class,$this);
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveEvent(IDomainEvent $e): void {
		if($e instanceof UserTypeChangedEvent) $this->_event = $e;
	}

	/**
	 * @return IResponse
	 * @throws \InvalidArgumentException
	 */
	protected function successResponse(): IResponse {
		if(is_null($this->_event)) throw new \InvalidArgumentException(
			"UserTypeChangedEvent not recieved !"
		);
		return parent::successResponse();
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand {
		switch($data["type"]){
			case "admin" :
				$type = new Admin(); break;
			case "client" :
				$type = new Client(); break;
			default :
				$type = new Basic(); break;
		}
		return new ChangeType(
			$data["id"],
			$type,
			$this->_session->get("user")->getId()
		);
	}
}