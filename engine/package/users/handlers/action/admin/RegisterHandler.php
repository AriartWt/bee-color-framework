<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/06/18
 * Time: 14:56
 */

namespace wfw\engine\package\users\handlers\action\admin;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\core\session\ISession;
use wfw\engine\lib\data\string\json\IJSONEncoder;
use wfw\engine\package\general\domain\Email;
use wfw\engine\package\users\command\RegisterUser;
use wfw\engine\package\users\data\model\IUserModelAccess;
use wfw\engine\package\users\domain\events\UserRegisteredEvent;
use wfw\engine\package\users\domain\Login;
use wfw\engine\package\users\domain\Password;
use wfw\engine\package\users\domain\settings\InMemoryUserSettings;
use wfw\engine\package\users\domain\states\DisabledUser;
use wfw\engine\package\users\domain\types\Admin;
use wfw\engine\package\users\domain\types\Basic;
use wfw\engine\package\users\domain\types\Client;
use wfw\engine\package\users\handlers\action\DefaultUserActionHandler;
use wfw\engine\package\users\security\data\RegisterUserRule;

/**
 * Enregistre un nouvel utilisateur
 */
final class RegisterHandler extends DefaultUserActionHandler implements IDomainEventListener{
	/** @var null|UserRegisteredEvent $_userRegisteredEvent */
	private $_userRegisteredEvent;
	/** @var IUserModelAccess $_access */
	private $_access;
	/** @var IJSONEncoder $_encoder */
	private $_encoder;

	/**
	 * RegisterHandler constructor.
	 * @param ICommandBus $bus
	 * @param RegisterUserRule $rule
	 * @param ISession $session
	 * @param IJSONEncoder $encoder
	 * @param IDomainEventObserver $observer
	 * @param IUserModelAccess $access
	 */
	public function __construct(
		ICommandBus $bus,
		RegisterUserRule $rule,
		ISession $session,
		IJSONEncoder $encoder,
		IDomainEventObserver $observer,
		IUserModelAccess $access
	){
		parent::__construct($bus, $rule, $session);
		$this->_encoder = $encoder;
		$this->_access = $access;
		$observer->addEventListener(UserRegisteredEvent::class,$this);
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
		return new RegisterUser(
			new Login($data["login"]),
			new Password($data["password"]),
			new Email($data["email"]),
			$type,
			$this->_session->get("user")->getId(),
			new InMemoryUserSettings(),
			new DisabledUser(),
			false
		);
	}

	/**
	 * @return IResponse
	 * @throws \Exception
	 */
	protected function successResponse(): IResponse {
		if(is_null($this->_userRegisteredEvent)) throw new \Exception(
			"UserRegisteredEvent not recieved !"
		);
		return new Response($this->_encoder->jsonEncode(
			$this->_access->getById($this->_userRegisteredEvent->getAggregateId())
		));
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveEvent(IDomainEvent $e): void {
		if($e instanceof UserRegisteredEvent) $this->_userRegisteredEvent = $e;
	}
}