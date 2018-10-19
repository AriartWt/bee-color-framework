<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 24/04/18
 * Time: 16:08
 */

namespace wfw\engine\package\news\data\model\specs;

use wfw\engine\core\data\specification\LeafSpecification;
use wfw\engine\package\news\data\model\objects\Article;

/**
 * Permet de savoir si un article a été écrit par un utilisateur contenu dans une liste
 * d'utilisateurs
 */
final class AuthorIs extends LeafSpecification
{
	/** @var string[] $_authorIds */
	private $_authorIds;

	/**
	 * AuthorIs constructor.
	 *
	 * @param string[] $ids Identifiants
	 */
	public function __construct(string... $ids) {
		parent::__construct();
		$this->_authorIds = array_flip($ids);
	}

	/**
	 *  Verifie que le candidat correspond à la spécification
	 *
	 * @param mixed $candidate Candidat à la specification
	 *
	 * @return bool
	 */
	public function isSatisfiedBy($candidate): bool {
		/** @var Article $candidate */
		return isset($this->_authorIds[(string)$candidate->getAuthor()]);
	}
}