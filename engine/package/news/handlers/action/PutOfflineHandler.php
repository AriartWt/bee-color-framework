<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 27/04/18
 * Time: 11:25
 */

namespace wfw\engine\package\news\handlers\action;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\core\session\ISession;
use wfw\engine\lib\data\string\json\IJSONEncoder;
use wfw\engine\package\news\command\PutArticlesOffline;
use wfw\engine\package\news\domain\events\PutOfflineEvent;
use wfw\engine\package\news\security\data\ArticleIdListRule;

/**
 * Met un article hors ligne
 */
final class PutOfflineHandler extends DefaultArticleActionHandler implements IDomainEventListener
{
	/** @var string[] $_ids */
	private $_ids;
	/** @var IJSONEncoder $_encoder */
	private $_encoder;

	/**
	 * PutOfflineHandler constructor.
	 *
	 * @param ICommandBus          $bus     Bus du commandes
	 * @param ISession             $session Session
	 * @param ArticleIdListRule    $rule    Régle de validation des données
	 * @param IDomainEventObserver $observer
	 * @param IJSONEncoder         $encoder
	 */
	public function __construct(
		ICommandBus $bus,
		ISession $session,
		ArticleIdListRule $rule,
		IDomainEventObserver $observer,
		IJSONEncoder $encoder
	){
		parent::__construct($bus, $rule, $session);
		$this->_ids = [];
		$this->_encoder = $encoder;
		$observer->addEventListener(PutOfflineEvent::class,$this);
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand{
		return new PutArticlesOffline($this->_session->get('user')->getId(),...$data['ids']);
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 *
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveEvent(IDomainEvent $e): void{
		if($e instanceof PutOfflineEvent) $this->_ids[] = (string) $e->getAggregateId();
	}

	/**
	 * @return IResponse
	 */
	protected function successResponse(): IResponse{
		return new Response($this->_encoder->jsonEncode($this->_ids));
	}
}