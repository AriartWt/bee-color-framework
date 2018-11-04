<?php
namespace wfw\engine\package\news\domain\events;

use wfw\engine\lib\PHP\types\UUID;

/**
 * L'article a été archivé
 */
final class ArchivedEvent extends ArticleEvent {
	/** @var string $_archiver */
	private $_archiver;

	/**
	 * ArchivedEvent constructor.
	 *
	 * @param UUID   $aggregateId Article archivé
	 * @param string $userId      Utilisateur ayant archivé l'article
	 */
	public function __construct(UUID $aggregateId,string $userId) {
		parent::__construct($aggregateId);
		$this->_archiver = $userId;
	}

	/**
	 * @return string
	 */
	public function getArchiver(): string { return $this->_archiver; }
}