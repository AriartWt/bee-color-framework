<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/04/18
 * Time: 10:22
 */

namespace wfw\engine\package\news\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\news\command\CreateArticle;
use wfw\engine\package\news\domain\Article;
/**
 * Handler de commande de création d'article.
 */
final class CreateArticleHandler extends ArticleCommandHandler
{
	/**
	 *  Traite la commande
	 * @param ICommand $command Commande à traiter
	 */
	public function handle(ICommand $command)
	{
		/** @var CreateArticle $command */
		$article = new Article(
			new UUID(),
			$command->getTitle(),
			$command->getVisualLink(),
			$command->getContent(),
			$command->getAuthorId(),
			$command->isOnline()
		);
		$this->repos()->add($article,$command);
	}
}