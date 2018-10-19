<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 25/05/18
 * Time: 13:26
 */

namespace wfw\engine\package\news\data\model;

use wfw\engine\core\data\DBAccess\NOSQLDB\msServer\IMSServerAccess;
use wfw\engine\core\data\model\IArraySorter;
use wfw\engine\core\data\specification\ISpecification;
use wfw\engine\package\news\data\model\DTO\Article;
use wfw\engine\package\news\data\model\specs\IsOffline;
use wfw\engine\package\news\data\model\specs\IsOnline;

/**
 * Acces au model Articles via le msserver
 */
final class ArticleModelAccess implements IArticleModelAccess
{
	/** @var IMSServerAccess $_db */
	private $_db;

	/**
	 * ArticleModelAccess constructor.
	 *
	 * @param IMSServerAccess $access
	 */
	public function __construct(IMSServerAccess $access) { $this->_db = $access; }

	/**
	 * Retourne tous les articles.
	 *
	 * @return Article[]
	 */
	public function getAll(): array {
		return $this->_db->query(ArticleModel::class,"id");
	}

	/**
	 * Retourne tous les articles en ligne.
	 *
	 * @return Article[]
	 */
	public function getOnline(): array {
		return $this->_db->query(ArticleModel::class,ArticleModel::ONLINE);
	}

	/**
	 * Rtourne tous les articles hors-ligne.
	 *
	 * @return Article[]
	 */
	public function getOffline(): array {
		return $this->_db->query(ArticleModel::class,ArticleModel::OFFLINE);
	}

	/**
	 * Retourne tous les articles archivés.
	 *
	 * @return Article[]
	 */
	public function getArchived(): array {
		return $this->_db->query(ArticleModel::class,ArticleModel::ARCHIVED);
	}

	/**
	 * Retourne tous les articles non archivés.
	 *
	 * @return Article[]
	 */
	public function getUnarchived(): array {
		return $this->_db->query(ArticleModel::class,ArticleModel::NOT_ARCHIVED);
	}

	/**
	 * @param IArraySorter        $sort Permet de contrôler l'ordre des articles et le nombre
	 * @param ISpecification|null $spec Permet de contrôler les articles à afficher ou non.
	 * @return array
	 */
	public function getArticleToDisplayInPublic(IArraySorter $sort, ISpecification $spec = null): array {
		return $this->_db->query(
			ArticleModel::class,
			"$sort:".($spec ? "$spec:" : "")."(online & notArchived)"
		);
	}
}