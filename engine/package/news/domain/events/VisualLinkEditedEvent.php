<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/05/18
 * Time: 16:31
 */

namespace wfw\engine\package\news\domain\events;


use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\news\domain\VisualLink;

/**
 * Le lien vers le visuel d'un article a été édité
 */
final class VisualLinkEditedEvent extends ArticleEvent
{
	/** @var VisualLink $_link */
	private $_link;

	/** @var string $_editorId */
	private $_editorId;

	/**
	 * VisualLinkEditedEvent constructor.
	 *
	 * @param UUID       $aggregateId
	 * @param VisualLink $link
	 * @param string     $editorId
	 */
	public function __construct(UUID $aggregateId,VisualLink $link,string $editorId) {
		parent::__construct($aggregateId);
		$this->_link = $link;
		$this->_editorId = $editorId;
	}

	/**
	 * @return VisualLink
	 */
	public function getVisualLink():VisualLink{ return $this->_link; }

	/**
	 * @return string
	 */
	public function getEditorId():string{ return $this->_editorId; }
}