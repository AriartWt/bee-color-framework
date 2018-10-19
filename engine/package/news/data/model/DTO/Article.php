<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/04/18
 * Time: 11:22
 */

namespace wfw\engine\package\news\data\model\DTO;

use wfw\engine\core\data\model\DTO\DTO;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\news\domain\Content;
use wfw\engine\package\news\domain\Title;
use wfw\engine\package\news\domain\VisualLink;

/**
 * Article
 */
class Article extends DTO
{
	/** @var Title $_title */
	private $_title;
	/** @var VisualLink $_link */
	private $_link;
	/** @var Content $_content */
	private $_content;
	/** @var bool $_online */
	private $_online;
	/** @var float $_creationDate */
	private $_creationDate;
	/** @var array $_editions */
	private $_editions;
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
	 * @param float      $creationDate
	 * @param bool       $online
	 * @param string     $author
	 * @param array      $editions
	 * @param bool       $archived
	 */
	public function __construct(
		UUID $id,
		Title $title,
		VisualLink $link,
		Content $content,
		float $creationDate,
		bool $online,
		string $author,
		array $editions,
		bool $archived
	){
		parent::__construct($id);
		$this->_title = $title;
		$this->_link = $link;
		$this->_content = $content;
		$this->_creationDate = $creationDate;
		$this->_online = $online;
		$this->_author = $author;
		$this->_editions = $editions;
		$this->_archived = $archived;
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
	public function getVisualLink(): VisualLink {
		return $this->_link;
	}

	/**
	 * @return Content
	 */
	public function getContent(): Content {
		return $this->_content;
	}

	/**
	 * @return bool
	 */
	public function isOnline(): bool {
		return $this->_online;
	}

	/**
	 * @return float
	 */
	public function getCreationDate(): float {
		return $this->_creationDate;
	}

	/**
	 * @return array
	 */
	public function getEditions():array {
		return $this->_editions;
	}

	/**
	 * @return string
	 */
	public function getAuthor():string{
		return $this->_author;
	}

	/**
	 * @return bool
	 */
	public function isArchived():bool{
		return $this->_archived;
	}

	/**
	 * @return array
	 */
	public function transformProperties(): array {
		return array_merge(parent::transformProperties(),[
			"_title" => (string) $this->_title,
			"_content" => (string) $this->_content,
			"_link" => (string) $this->_link
		]);
	}
}