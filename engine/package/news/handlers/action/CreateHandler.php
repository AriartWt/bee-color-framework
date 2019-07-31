<?php
namespace wfw\engine\package\news\handlers\action;

use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\data\DBAccess\NOSQLDB\msServer\IMSServerAccess;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\core\security\data\sanitizer\IHTMLSanitizer;
use wfw\engine\core\session\ISession;
use wfw\engine\lib\data\string\json\IJSONEncoder;
use wfw\engine\package\news\cache\NewsCacheKeys;
use wfw\engine\package\news\command\CreateArticle;
use wfw\engine\package\news\data\model\ArticleModel;
use wfw\engine\package\news\domain\Content;
use wfw\engine\package\news\domain\events\ArticleWrittenEvent;
use wfw\engine\package\news\domain\Title;
use wfw\engine\package\news\domain\VisualLink;
use wfw\engine\package\news\security\data\CreateArticleRule;

/**
 * Crée un article
 */
final class CreateHandler extends DefaultArticleActionHandler implements IDomainEventListener {
	/** @var IHTMLSanitizer $_sanitizer */
	private $_sanitizer;
	/** @var ArticleWrittenEvent $_creationEvent */
	private $_creationEvent;
	/** @var IJSONEncoder $_encoder */
	private $_encoder;
	/** @var IMSServerAccess $_msclient */
	private $_msclient;
	/** @var ICacheSystem $_cache */
	private $_cache;
	/** @var ITranslator $_translator */
	private $_translator;

	/**
	 * CreateArticleHandler constructor.
	 *
	 * @param ICommandBus          $commandBus Bus de commandes.
	 * @param ISession             $session    Session
	 * @param CreateArticleRule    $rule       Régle de validation des données
	 * @param IHTMLSanitizer       $sanitizer
	 * @param IDomainEventObserver $observer
	 * @param IJSONEncoder         $encoder
	 * @param IMSServerAccess      $access
	 * @param ICacheSystem         $cache
	 * @param ITranslator          $translator
	 */
	public function __construct(
		ICommandBus $commandBus,
		ISession $session,
		CreateArticleRule $rule,
		IHTMLSanitizer $sanitizer,
		IDomainEventObserver $observer,
		IJSONEncoder $encoder,
		IMSServerAccess $access,
		ICacheSystem $cache,
		ITranslator $translator
	) {
		parent::__construct($commandBus,$rule,$session,$translator);
		$this->_translator = $translator;
		$this->_sanitizer = $sanitizer;
		$this->_encoder = $encoder;
		$this->_msclient = $access;
		$this->_cache = $cache;
		$observer->addDomainEventListener(ArticleWrittenEvent::class, $this);
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand{
		return new CreateArticle(
			new Title(strip_tags($data['title'])),
			new VisualLink(strip_tags($data['visual'])),
			new Content($this->_sanitizer->sanitizeHTML($data['content'])),
			$this->_session->get('user')->getId(),
			$data['online']??false
		);
	}

	/**
	 * @return IResponse
	 */
	protected function successResponse(): IResponse {
		$this->_cache->deleteAll([NewsCacheKeys::ROOT]);
		if(is_null($this->_creationEvent)) throw new \Exception($this->_translator->get(
			"server/engine/package/news/forms/ERROR_WRITTEN_EVENT_NOT_RECIEVED"
		));
		return new Response($this->_encoder->jsonEncode(
			$this->_msclient->query(
				ArticleModel::class,
				"id='{$this->_creationEvent->getAggregateId()}'"
			)[0]
		));
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 *
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveDomainEvent(IDomainEvent $e): void{
		$this->_creationEvent = $e;
	}
}