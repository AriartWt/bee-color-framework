<?php
namespace wfw\engine\package\news\handlers\action;

use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\data\DBAccess\NOSQLDB\msServer\IMSServerAccess;
use wfw\engine\core\domain\events\IDomainEvent;
use wfw\engine\core\domain\events\IDomainEventListener;
use wfw\engine\core\domain\events\IDomainEventObserver;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\core\security\data\sanitizer\IHTMLSanitizer;
use wfw\engine\core\session\ISession;
use wfw\engine\lib\data\string\json\IJSONEncoder;
use wfw\engine\package\news\cache\NewsCacheKeys;
use wfw\engine\package\news\command\EditArticle;
use wfw\engine\package\news\data\model\ArticleModel;
use wfw\engine\package\news\domain\Content;
use wfw\engine\package\news\domain\events\ContentEditedEvent;
use wfw\engine\package\news\domain\events\TitleEditedEvent;
use wfw\engine\package\news\domain\events\VisualLinkEditedEvent;
use wfw\engine\package\news\domain\Title;
use wfw\engine\package\news\domain\VisualLink;
use wfw\engine\package\news\security\data\EditArticleRule;

/**
 * Permet l'édition d'un article
 */
final class EditHandler extends DefaultArticleActionHandler implements IDomainEventListener {
	/** @var IHTMLSanitizer $_sanitizer */
	private $_sanitizer;
	/** @var IJSONEncoder $_encoder */
	private $_encoder;
	/** @var TitleEditedEvent $_titleEvent */
	private $_titleEvent;
	/** @var ContentEditedEvent $_contentEvent */
	private $_contentEvent;
	/** @var VisualLinkEditedEvent $_visualEvent */
	private $_visualEvent;
	/** @var IMSServerAccess $_msaccess */
	private $_msaccess;
	/** @var ICacheSystem $_cache */
	private $_cache;

	/**
	 * EditArticleHandler constructor.
	 *
	 * @param ICommandBus          $bus       Bus de commandes
	 * @param ISession             $session   Session
	 * @param EditArticleRule      $rule      Régle de validation des données
	 * @param IHTMLSanitizer       $sanitizer Perifieur pour l'HTML
	 * @param IDomainEventObserver $observer
	 * @param IJSONEncoder         $encoder
	 * @param IMSServerAccess      $msaccess
	 * @param ICacheSystem         $cacheSystem
	 */
	public function __construct(
		ICommandBus $bus,
		ISession $session,
		EditArticleRule $rule,
		IHTMLSanitizer $sanitizer,
		IDomainEventObserver $observer,
		IJSONEncoder $encoder,
		IMSServerAccess $msaccess,
		ICacheSystem $cacheSystem
	) {
		parent::__construct($bus,$rule,$session);
		$this->_sanitizer = $sanitizer;
		$this->_encoder = $encoder;
		$this->_cache = $cacheSystem;
		$observer->addEventListener(TitleEditedEvent::class,$this);
		$observer->addEventListener(ContentEditedEvent::class,$this);
		$observer->addEventListener(VisualLinkEditedEvent::class, $this);
		$this->_msaccess = $msaccess;
	}

	/**
	 * @param array $data
	 * @return ICommand
	 */
	protected function createCommand(array $data): ICommand{
		return new EditArticle(
			$data['article_id'],
			$this->_session->get('user')->getId(),
			!(empty($data['title']))
				? new Title(strip_tags($data['title']))
				: null
			,
			!(empty($data['visual']))
				? new VisualLink(strip_tags($data['visual']))
				: null
			,
			!(empty($data['content']))
				? new Content($this->_sanitizer->sanitizeHTML($data['content']))
				: null
		);
	}

	/**
	 * @return IResponse
	 */
	protected function successResponse(): IResponse {
		$this->_cache->deleteAll([NewsCacheKeys::ROOT]);
		$id = null;
		if($this->_titleEvent) $id = $this->_titleEvent->getAggregateId();
		else if($this->_contentEvent) $id = $this->_contentEvent->getAggregateId();
		else if($this->_visualEvent) $id = $this->_visualEvent->getAggregateId();

		if(is_null($id) === 0) return new Response();
		return new Response($this->_encoder->jsonEncode(
			$this->_msaccess->query(ArticleModel::class,"id='$id'")[0]
		));
	}

	/**
	 * Méthode appelée lors de la reception d'un événement
	 *
	 * @param IDomainEvent $e Evenement reçu
	 */
	public function recieveEvent(IDomainEvent $e): void {
		if($e instanceof TitleEditedEvent) $this->_titleEvent = $e;
		else if($e instanceof ContentEditedEvent) $this->_contentEvent = $e;
		else if($e instanceof VisualLinkEditedEvent) $this->_visualEvent = $e;
	}
}