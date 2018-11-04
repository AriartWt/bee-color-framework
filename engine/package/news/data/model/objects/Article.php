<?php
namespace wfw\engine\package\news\data\model\objects;

use wfw\engine\core\data\model\DTO\IDTO;
use wfw\engine\core\data\model\IModelObject;
use wfw\engine\core\data\model\ModelObject;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\news\domain\Content;
use wfw\engine\package\news\domain\Title;
use wfw\engine\package\news\domain\VisualLink;

/**
 * Article
 */
class Article extends ModelObject {
	public const TITLE = "title";
	public const VISUAL = "visual";
	public const CONTENT = "content";
	public const ONLINE = "online";
	public const OFFLINE = "offline";
	public const ARCHIVED = "archived";
	public const UNARCHIVED = "unarchived";

	/** @var Title $_title */
	private $_title;
	/** @var VisualLink $_link */
	private $_link;
	/** @var Content $_content */
	private $_content;
	/** @var string $_author */
	private $_author;
	/** @var float $_creationDate */
	private $_creationDate;
	/** @var bool $_online */
	private $_online;
	/** @var array $_editions */
	private $_editions;
	/** @var bool $_archived */
	private $_archived;

	/**
	 * Article constructor.
	 *
	 * @param UUID       $id
	 * @param Title      $title
	 * @param VisualLink $link
	 * @param Content    $content
	 * @param string     $author
	 * @param float      $creationDate
	 * @param bool       $online
	 */
	public function __construct(
		UUID $id,
		Title $title,
		VisualLink $link,
		Content $content,
		string $author,
		float $creationDate,
		bool $online
	){
		parent::__construct($id);
		$this->_title = $title;
		$this->_link = $link;
		$this->_content = $content;
		$this->_author = $author;
		$this->_creationDate = $creationDate;
		$this->_online = $online;
		$this->_editions = [];
		$this->_archived = false;
	}

	/**
	 * @param Title  $title
	 */
	public function setTitle(Title $title): void {
		$this->_title = $title;
	}

	/**
	 * @param Content $content
	 */
	public function setContent(Content $content): void {
		$this->_content = $content;
	}

	/**
	 * @param VisualLink $link
	 */
	public function setVisualLink(VisualLink $link) : void {
		$this->_link = $link;
	}

	/**
	 * @param string $editor Utilisateur ayant édité l'article
	 * @param float  $date   Date d'édition
	 * @param string $action Flag (voir constantes TITLE, CONTENT, ONLINE)
	 */
	public function edited(string $editor, float $date, string $action):void{
		$len = count($this->_editions);
		$entry = ['user'=>$editor,'date'=>$date,'actions'=>[$action]];
		if($len>0){
			$last = &$this->_editions[$len-1];
			if($editor === $last['user'] && abs($date-$last["date"])<1){
				$last['date']=$date;
				$last['actions'] = array_merge($last['actions'],[$action]);
			}else $this->_editions[] = $entry;
		}else $this->_editions[] = $entry;
	}

	/**
	 * @param bool $online
	 */
	public function setOnline(bool $online): void {
		$this->_online = $online;
	}

	/**
	 * @return Title
	 */
	public function getTitle(): Title {
		return $this->_title;
	}

	/**
	 * @return VisualLink
	 */
	public function getVisualLink() : VisualLink {
		return $this->_link;
	}

	/**
	 * @return Content
	 */
	public function getContent(): Content {
		return $this->_content;
	}

	/**
	 * @return string
	 */
	public function getAuthor(): string {
		return $this->_author;
	}

	/**
	 * @return float
	 */
	public function getCreationDate(): float {
		return $this->_creationDate;
	}

	/**
	 * @return bool
	 */
	public function isOnline(): bool {
		return $this->_online;
	}

	/**
	 * @return array
	 */
	public function getEditions(): array {
		return $this->_editions;
	}

	/**
	 * @return bool
	 */
	public function isArchived(): bool {
		return $this->_archived;
	}

	/**
	 * @param bool $archived
	 */
	public function setArchived(bool $archived): void {
		$this->_archived = $archived;
	}

	/**
	 * @param IModelObject $o
	 * @return int
	 */
	public function compareTo(IModelObject $o): int {
		/** @var Article $o */
		if($this->getCreationDate() === $o->getCreationDate()){
			$cEdit = $this->getEditions();
			$oEdit = $o->getEditions();
			if(count($cEdit) > 0){
				if(count($oEdit) > 0) return 1;
				else{
					$cEdit = array_pop($cEdit);
					$oEdit = array_pop($oEdit);
					return $cEdit["date"] - $oEdit["date"] < 0 ? -1 : 1;
				}
			}else if(count($oEdit) > 0){
				return -1;
			}else return 0;
		}else return $this->getCreationDate() - $o->getCreationDate() < 0 ? -1 : 1;
	}

	/**
	 *  Transforme l'objet courant en DTO pour garder la cohérence du Model
	 *
	 * @return IDTO
	 */
	public function toDTO(): IDTO {
		return new \wfw\engine\package\news\data\model\DTO\Article(
			$this->getId(),
			$this->_title,
			$this->_link,
			$this->_content,
			$this->_creationDate,
			$this->_online,
			$this->_author,
			$this->_editions,
			$this->_archived
		);
	}
}