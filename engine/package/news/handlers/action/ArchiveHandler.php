<?php
namespace wfw\engine\package\news\handlers\action;

use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\core\session\ISession;
use wfw\engine\lib\data\string\json\IJSONEncoder;
use wfw\engine\package\news\cache\NewsCacheKeys;
use wfw\engine\package\news\command\ArchiveArticles;
use wfw\engine\package\news\domain\events\ArchivedEvent;
use wfw\engine\package\news\security\data\ArticleIdListRule;

/**
 * Archive un article
 */
final class ArchiveHandler extends DefaultArticleActionHandler implements IDomainEventListener {
	/** @var string[] $_ids */
	private $_ids;
	/** @var IJSONEncoder $_encoder */
	private $_encoder;
	/** @var ICacheSystem $_cache */
	private $_cache;

	/**
	 * ArchiveHandler constructor.
	 *
	 * @param ICommandBus          $bus     Bus du commandes
	 * @param ISession             $session Session
	 * @param ArticleIdListRule    $rule    Régle de validation des données
	 * @param IDomainEventObserver $observer
	 * @param IJSONEncoder         $encoder
	 * @param ICacheSystem         $cache
	 */
	public function __construct(
		ICommandBus $bus,
		ISession $session,
		ArticleIdListRule $rule,
		IDomainEventObserver $observer,
		IJSONEncoder $encoder,
		ICacheSystem $cache
	){
		parent::__construct($bus, $rule, $session);
		$this->_ids = [];
		$this->_cache = $cache;
		$this->_encoder = $encoder;
		$observer->addEventListener(ArchivedEvent::class,$this);
	}
	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand{
		return new ArchiveArticles($this->_session->get('user')->getId(),...$data['ids']);
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 *
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveEvent(IDomainEvent $e): void{
		if($e instanceof ArchivedEvent) $this->_ids[] = (string) $e->getAggregateId();
	}

	/**
	 * @return IResponse
	 */
	protected function successResponse(): IResponse{
		$this->_cache->deleteAll([NewsCacheKeys::ROOT]);
		return new Response($this->_encoder->jsonEncode($this->_ids));
	}
}