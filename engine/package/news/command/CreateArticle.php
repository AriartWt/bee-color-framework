<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 25/04/18
 * Time: 11:34
 */

namespace wfw\engine\package\news\command;

use wfw\engine\package\news\domain\Content;
use wfw\engine\package\news\domain\Title;
use wfw\engine\package\news\domain\VisualLink;

/**
 * Commande de création d'un article.
 */
final class CreateArticle extends ArticleCommand
{
	/** @var Title $_title */
	private $_title;
	/** @var VisualLink $_visual */
	private $_visual;
	/** @var Content $_content */
	private $_content;
	/** @var bool $_online */
	private $_online;
	/** @var string $_authorId */
	private $_authorId;

	/**
	 * CreateArticle constructor.
	 *
	 * @param Title      $title    Titre de l'article
	 * @param VisualLink $link     Lien vers le visuel de l'article
	 * @param Content    $content  Contenu de l'article
	 * @param string     $authorId Identifiant de l'auteur
	 * @param bool       $online   True article en ligne, false hors ligne
	 */
	public function __construct(
		Title $title,
		VisualLink $link,
		Content $content,
		string $authorId,
		bool $online=false
	){
		parent::__construct();
		$this->_title = $title;
		$this->_visual = $link;
		$this->_content = $content;
		$this->_online = $online;
		$this->_authorId = $authorId;
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
		return $this->_visual;
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
	 * @return string
	 */
	public function getAuthorId(): string {
		return $this->_authorId;
	}
}