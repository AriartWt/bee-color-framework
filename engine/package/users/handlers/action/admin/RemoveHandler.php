<?php
namespace wfw\engine\package\users\handlers\action\admin;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\IQueryProcessor;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\core\session\ISession;
use wfw\engine\lib\data\string\json\IJSONEncoder;
use wfw\engine\package\users\command\RemoveUsers;
use wfw\engine\package\users\domain\events\UserRemovedEvent;
use wfw\engine\package\users\handlers\action\admin\errors\IllegalSelfOperation;
use wfw\engine\package\users\handlers\action\DefaultUserActionHandler;
use wfw\engine\package\users\security\data\UserIdList;

/**
 * Supprime une liste d'utilisateurs
 */
final class RemoveHandler extends DefaultUserActionHandler implements IDomainEventListener{
	/** @var IJSONEncoder $_encoder */
	private $_encoder;
	/** @var array $_ids */
	private $_ids;

	/**
	 * RemoveHandler constructor.
	 *
	 * @param IQueryProcessor      $bus
	 * @param UserIdList           $rule
	 * @param ISession             $session
	 * @param IDomainEventObserver $observer
	 * @param IJSONEncoder         $encoder
	 * @param ITranslator          $translator
	 */
	public function __construct(
		IQueryProcessor $bus,
		UserIdList $rule,
		ISession $session,
		IDomainEventObserver $observer,
		IJSONEncoder $encoder,
		ITranslator $translator
	){
		parent::__construct($bus, $rule, $session, $translator);
		$this->_encoder = $encoder;
		$this->_ids = [];
		$observer->addDomainEventListener(UserRemovedEvent::class, $this);
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand {
		foreach($data["ids"] as $k=>$id){
			//on empêche un utilisateur de se supprimer lui même.
			if((string)$id === (string) $this->_session->get("user")->getId())
				throw new IllegalSelfOperation(
					$this->_translator->get("server/engine/package/users/USER_CANT_DELETE_HIS_OWN_ACCOUNT")
				);
		}
		return new RemoveUsers($this->_session->get("user")->getId(),...$data["ids"]);
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
		if($e instanceof UserRemovedEvent) $this->_ids[] = (string) $e->getAggregateId();
	}
}