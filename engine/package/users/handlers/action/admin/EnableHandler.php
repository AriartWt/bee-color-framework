<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 27/06/18
 * Time: 15:57
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
use wfw\engine\package\users\command\EnableUsers;
use wfw\engine\package\users\domain\events\UserEnabledEvent;
use wfw\engine\package\users\handlers\action\DefaultUserActionHandler;
use wfw\engine\package\users\security\data\UserIdList;

/**
 * Permet d'active rune liste d'utilisateurs
 */
final class EnableHandler extends DefaultUserActionHandler implements IDomainEventListener{
	/** @var array $_ids */
	private $_ids;
	/** @var IJSONEncoder $_encoder */
	private $_encoder;
	/**
	 * EnableHandler constructor.
	 * @param ICommandBus $bus
	 * @param UserIdList $rule
	 * @param ISession $session
	 * @param IJSONEncoder $encoder
	 * @param IDomainEventObserver $observer
	 */
	public function __construct(
		ICommandBus $bus,
		UserIdList $rule,
		ISession $session,
		IJSONEncoder $encoder,
		IDomainEventObserver $observer
	){
		parent::__construct($bus, $rule, $session);
		$this->_encoder = $encoder;
		$this->_ids = [];
		$observer->addEventListener(UserEnabledEvent::class,$this);
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand {
		return new EnableUsers($this->_session->get("user")->getId(),...$data["ids"]);
	}

	/**
	 * @return IResponse
	 */
	protected function successResponse(): IResponse {
		return new Response($this->_encoder->jsonEncode($this->_ids));
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveEvent(IDomainEvent $e): void {
		if($e instanceof UserEnabledEvent) $this->_ids[] = (string) $e->getAggregateId();
	}
}