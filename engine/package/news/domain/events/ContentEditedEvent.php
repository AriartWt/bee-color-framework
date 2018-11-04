<?php
namespace wfw\engine\package\news\domain\events;

use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\news\domain\Content;

/**
 * Le contenu d'un article a été édité
 */
final class ContentEditedEvent extends ArticleEvent {
	/** @var Content $_content */
	private $_content;
	/** @var string $_editorId */
	private $_editorId;

	/**
	 * ContentEditedEvent constructor.
	 *
	 * @param UUID    $articleId Identifiant de l'article
	 * @param Content $content   Contenu
	 * @param string  $editorId  Identifiant de l'éditeur
	 */
	public function __construct(UUID $articleId,Content $content,string $editorId) {
		parent::__construct($articleId);
		$this->_content = $content;
		$this->_editorId = $editorId;
	}

	/**
	 * @return Content
	 */
	public function getContent():Content{
		return $this->_content;
	}

	/**
	 * @return string
	 */
	public function getEditorId():string{
		return $this->_editorId;
	}
}