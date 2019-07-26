<?php
namespace wfw\engine\package\contact\handlers\action;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\core\session\ISession;
use wfw\engine\lib\data\string\json\IJSONEncoder;
use wfw\engine\package\contact\command\MarkContactsAsUnread;
use wfw\engine\package\contact\security\data\ContactIdListRule;
use wfw\engine\package\contact\domain\events\MarkedAsUnreadEvent;

/**
 * Permet d'archiver une liste de prises de contact
 */
final class MarkAsUnreadHandler extends DefaultContactActionHandler implements IDomainEventListener{
	/** @var IJSONEncoder $_encoder */
	private $_encoder;
	/** @var array $_ids */
	private $_ids;
	/**
	 * ArchiveHandler constructor.
	 *
	 * @param ICommandBus       $bus      Bus de commandes
	 * @param ISession          $session  Session
	 * @param ContactIdListRule $rule     Régle de validation des données
	 * @param IDomainEventObserver $observer Observeur de DomainEventListeners
	 * @param IJSONEncoder         $encoder  Encodeur JSON
	 */
	public function __construct(
		ICommandBus $bus,
		ISession $session,
		ContactIdListRule $rule,
		IDomainEventObserver $observer,
		IJSONEncoder $encoder
	) {
		parent::__construct($bus, $rule, $session);
		$this->_ids = [];
		$this->_encoder = $encoder;
		$observer->addDomainEventListener(MarkedAsUnreadEvent::class, $this);
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand {
		return new MarkContactsAsUnread($this->_session->get('user')->getId(),...$data["ids"]);
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 *
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveDomainEvent(IDomainEvent $e): void {
		if($e instanceof MarkedAsUnreadEvent) $this->_ids[] = (string) $e->getAggregateId();
	}

	/**
	 * @return IResponse
	 */
	protected function successResponse(): IResponse{
		return new Response($this->_encoder->jsonEncode($this->_ids));
	}
}