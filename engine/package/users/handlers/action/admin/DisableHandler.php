<?php
namespace wfw\engine\package\users\handlers\action\admin;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\core\session\ISession;
use wfw\engine\lib\data\string\json\IJSONEncoder;
use wfw\engine\package\users\command\DisableUsers;
use wfw\engine\package\users\domain\events\UserDisabledEvent;
use wfw\engine\package\users\handlers\action\admin\errors\IllegalSelfOperation;
use wfw\engine\package\users\handlers\action\DefaultUserActionHandler;
use wfw\engine\package\users\security\data\UserIdList;

/**
 * Désactive une liste d'utilisateurs
 */
final class DisableHandler extends DefaultUserActionHandler implements IDomainEventListener{
	/** @var array $_ids */
	private $_ids;
	/** @var IJSONEncoder $_encoder */
	private $_encoder;

	/**
	 * DisableUsers constructor.
	 *
	 * @param ICommandBus          $bus
	 * @param UserIdList           $rule
	 * @param ISession             $session
	 * @param IJSONEncoder         $encoder
	 * @param IDomainEventObserver $observer
	 * @param ITranslator          $translator
	 */
	public function __construct(
		ICommandBus $bus,
		UserIdList $rule,
		ISession $session,
		IJSONEncoder $encoder,
		IDomainEventObserver $observer,
		ITranslator $translator
	){
		parent::__construct($bus, $rule, $session,$translator);
		$this->_ids = [];
		$this->_encoder = $encoder;
		$observer->addDomainEventListener(UserDisabledEvent::class, $this);
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand {
		foreach($data["ids"] as $k=>$id){
			//on empêche un utilisateur de se désactiver lui même.
			if((string)$id === (string) $this->_session->get("user")->getId())
				throw new IllegalSelfOperation($this->_translator->get(
					"server/engine/package/users/USER_CANT_DISABLE_HIS_OWN_ACCOUNT"
				));
		}
		return new DisableUsers($this->_session->get("user")->getId(),...$data["ids"]);
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
	public function recieveDomainEvent(IDomainEvent $e): void {
		if($e instanceof UserDisabledEvent) $this->_ids[] = (string) $e->getAggregateId();
	}
}