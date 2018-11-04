<?php
namespace wfw\engine\package\news\domain\events;

use wfw\engine\lib\PHP\types\UUID;

/**
 * L'article a été mis en ligne
 */
final class PutOnlineEvent extends ArticleEvent{
	/** @var string $_userId */
	private $_userId;

	/**
	 * PutOnlineEvent constructor.
	 *
	 * @param UUID   $aggregateId
	 * @param string $userId
	 */
	public function __construct(UUID $aggregateId,string $userId) {
		parent::__construct($aggregateId);
		$this->_userId = $userId;
	}

	/**
	 * @return string
	 */
	public function getUserId():string{ return $this->_userId; }
}