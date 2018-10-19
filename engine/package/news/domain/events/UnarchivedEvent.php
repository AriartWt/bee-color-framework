<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 25/04/18
 * Time: 11:01
 */

namespace wfw\engine\package\news\domain\events;

use wfw\engine\lib\PHP\types\UUID;

/**
 * L'article a été désarchivé
 */
final class UnarchivedEvent extends ArticleEvent
{
	/** @var string $_unarchiver */
	private $_unarchiver;

	/**
	 * UnarchivedEvent constructor.
	 *
	 * @param UUID   $aggregateId Article
	 * @param string $userId      Utilisateur ayant désarchivé
	 */
	public function __construct(UUID $aggregateId,string $userId) {
		parent::__construct($aggregateId);
		$this->_unarchiver = $userId;
	}

	/**
	 * @return string
	 */
	public function getUnarchiver(): string { return $this->_unarchiver; }
}