<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/04/18
 * Time: 10:51
 */

namespace wfw\engine\package\news\domain\repository;


use wfw\engine\core\command\ICommand;
use wfw\engine\package\news\domain\Article;

/**
 * Repository d'articles
 */
interface IArticleRepository
{
	/**
	 * Obtient l'article d'identifiant $id
	 * @param string $id
	 * @return null|Article
	 */
	public function get(string $id):?Article;

	/**
	 * Retourne tous les articles correspondants aux identifiants
	 * @param string ...$ids Liste d'identifiants d'articles
	 * @return Article[]
	 */
	public function getAll(string... $ids):array;

	/**
	 * @param Article  $article Article à ajouter/modifier
	 * @param ICommand $command Commande ayant entraîné la création
	 */
	public function add(Article $article,ICommand $command):void;

	/**
	 * @param Article  $article Article à supprimer
	 * @param ICommand $command Commande ayant entraîné la modification
	 */
	public function edit(Article $article,ICommand $command): void;
}