<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/04/18
 * Time: 09:10
 */

namespace wfw\engine\package\news\domain\events;

use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\news\domain\Title;

/**
 * Le titre d'un article a été édité
 */
final class TitleEditedEvent extends ArticleEvent
{
	/**
	 * @var Title $_title
	 */
	private $_title;

	/**
	 * @var string $_editor
	 */
	private $_editor;

	/**
	 * TitleEditedEvent constructor.
	 *
	 * @param UUID   $articleId Identifiant de l'article
	 * @param Title  $title     Nouveau titre
	 * @param string $editorId  Identifiant de l'éditeur
	 */
	public function __construct(UUID $articleId,Title $title,string $editorId)
	{
		parent::__construct($articleId);
		$this->_title = $title;
		$this->_editor = $editorId;
	}

	/**
	 * @return Title
	 */
	public function getTitle():Title{
		return $this->_title;
	}

	/**
	 * @return string
	 */
	public function getEditorId():string{
		return $this->_editor;
	}
}