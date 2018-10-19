<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/04/18
 * Time: 11:09
 */

namespace wfw\engine\package\news\command;

use wfw\engine\package\news\domain\Content;
use wfw\engine\package\news\domain\Title;
use wfw\engine\package\news\domain\VisualLink;

/**
 * Edite un article
 */
final class EditArticle extends ArticleCommand
{
	/** @var string $_articleId */
	private $_articleId;
	/** @var null|Title $_title */
	private $_title;
	/** @var null|VisualLink $_visual */
	private $_visual;
	/** @var null|Content $_content */
	private $_content;
	/** @var string $_editor */
	private $_editor;

	/**
	 * EditArticle constructor.
	 *
	 * @param string          $articleIds
	 * @param string          $editorId
	 * @param null|Title      $title
	 * @param null|VisualLink $link
	 * @param null|Content    $content
	 * @throws \InvalidArgumentException
	 */
	public function __construct(
		string $articleIds,
		string $editorId,
		?Title $title=null,
		?VisualLink $link=null,
		?Content $content=null
	){
		parent::__construct();
		if(is_null($title) && is_null($content) && is_null($link))
			throw new \InvalidArgumentException(
				"At least title,content or visual link have to be edited !"
			);

		$this->_articleId = $articleIds;
		$this->_content = $content;
		$this->_editor = $editorId;
		$this->_title = $title;
		$this->_visual = $link;
	}

	/**
	 * @return string
	 */
	public function getArticleId(): string {
		return $this->_articleId;
	}

	/**
	 * @return null|Title
	 */
	public function getTitle(): ?Title {
		return $this->_title;
	}

	/**
	 * @return null|VisualLink
	 */
	public function getVisualLink(): ?VisualLink {
		return $this->_visual;
	}

	/**
	 * @return null|Content
	 */
	public function getContent(): ?Content {
		return $this->_content;
	}

	/**
	 * @return string
	 */
	public function getEditor(): string {
		return $this->_editor;
	}
}