<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/04/18
 * Time: 11:19
 */

namespace wfw\engine\package\news\domain\events;

use wfw\engine\core\domain\events\IAggregateRootGeneratedEvent;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\news\domain\Content;
use wfw\engine\package\news\domain\Title;
use wfw\engine\package\news\domain\VisualLink;

/**
 * L'article a été écrit
 */
final class ArticleWrittenEvent extends ArticleEvent implements IAggregateRootGeneratedEvent
{
	/** @var Title $_title */
	private $_title;
	/** @var VisualLink $_link */
	private $_link;
	/** @var Content $_content */
	private $_content;
	/** @var bool $_online */
	private $_online;
	/** @var string $_author */
	private $_author;
	/** @var array $_args */
	private $_args;

	/**
	 * ArticleWrittenEvent constructor.
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
		bool $online
	){
		parent::__construct($id);
		$this->_title = $title;
		$this->_link = $link;
		$this->_content = $content;
		$this->_author = $authorId;
		$this->_online = $online;
		$this->_args = func_get_args();
	}

	/**
	 * @return Title
	 */
	public function getTitle(): Title { return $this->_title; }

	/**
	 * @return VisualLink
	 */
	public function getVisualLink(): VisualLink { return $this->_link; }

	/**
	 * @return Content
	 */
	public function getContent(): Content { return $this->_content; }

	/**
	 * @return bool
	 */
	public function isOnline(): bool { return $this->_online; }

	/**
	 * @return string
	 */
	public function getAuthor(): string { return $this->_author; }

	/**
	 * @return array Arguments du constructeur de l'aggrégat
	 */
	public function getConstructorArgs(): array { return $this->_args; }
}