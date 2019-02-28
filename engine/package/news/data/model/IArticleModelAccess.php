<?php
namespace wfw\engine\package\news\data\model;

use wfw\engine\core\data\model\IArraySorter;
use wfw\engine\core\data\specification\ISpecification;
use wfw\engine\package\news\data\model\DTO\Article;

/**
 * Accés au model Articles
 */
interface IArticleModelAccess {
	/**
	 * @param string $id identifiant de l'article recherché
	 * @return Article
	 */
	public function getById(string $id):?Article;

	/**
	 * Retourne tous les articles.
	 * @return Article[]
	 */
	public function getAll():array;

	/**
	 * Retourne tous les articles en ligne.
	 * @return Article[]
	 */
	public function getOnline():array;

	/**
	 * Rtourne tous les articles hors-ligne.
	 * @return Article[]
	 */
	public function getOffline():array;

	/**
	 * Retourne tous les articles archivés.
	 * @return Article[]
	 */
	public function getArchived():array;

	/**
	 * Retourne tous les articles non archivés.
	 * @return Article[]
	 */
	public function getUnarchived():array;

	/**
	 * @param IArraySorter        $sort Permet de contrôler l'ordre des articles et le nombre
	 * @param ISpecification|null $spec Permet de contrôler les articles à afficher ou non.
	 * @return array
	 */
	public function getArticleToDisplayInPublic(IArraySorter $sort,ISpecification $spec=null):array;
}