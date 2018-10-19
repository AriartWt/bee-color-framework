<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/04/18
 * Time: 08:59
 */

namespace wfw\engine\package\news\domain;

use wfw\engine\core\domain\aggregate\AggregateRoot;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\news\domain\errors\ArchivingFailed;
use wfw\engine\package\news\domain\errors\PutOfflineFailed;
use wfw\engine\package\news\domain\errors\PutOnlineFailed;
use wfw\engine\package\news\domain\events\ArchivedEvent;
use wfw\engine\package\news\domain\events\ArticleWrittenEvent;
use wfw\engine\package\news\domain\events\ContentEditedEvent;
use wfw\engine\package\news\domain\events\PutOnlineEvent;
use wfw\engine\package\news\domain\events\PutOfflineEvent;
use wfw\engine\package\news\domain\events\TitleEditedEvent;
use wfw\engine\package\news\domain\events\UnarchivedEvent;
use wfw\engine\package\news\domain\events\VisualLinkEditedEvent;

/**
 * Article
 */
class Article extends AggregateRoot
{
	/** @var float $_creationDate */
	private $_creationDate;
	/** @var float $_editDate */
	private $_editDate;
	/** @var Content $_content*/
	private $_content;
	/** @var bool $_online */
	private $_online;
	/** @var Title $_title */
	private $_title;
	/** @var VisualLink $_link */
	private $_link;
	/** @var string $_author */
	private $_author;
	/** @var bool $_archived */
	private $_archived;

	/**
	 * Article constructor.
	 *
	 * @param UUID       $id
	 * @param Title      $title
	 * @param VisualLink $link
	 * @param Content    $content
	 * @param string     $authorId
	 * @param bool       $online
	 */
	public function __construct(
		UUID $id,
		Title $title,
		VisualLink $link,
		Content $content,
		string $authorId,
		bool $online=false
	){
		parent::__construct(new ArticleWrittenEvent($id,$title,$link,$content,$authorId,$online));
	}

	/**
	 * @param Title  $title Nouveau titre
	 * @param string $editorId Identifiant de l'éditeur
	 */
	public function editTitle(Title $title,string $editorId):void{
		$this->registerEvent(new TitleEditedEvent($this->getId(),$title,$editorId));
	}

	/**
	 * @param VisualLink $link Lien vers le visuel de l'article
	 * @param string     $editorId Identifiant de l'éditeur
	 */
	public function editVisual(VisualLink $link, string $editorId):void{
		$this->registerEvent(new VisualLinkEditedEvent($this->getId(),$link,$editorId));
	}

	/**
	 * @param Content $content Nouveau contenu
	 * @param string  $editorId
	 */
	public function editContent(Content $content,string $editorId):void{
		$this->registerEvent(new ContentEditedEvent($this->getId(),$content,$editorId));
	}

	/**
	 * Met l'article en ligne
	 *
	 * @param string $user Utilisateur ayant mis l'article en ligne
	 */
	public function putOnline(string $user):void{
		if($this->_online) throw new PutOnlineFailed("This article is already online !");
		$this->registerEvent(new PutOnlineEvent($this->getId(), $user));
	}

	/**
	 * Met l'article hors-ligne
	 *
	 * @param string $user Utilisateur ayant mis l'article hors ligne
	 */
	public function putOffline(string $user):void{
		if(!$this->_online) throw new PutOfflineFailed("This article is already offline !");
		$this->registerEvent(new PutOfflineEvent($this->getId(), $user));
	}

	/**
	 * @param string $user Utilisateur demandant l'archivage
	 * @throws ArchivingFailed
	 */
	public function archive(string $user):void{
		if($this->_archived) throw new ArchivingFailed("Already archived");
		$this->registerEvent(new ArchivedEvent($this->getId(),$user));
	}

	/**
	 * @param string $user Utilisateur demandant de désarchivage
	 * @throws ArchivingFailed
	 */
	public function unarchive(string $user):void{
		if(!$this->_archived) throw new ArchivingFailed("Not yet archived");
		$this->registerEvent(new UnarchivedEvent($this->getId(),$user));
	}

	/**
	 * Applique l'événement de création de l'article.
	 * @param ArticleWrittenEvent $e Evenement de création de l'article
	 */
	protected final function applyArticleWrittenEvent(ArticleWrittenEvent $e){
		$this->_title = $e->getTitle();
		$this->_link = $e->getVisualLink();
		$this->_content = $e->getContent();
		$this->_online = $e->isOnline();
		$this->_author = $e->getAuthor();
		$this->_creationDate = $e->getGenerationDate();
		$this->_archived = false;
	}

	/**
	 * Applique l'événement d'édition de titre
	 * @param TitleEditedEvent $e
	 */
	protected final function applyTitleEditedEvent(TitleEditedEvent $e){
		$this->_title = $e->getTitle();
		$this->_editDate = $e->getGenerationDate();
	}

	/**
	 * Applique l'événement d'édition du visuel de l'article
	 * @param VisualLinkEditedEvent $e
	 */
	protected final function applyVisualLinkEditedEvent(VisualLinkEditedEvent $e){
		$this->_link = $e->getVisualLink();
		$this->_editDate = $e->getGenerationDate();
	}

	/**
	 * Applique l'événement d'édition du contenu
	 * @param ContentEditedEvent $e
	 */
	protected final function applyContentEditedEvent(ContentEditedEvent $e){
		$this->_content = $e->getContent();
		$this->_editDate = $e->getGenerationDate();
	}

	/**
	 * Applique l'événement de mise en ligne
	 *
	 * @param PutOnlineEvent $e
	 */
	protected final function applyPutOnlineEvent(PutOnlineEvent $e){
		$this->_online = true;
	}

	/**
	 * Applique l'événement de mise hors ligne
	 *
	 * @param PutOfflineEvent $e
	 */
	protected final function applyPutOfflineEvent(PutOfflineEvent $e){
		$this->_online = false;
	}

	/**
	 * Applique l'événement d'archivage de l'article
	 * @param ArchivedEvent $e
	 */
	protected final function applyArchivedEvent(ArchivedEvent $e){
		$this->_archived = true;
	}

	/**
	 * Applique l'événement de désarchivage de l'article
	 * @param UnarchivedEvent $e
	 */
	protected final function applyUnarchivedEvent(UnarchivedEvent $e){
		$this->_archived = false;
	}
}